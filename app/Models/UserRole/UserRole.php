<?php

namespace App\Models\UserRole;

use App\Models\AccessRole\AccessRole;
use App\Models\Role\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRole extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'userRoles';
    use HasFactory;
    protected $guarded = [];


    public function roles()
    {
        return $this->belongsTo(AccessRole::class, 'accessRoleId');
    }
}
