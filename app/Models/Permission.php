<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $table = 'permissions';
    protected $primaryKey = 'permission_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'code',
        'name',
        'module_name',
        'description',
        'status',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_permissions',
            'permission_id',
            'role_id',
            'permission_id',
            'role_id'
        );
    }
}
