<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'customer_id';

    protected $fillable = [
        'company_id',
        'customer_code',
        'customer_type',
        'first_name',
        'last_name',
        'business_name',
        'tax_id',
        'email',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'province',
        'credit_limit',
        'balance',
        'status',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->customer_type === 'BUSINESS') {
            return (string) $this->business_name;
        }

        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }
}
