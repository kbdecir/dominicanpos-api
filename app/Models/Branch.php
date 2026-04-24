<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $table = 'branches';
    protected $primaryKey = 'branch_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'phone',
        'email',
        'address_line_1',
        'address_line_2',
        'city',
        'province',
        'is_main_branch',
        'status',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function userCompanyRoles(): HasMany
    {
        return $this->hasMany(UserCompanyRole::class, 'branch_id', 'branch_id');
    }
}
