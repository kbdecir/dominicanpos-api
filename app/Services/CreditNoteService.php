<?php

namespace App\Services;

use App\Models\CreditNote;
use App\Models\CreditNoteItem;

use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreditNoteService
{
    public function __construct(
        private readonly FiscalDocumentService $fiscalDocumentService,
    ) {}

    public function create(array $data): CreditNote
    {
        return DB::transaction(function () use ($data) {
            $sale = Sale::query()
                ->with(['items'])
                ->where('company_id', $data['company_id'])
                ->where('branch_id', $data['branch_id'])
                ->where('sale_id', $data['original_sale_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $this->validateOriginalSale($sale);

            $itemsPayload = $data['items'];
            $totals = $this->calculateTotals($sale, $itemsPayload);
            $this->validateCreditLimit($sale, $totals['total_amount']);

            $creditNote = CreditNote::create([
                'company_id' => $data['company_id'],
                'branch_id' => $data['branch_id'],
                'original_sale_id' => $sale->sale_id,
                'customer_id' => $sale->customer_id,
                'fiscal_document_type_id' => $data['fiscal_document_type_id'],
                'reason' => $data['reason'],
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['tax_amount'],
                'total_amount' => $totals['total_amount'],
                'status' => 'ISSUED',
                'issued_at' => now(),
                'created_by_user_id' => $data['created_by_user_id'],
            ]);

            foreach ($itemsPayload as $itemPayload) {
                $saleItem = $this->findSaleItem($sale, (int) $itemPayload['sale_item_id']);
                $quantity = (float) $itemPayload['quantity'];

                $lineSubtotal = $quantity * (float) $saleItem->unit_price;
                $taxAmount = $this->calculateProportionalTax($saleItem, $quantity);
                $lineTotal = $lineSubtotal + $taxAmount;

                CreditNoteItem::create([
                    'credit_note_id' => $creditNote->credit_note_id,
                    'sale_item_id' => $saleItem->sale_item_id,
                    'product_id' => $saleItem->product_id,
                    'product_name' => $saleItem->product_name,
                    'sku' => $saleItem->sku,
                    'quantity' => $quantity,
                    'unit_price' => $saleItem->unit_price,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineTotal,
                ]);

                /* $this->restoreInventory(
                    sale: $sale,
                    saleItem: $saleItem,
                    quantity: $quantity,
                    createdByUserId: $data['created_by_user_id']
                ); */
                $this->restoreInventory(
                    creditNote: $creditNote,
                    sale: $sale,
                    saleItem: $saleItem,
                    quantity: $quantity,
                    createdByUserId: $data['created_by_user_id']
                );
            }

            $creditNote = $this->fiscalDocumentService->assignFiscalDocumentToCreditNote(
                creditNote: $creditNote,
                fiscalDocumentTypeId: $data['fiscal_document_type_id']
            );

            $this->updateSaleCreditStatus($sale);

            return $creditNote->fresh(['items', 'originalSale']);
        });
    }

    private function validateOriginalSale(Sale $sale): void
    {
        if (empty($sale->ncf) || $sale->fiscal_status !== 'ISSUED') {
            throw ValidationException::withMessages([
                'original_sale_id' => 'Solo se pueden emitir notas de crédito sobre ventas fiscales emitidas.',
            ]);
        }

        if ($sale->status !== 'COMPLETED') {
            throw ValidationException::withMessages([
                'original_sale_id' => 'Solo se pueden acreditar ventas completadas.',
            ]);
        }

        if ($sale->credit_status === 'FULL') {
            throw ValidationException::withMessages([
                'original_sale_id' => 'Esta venta ya fue acreditada completamente.',
            ]);
        }
    }

    private function calculateTotals(Sale $sale, array $itemsPayload): array
    {
        $subtotal = 0;
        $taxAmount = 0;
        $totalAmount = 0;

        foreach ($itemsPayload as $itemPayload) {
            $saleItem = $this->findSaleItem($sale, (int) $itemPayload['sale_item_id']);
            $quantity = (float) $itemPayload['quantity'];

            if ($quantity <= 0) {
                throw ValidationException::withMessages([
                    'items' => 'La cantidad a acreditar debe ser mayor que cero.',
                ]);
            }

            if ($quantity > (float) $saleItem->quantity) {
                throw ValidationException::withMessages([
                    'items' => "La cantidad a acreditar excede la cantidad vendida del producto {$saleItem->product_name}.",
                ]);
            }

            $lineSubtotal = $quantity * (float) $saleItem->unit_price;
            $lineTax = $this->calculateProportionalTax($saleItem, $quantity);
            $lineTotal = $lineSubtotal + $lineTax;

            $subtotal += $lineSubtotal;
            $taxAmount += $lineTax;
            $totalAmount += $lineTotal;
        }

        return [
            'subtotal' => round($subtotal, 4),
            'tax_amount' => round($taxAmount, 4),
            'total_amount' => round($totalAmount, 4),
        ];
    }

    private function validateCreditLimit(Sale $sale, float $newCreditTotal): void
    {
        $alreadyCredited = CreditNote::query()
            ->where('original_sale_id', $sale->sale_id)
            ->where('status', 'ISSUED')
            ->sum('total_amount');

        if (((float) $alreadyCredited + $newCreditTotal) > (float) $sale->total_amount) {
            throw ValidationException::withMessages([
                'total_amount' => 'La nota de crédito excede el total disponible de la venta original.',
            ]);
        }
    }

    private function findSaleItem(Sale $sale, int $saleItemId): SaleItem
    {
        $saleItem = $sale->items->firstWhere('sale_item_id', $saleItemId);

        if (! $saleItem) {
            throw ValidationException::withMessages([
                'items' => 'Uno de los items no pertenece a la venta original.',
            ]);
        }

        return $saleItem;
    }

    private function calculateProportionalTax(SaleItem $saleItem, float $quantity): float
    {
        if ((float) $saleItem->quantity <= 0) {
            return 0;
        }

        $ratio = $quantity / (float) $saleItem->quantity;

        return round((float) $saleItem->tax_amount * $ratio, 4);
    }

    private function restoreInventory(
        CreditNote $creditNote,
        Sale $sale,
        SaleItem $saleItem,
        float $quantity,
        int $createdByUserId
    ): void {
        $product = Product::query()
            ->where('company_id', $sale->company_id)
            ->where('product_id', $saleItem->product_id)
            ->first();

        if (! $product || ! $product->track_inventory) {
            return;
        }

        $balance = InventoryBalance::query()
            ->where('company_id', $sale->company_id)
            ->where('branch_id', $sale->branch_id)
            ->where('warehouse_id', $sale->warehouse_id)
            ->where('product_id', $saleItem->product_id)
            ->lockForUpdate()
            ->first();

        if (! $balance) {
            throw ValidationException::withMessages([
                'inventory' => "No existe balance de inventario para el producto {$saleItem->product_name}.",
            ]);
        }

        $balance->update([
            'qty_on_hand' => (float) $balance->qty_on_hand + $quantity,
        ]);

        StockMovement::create([
            'company_id' => $sale->company_id,
            'branch_id' => $sale->branch_id,
            'warehouse_id' => $sale->warehouse_id,
            'product_id' => $saleItem->product_id,
            'movement_type' => 'CREDIT_NOTE',
            'reference_table' => 'credit_notes',
            'reference_id' => $creditNote->credit_note_id,
            'qty_in' => $quantity,
            'qty_out' => 0,
            'unit_cost' => $product->cost_price,
            'notes' => 'Entrada por nota de crédito',
            'created_by_user_id' => $createdByUserId,
            'moved_at' => now(),
        ]);
    }

    /* private function updateSaleCreditStatus(Sale $sale): void
    {
        $creditedTotal = CreditNote::query()
            ->where('original_sale_id', $sale->sale_id)
            ->where('status', 'ISSUED')
            ->sum('total_amount');

        $status = ((float) $creditedTotal >= (float) $sale->total_amount)
            ? 'FULL'
            : 'PARTIAL';

        $sale->update([
            'credit_status' => $status,
        ]);
    } */

    private function updateSaleCreditStatus(Sale $sale): void
    {
        $saleItemIds = $sale->items()
            ->pluck('sale_item_id');

        $soldQty = (float) $sale->items()
            ->sum('quantity');

        $creditedQty = (float) CreditNoteItem::query()
            ->join('credit_notes', 'credit_notes.credit_note_id', '=', 'credit_note_items.credit_note_id')
            ->where('credit_notes.original_sale_id', $sale->sale_id)
            ->where('credit_notes.status', 'ISSUED')
            ->whereIn('credit_note_items.sale_item_id', $saleItemIds)
            ->sum('credit_note_items.quantity');

        if ($creditedQty <= 0) {
            $status = 'NONE';
        } elseif ($creditedQty >= $soldQty) {
            $status = 'FULL';
        } else {
            $status = 'PARTIAL';
        }

        $sale->update([
            'credit_status' => $status,
        ]);
    }
}
