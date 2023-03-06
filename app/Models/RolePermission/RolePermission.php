<?php

namespace App\Models\RolePermission;

use App\Models\Action\Action;
use App\Models\Role\AccessRole;
use App\Models\Role\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RolePermission extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'rolePermissions';
    use HasFactory;
    protected $guarded = [];

    public function role()
    {
        return $this->belongsTo(AccessRole::class,'accessRoleId');
    }

    public function action()
    {
        return $this->belongsTo(Action::class,'actionId');
    }
}
