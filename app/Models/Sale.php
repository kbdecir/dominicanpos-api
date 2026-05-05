<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $table = 'sales';
    protected $primaryKey = 'sale_id';
    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'branch_id',
        'warehouse_id',
        'cash_register_id',
        'cash_shift_id',
        'customer_id',
        'sold_by_user_id',
        'sale_number',
        'sale_type',
        'status',
        'sale_datetime',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'change_amount',
        'balance_due',
        'notes',
        'fiscal_document_type_id',
        'ncf_sequence_id',
        'ncf',
        'fiscal_status',
        'fiscal_issued_at',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'sale_id', 'sale_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class, 'sale_id', 'sale_id');
    }

    public function cashShift(): BelongsTo
    {
        return $this->belongsTo(CashShift::class, 'cash_shift_id', 'cash_shift_id');
    }

    public function fiscalDocumentType(): BelongsTo
    {
        return $this->belongsTo(
            FiscalDocumentType::class,
            'fiscal_document_type_id',
            'fiscal_document_type_id'
        );
    }

    public function ncfSequence(): BelongsTo
    {
        return $this->belongsTo(
            NcfSequence::class,
            'ncf_sequence_id',
            'ncf_sequence_id'
        );
    }
}
