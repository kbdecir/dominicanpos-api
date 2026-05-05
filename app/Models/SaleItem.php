<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $table = 'sale_items';
    protected $primaryKey = 'sale_item_id';
    public $timestamps = false;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'sku',
        'quantity',
        'unit_price',
        'unit_cost',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'line_subtotal',
        'line_total',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id', 'sale_id');
    }
}
