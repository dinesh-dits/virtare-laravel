<?php

namespace App\Models\GeneralParameter;

use App\Models\Vital\VitalField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GeneralParameter extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'generalParameters';
    use HasFactory;
	protected $guarded = [];

    public function generalParameterGroup()
    {
        return $this->hasOne(GeneralParameterGroup::class,'id','generalParameterGroupId');
    }

    public function vitalField()
    {
        return $this->belongsTo(VitalField::class,'vitalFieldId');
    }
}
