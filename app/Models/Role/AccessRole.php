<?php

namespace App\Models\Role;

use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccessRole extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'accessRoles';
    use HasFactory;
    protected $guarded = [];

    public function roleType()
    {
        return $this->belongsTo(GlobalCode::class, 'roleTypeId');
    }

    public function level()
    {
        return $this->belongsTo(GlobalCode::class, 'levelId');
    }
}
