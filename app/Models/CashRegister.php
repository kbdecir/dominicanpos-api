<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CashRegister extends Model
{
    use HasFactory;

    protected $table = 'cash_registers';
    protected $primaryKey = 'cash_register_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'name',
        'description',
        'status',
        'created_by_user_id',
        'updated_by_user_id',
        'min_cash_balance',
    ];

    protected $casts = [
        'min_cash_balance' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'branch_id');
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(CashShift::class, 'cash_register_id', 'cash_register_id');
    }

    public function openShift(): HasOne
    {
        return $this->hasOne(CashShift::class, 'cash_register_id', 'cash_register_id')
            ->where('status', 'OPEN');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }
}
