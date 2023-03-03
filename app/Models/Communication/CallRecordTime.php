<?php

namespace App\Models\Communication;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CallRecordTime extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'callRecordTimes';
    use HasFactory;
	protected $guarded = [];

    // Relationship with Call Record Table
    public function callRecord()
    {
        return $this->hasMany(CallRecord::class,'','id');
    }
}
