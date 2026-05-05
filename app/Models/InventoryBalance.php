<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBalance extends Model
{
    protected $table = 'inventory_balances';
    protected $primaryKey = 'inventory_balance_id';
    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'branch_id',
        'warehouse_id',
        'product_id',
        'qty_on_hand',
        'avg_cost',
    ];

    protected $casts = [
        'qty_on_hand' => 'decimal:4',
        'avg_cost' => 'decimal:4',
    ]; //

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'warehouse_id');
    }
}
