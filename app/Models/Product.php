<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $table = 'products';
    protected $primaryKey = 'product_id';

    protected $fillable = [
        'company_id',
        'category_id',
        'brand_id',
        'unit_id',
        'tax_rate_id',
        'sku',
        'product_type',
        'name',
        'description',
        'cost_price',
        'base_price',
        'min_price',
        'track_inventory',
        'allow_negative_stock',
        'min_stock_level',
        'max_stock_level',
        'image_url',
        'status',
    ];

    protected $casts = [
        'cost_price' => 'decimal:4',
        'base_price' => 'decimal:4',
        'min_price' => 'decimal:4',
        'track_inventory' => 'boolean',
        'allow_negative_stock' => 'boolean',
        'min_stock_level' => 'decimal:4',
        'max_stock_level' => 'decimal:4',
    ];
}
