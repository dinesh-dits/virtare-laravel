<?php

namespace App\Models\Communication;

use App\Models\Staff\Staff;
use Illuminate\Database\Eloquent\Model;
use App\Models\Communication\CallRecordTime;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Communication\CommunicationCallRecord;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CallRecord extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'callRecords';
    use HasFactory;
	protected $guarded = [];

    // Relationship with Communication Call Record Table
    public function communicationCallRecord()
    {
        return $this->hasOne(CommunicationCallRecord::class,'id');
    }

    // Relationship with Staff Table
    public function staff()
    {
        return $this->hasOne(Staff::class,'id','staffId');
    }

    // Relationship with Call Record Time
    public function callRecordTime()
    {
        return $this->belongsTo(CallRecordTime::class,'id','callRecordId');
    }

}
