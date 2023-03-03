<?php

namespace App\Models\Patient;

use App\Models\Staff\Staff;
use App\Models\Patient\Patient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientStaff extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'patientStaffs';
    use HasFactory;
	protected $guarded = [];
    
    public function patient()
    {
        return $this->hasOne(Patient::class,'id','patientId')->orderBy('firstName','ASC')->orderBy('lastName','ASC');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class,'staffId');
    }
}
