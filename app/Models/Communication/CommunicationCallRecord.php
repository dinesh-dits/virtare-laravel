<?php

namespace App\Models\Communication;

use App\Models\Staff\Staff;
use App\Models\Patient\Patient;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use App\Models\Communication\CallRecord;
use App\Models\Communication\Communication;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommunicationCallRecord extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'communicationCallRecords';
    use HasFactory;
    protected $guarded = [];

    // Relationship with Global Code Table for Call Status
    public function status()
    {
        return $this->belongsTo(GlobalCode::class, 'callStatusId');
    }

    // Relationship with Call Record Table
    public function callRecord()
    {
        return $this->belongsTo(CallRecord::class,'id', 'communicationCallRecordId');
    }

    // Relationship 
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patientId');
    }

    public function communication()
    {
        return $this->hasOne(Communication::class,'id', 'communicationId');
    }
}
