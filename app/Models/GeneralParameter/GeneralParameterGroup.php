<?php

namespace App\Models\GeneralParameter;

use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\GeneralParameter\GeneralParameter;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GeneralParameterGroup extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'generalParameterGroups';
    use HasFactory;
    protected $guarded = [];

    public function generalParameter()
    {
        return $this->hasMany(GeneralParameter::class, 'generalParameterGroupId');
    }

    public function deviceType()
    {
        return $this->hasOne(GlobalCode::class, 'id','deviceTypeId');
    }
}
