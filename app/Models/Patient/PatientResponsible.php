<?php

namespace App\Models\Patient;

use App\Models\User\User;
use App\Models\Patient\Patient;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientResponsible extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'patientResponsibles';
    use HasFactory;
	protected $guarded = [];
    

    public function gender()
    {
        return $this->hasOne(GlobalCode::class,'id','genderId');
    }

    public function relation()
    {
        return $this->hasOne(GlobalCode::class,'id','relationId');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class,'id');
    }
}
