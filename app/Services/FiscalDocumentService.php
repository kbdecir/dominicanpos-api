<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\FiscalDocumentType;
use App\Models\NcfSequence;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FiscalDocumentService
{
    public function assignFiscalDocumentToSale(
        Sale $sale,
        ?int $fiscalDocumentTypeId,
        ?int $customerId
    ): Sale {
        if (! $fiscalDocumentTypeId) {
            $sale->update([
                'fiscal_status' => 'NOT_APPLICABLE',
            ]);

            return $sale->fresh();
        }

        return DB::transaction(function () use ($sale, $fiscalDocumentTypeId, $customerId) {
            $type = FiscalDocumentType::query()
                ->active()
                ->where('fiscal_document_type_id', $fiscalDocumentTypeId)
                ->firstOrFail();

            $customer = null;

            if ($customerId) {
                $customer = Customer::query()
                    ->where('company_id', $sale->company_id)
                    ->where('customer_id', $customerId)
                    ->first();
            }

            if ($type->requires_customer_tax_id) {
                if (! $customer || empty($customer->tax_id)) {
                    throw ValidationException::withMessages([
                        'customer_id' => 'Este tipo de comprobante requiere un cliente con RNC/Cédula.',
                    ]);
                }
            }

            $sequence = NcfSequence::query()
                ->where('company_id', $sale->company_id)
                ->where('fiscal_document_type_id', $type->fiscal_document_type_id)
                ->active()
                ->where(function ($query) {
                    $query->whereNull('valid_until')
                        ->orWhereDate('valid_until', '>=', now()->toDateString());
                })
                ->whereColumn('current_number', '<=', 'end_number')
                ->lockForUpdate()
                ->orderBy('ncf_sequence_id')
                ->first();

            if (! $sequence) {
                throw ValidationException::withMessages([
                    'fiscal_document_type_id' => 'No existe una secuencia NCF activa y disponible para este tipo de comprobante.',
                ]);
            }

            $ncf = $this->buildNcf($sequence);

            $sale->update([
                'fiscal_document_type_id' => $type->fiscal_document_type_id,
                'ncf_sequence_id' => $sequence->ncf_sequence_id,
                'ncf' => $ncf,
                'fiscal_status' => 'ISSUED',
                'fiscal_issued_at' => now(),
            ]);

            $sequence->current_number = $sequence->current_number + 1;

            if ($sequence->current_number > $sequence->end_number) {
                $sequence->status = 'EXHAUSTED';
            }

            $sequence->save();

            return $sale->fresh();
        });
    }

    private function buildNcf(NcfSequence $sequence): string
    {
        return $sequence->prefix . str_pad(
            (string) $sequence->current_number,
            8,
            '0',
            STR_PAD_LEFT
        );
    }
}
