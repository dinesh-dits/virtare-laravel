<?php

namespace App\Models\TimeApproval;

use App\Models\Staff\Staff;
use App\Models\Patient\Patient;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimeApproval extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'timeApprovals';
    use HasFactory;
    protected $guarded = [];

    public function status()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'statusId');
    }

    public function type()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'typeId');
    }

    public function staff()
    {
        return $this->hasOne(Staff::class, 'id', 'staffId');
    }

    public function patient()
    {
        return $this->hasOne(Patient::class, 'id', 'patientId');
    }
}
