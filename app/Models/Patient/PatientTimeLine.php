<?php

namespace App\Models\Patient;
;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientTimeLine extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'patientTimelines';
    use HasFactory;
	protected $guarded = [];
    
   public function patient()
   {
       return $this->hasOne(Patient::class,'id','patientId');
   }
    
}
