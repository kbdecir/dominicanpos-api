<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserInvitation extends Model
{
    use HasFactory;

    protected $table = 'user_invitations';
    protected $primaryKey = 'invitation_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'company_id',
        'branch_id',
        'role_id',
        'email',
        'invitation_token',
        'invited_by_user_id',
        'status',
        'expires_at',
        'accepted_at',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

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

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id', 'user_id');
    }
}
