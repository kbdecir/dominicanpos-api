<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class StockMovement extends Model
{
    //
    protected $table = 'stock_movements';
    protected $primaryKey = 'stock_movement_id';
    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'branch_id',
        'warehouse_id',
        'product_id',
        'movement_type',
        'reference_table',
        'reference_id',
        'qty_in',
        'qty_out',
        'unit_cost',
        'notes',
        'created_by_user_id',
    ];

    protected $casts = [
        'qty_in' => 'decimal:4',
        'qty_out' => 'decimal:4',
        'unit_cost' => 'decimal:4',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'warehouse_id');
    }
}
