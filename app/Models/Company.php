<?php

namespace App\Models;

use App\Models\UserInvitation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';
    protected $primaryKey = 'company_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'legal_name',
        'trade_name',
        'rnc',
        'email',
        'phone',
        'status',
        'created_by_user_id',
    ];

    public $timestamps = true;

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class, 'company_id', 'company_id');
    }

    public function userCompanyRoles(): HasMany
    {
        return $this->hasMany(UserCompanyRole::class, 'company_id', 'company_id');
    }

    public function invitations()
    {
        return $this->hasMany(UserInvitation::class, 'company_id', 'company_id');
    }

}
