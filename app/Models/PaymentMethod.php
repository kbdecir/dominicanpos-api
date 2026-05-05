<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $table = 'payment_methods';
    protected $primaryKey = 'payment_method_id';
    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'method_type',
        'requires_reference',
        'is_cash',
        'status',
    ];

    protected $casts = [
        'requires_reference' => 'boolean',
        'is_cash' => 'boolean',
    ];
}
