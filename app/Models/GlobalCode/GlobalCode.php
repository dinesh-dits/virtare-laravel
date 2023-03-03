<?php

namespace App\Models\GlobalCode;

use App\Models\Vital\VitalTypeField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GlobalCode extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'globalCodes';
    use HasFactory;
	protected $guarded = [];

    public function globalCodeCategory()
    {
        return $this->hasOne(GlobalCodeCategory::class,'id','globalCodeCategoryId');
    }

    public function vitalFieldType()
    {
        return $this->hasMany(VitalTypeField::class,'vitalTypeId');
    }
}
