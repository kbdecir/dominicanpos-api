<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditNote extends Model
{
    protected $table = 'credit_notes';
    protected $primaryKey = 'credit_note_id';
    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'branch_id',
        'original_sale_id',
        'customer_id',
        'fiscal_document_type_id',
        'ncf_sequence_id',
        'ncf',
        'reason',
        'subtotal',
        'tax_amount',
        'total_amount',
        'status',
        'issued_at',
        'created_by_user_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:4',
        'tax_amount' => 'decimal:4',
        'total_amount' => 'decimal:4',
        'issued_at' => 'datetime',
    ];

    public function originalSale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'original_sale_id', 'sale_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
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
        return $this->belongsTo(NcfSequence::class, 'ncf_sequence_id', 'ncf_sequence_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id', 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CreditNoteItem::class, 'credit_note_id', 'credit_note_id');
    }
}
