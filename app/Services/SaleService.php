<?php

namespace App\Services;

use App\Models\CashMovement;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function __construct(
        private readonly CashShiftService $cashShiftService,
        private readonly FiscalDocumentService $fiscalDocumentService,
    ) {}

    public function create(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $shift = $this->cashShiftService->getOpenShiftOrFail(
                companyId: $data['company_id'],
                branchId: $data['branch_id'],
                cashierUserId: $data['sold_by_user_id']
            );

            if ((int) $shift->cash_register_id !== (int) $data['cash_register_id']) {
                throw ValidationException::withMessages([
                    'cash_register_id' => 'La caja indicada no coincide con el turno abierto del cajero.',
                ]);
            }

            $sale = Sale::create([
                'company_id' => $data['company_id'],
                'branch_id' => $data['branch_id'],
                'warehouse_id' => $data['warehouse_id'],
                'cash_register_id' => $data['cash_register_id'],
                'cash_shift_id' => $shift->cash_shift_id,
                'customer_id' => $data['customer_id'] ?? null,
                'sold_by_user_id' => $data['sold_by_user_id'],
                'sale_number' => $this->generateSaleNumber($data['company_id']),
                'sale_type' => $data['sale_type'] ?? 'TICKET',
                'status' => 'COMPLETED',
                'sale_datetime' => now(),
                'notes' => $data['notes'] ?? null,
            ]);



            foreach ($data['items'] as $item) {
                SaleItem::create([
                    'sale_id' => $sale->sale_id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'sku' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'unit_cost' => $item['unit_cost'] ?? 0,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                ]);
            }

            $this->applyInventoryFromSale($data, $sale);

            $sale = $this->fiscalDocumentService->assignFiscalDocumentToSale(
                sale: $sale,
                fiscalDocumentTypeId: $data['fiscal_document_type_id'] ?? null,
                customerId: $data['customer_id'] ?? null
            );

            foreach ($data['payments'] as $payment) {
                SalePayment::create([
                    'sale_id' => $sale->sale_id,
                    'payment_method_id' => $payment['payment_method_id'],
                    'amount' => $payment['amount'],
                    'reference_no' => $payment['reference_no'] ?? null,
                    'paid_at' => now(),
                ]);
            }

            $sale = $sale->fresh(['items', 'payments']);

            $this->syncShiftTotalsFromSale($shift, $sale);

            return $sale->fresh(['items', 'payments']);
        });
    }

    private function syncShiftTotalsFromSale($shift, Sale $sale): void
    {
        $payments = $sale->payments()->with('paymentMethod')->get();

        $cashAmount = 0;
        $cardAmount = 0;
        $transferAmount = 0;
        $otherAmount = 0;

        foreach ($payments as $payment) {
            $method = $payment->paymentMethod;

            if (! $method) {
                $otherAmount += (float) $payment->amount;
                continue;
            }

            if ($method->is_cash || $method->method_type === 'CASH') {
                $cashAmount += (float) $payment->amount;
            } elseif ($method->method_type === 'CARD') {
                $cardAmount += (float) $payment->amount;
            } elseif ($method->method_type === 'TRANSFER') {
                $transferAmount += (float) $payment->amount;
            } else {
                $otherAmount += (float) $payment->amount;
            }
        }

        if ($cashAmount > 0) {
            CashMovement::create([
                'company_id' => $shift->company_id,
                'branch_id' => $shift->branch_id,
                'cash_shift_id' => $shift->cash_shift_id,
                'movement_type' => 'SALE_IN',
                'payment_method_id' => $payments->first(fn($p) => $p->paymentMethod?->is_cash)?->payment_method_id,
                'expense_category_id' => null,
                'amount' => $cashAmount,
                'reference_table' => 'sales',
                'reference_id' => $sale->sale_id,
                'notes' => 'Venta en efectivo',
                'created_by_user_id' => $sale->sold_by_user_id,
            ]);
        }

        $shift->increment('total_cash_sales', $cashAmount);
        $shift->increment('total_card_sales', $cardAmount);
        $shift->increment('total_transfer_sales', $transferAmount);
        $shift->increment('total_other_sales', $otherAmount);
        $shift->increment('expected_cash_amount', $cashAmount);
    }

    private function generateSaleNumber(int $companyId): string
    {
        return 'SALE-' . $companyId . '-' . now()->format('YmdHis') . '-' . random_int(100, 999);
    }

    private function applyInventoryFromSale(array $data, Sale $sale): void
    {
        foreach ($data['items'] as $item) {
            $product = \App\Models\Product::query()
                ->where('company_id', $data['company_id'])
                ->where('product_id', $item['product_id'])
                ->firstOrFail();

            if (! $product->track_inventory) {
                continue;
            }

            $quantity = (float) $item['quantity'];

            $balance = \App\Models\InventoryBalance::query()
                ->where('company_id', $data['company_id'])
                ->where('branch_id', $data['branch_id'])
                ->where('warehouse_id', $data['warehouse_id'])
                ->where('product_id', $product->product_id)
                ->lockForUpdate()
                ->first();

            if (! $balance) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'inventory' => "No existe balance de inventario para el producto {$product->name}.",
                ]);
            }

            $allowNegativeStock = (int) $product->allow_negative_stock === 1;

            // if (! $product->allow_negative_stock && (float) $balance->qty_on_hand < $quantity) {
            if (! $allowNegativeStock && (float) $balance->qty_on_hand < $quantity) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'quantity' => "Stock insuficiente para el producto {$product->name}.",
                ]);
            }

            $balance->update([
                'qty_on_hand' => (float) $balance->qty_on_hand - $quantity,
            ]);

            \App\Models\StockMovement::create([
                'company_id' => $data['company_id'],
                'branch_id' => $data['branch_id'],
                'warehouse_id' => $data['warehouse_id'],
                'product_id' => $product->product_id,
                'movement_type' => 'SALE',
                'reference_table' => 'sales',
                'reference_id' => $sale->sale_id,
                'qty_in' => 0,
                'qty_out' => $quantity,
                'unit_cost' => $product->cost_price,
                'notes' => 'Salida por venta',
                'created_by_user_id' => $data['sold_by_user_id'],
            ]);
        }
    }
}
