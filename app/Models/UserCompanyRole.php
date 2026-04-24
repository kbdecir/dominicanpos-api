<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCompanyRole extends Model
{
    use HasFactory;

    protected $table = 'user_company_roles';
    protected $primaryKey = 'user_company_roles_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'role_id',
        'assigned_by_user_id',
        'revoked_by_user_id',
        'status',
        'is_default_company',
        'notes',
        'assigned_at',
        'revoked_at',
        'last_access_at',
    ];

    protected $casts = [
        'is_default_company' => 'boolean',
        'assigned_at' => 'datetime',
        'revoked_at' => 'datetime',
        'last_access_at' => 'datetime',
    ];

    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'branch_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }
}
