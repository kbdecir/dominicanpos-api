<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password_hash',
        'phone',
        'status',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    public function companyRoles(): HasMany
    {
        return $this->hasMany(UserCompanyRole::class, 'user_id', 'user_id');
    }

    public function activeCompanyRoles(): HasMany
    {
        return $this->companyRoles()->where('status', 'ACTIVE');
    }
}
