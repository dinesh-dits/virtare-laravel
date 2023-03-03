<?php

namespace App\Models\Patient;

use App\Models\Patient\Patient;
use App\Models\Program\Program;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientProgram extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'patientPrograms';
    use HasFactory;
	protected $guarded = [];
    
    public function program()
    {
        return $this->hasOne(Program::class,'id','programtId');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class,'id');
    }

}
