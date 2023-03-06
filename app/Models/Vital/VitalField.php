<?php

namespace App\Models\Vital;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VitalField extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'vitalFields';
    use HasFactory;
	protected $guarded = [];

    public function deviceName()
    {
        return $this->belongsTo(VitalTypeField::class,'vitalFieldId');
    }
    
}
