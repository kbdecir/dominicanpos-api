<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NcfSequence extends Model
{
    protected $table = 'ncf_sequences';
    protected $primaryKey = 'ncf_sequence_id';
    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'fiscal_document_type_id',
        'prefix',
        'start_number',
        'end_number',
        'current_number',
        'valid_until',
        'status',
    ];

    protected $casts = [
        'valid_until' => 'date',
    ];

    public function fiscalDocumentType(): BelongsTo
    {
        return $this->belongsTo(
            FiscalDocumentType::class,
            'fiscal_document_type_id',
            'fiscal_document_type_id'
        );
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }
}
