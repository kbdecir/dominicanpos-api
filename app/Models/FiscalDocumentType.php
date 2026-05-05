<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiscalDocumentType extends Model
{
    protected $table = 'fiscal_document_types';
    protected $primaryKey = 'fiscal_document_type_id';
    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'description',
        'requires_customer_tax_id',
        'is_electronic',
        'status',
    ];

    protected $casts = [
        'requires_customer_tax_id' => 'boolean',
        'is_electronic' => 'boolean',
    ];

    public function sequences(): HasMany
    {
        return $this->hasMany(NcfSequence::class, 'fiscal_document_type_id', 'fiscal_document_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }
}
