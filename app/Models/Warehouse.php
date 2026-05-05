<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $table = 'warehouses';
    protected $primaryKey = 'warehouse_id';
    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'name',
        'is_sales_default',
        'status',
    ];

    protected $casts = [
        'is_sales_default' => 'boolean',
    ];
}
