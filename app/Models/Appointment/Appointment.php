<?php

namespace App\Models\Appointment;

use App\Models\Note\Note;
use App\Models\Staff\Staff;
use App\Models\Patient\Patient;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\StaffAvailability\StaffAvailability;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'appointments';
    use HasFactory;
    protected $guarded = [];


    // Relationship with Patient Table
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patientId');
    }

    // Relationship with Patient Table
    public function patientName()
    {
        return $this->belongsTo(Patient::class, 'patientId');
    }

    // Relationship with Staff Table
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staffId');
    }

    // Relationship with Staff Table
    public function staffName()
    {
        return $this->belongsTo(Staff::class, 'staffId');
    }

    // Relationship with Staff Availability Table
    public function availability()
    {
        return $this->hasMany(StaffAvailability::class, 'staffId');
    }

    // Relationship with Global Code Table for Appointment Type 
    public function appointmentType()
    {
        return $this->belongsTo(GlobalCode::class, 'appointmentTypeId');
    }

    // Relationship with Global Code Table for duration
    public function duration()
    {
        return $this->belongsTo(GlobalCode::class, 'durationId');
    }

    // Relationship with Global Code Table For Status
    public function status()
    {
        return $this->belongsTo(GlobalCode::class,  'statusId');
    }

    // Relationship with Note Table
    public function notes()
    {
        return $this->hasOne(Note::class, 'referenceId')->where('entityType', 'appointment');
    }
}
