<?php

namespace App\Models\Escalation;

use App\Models\User\User;
use App\Models\Patient\Patient;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use App\Models\Escalation\EscalationDetail;
use App\Models\Escalation\EscalationAssignTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Escalation extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'escalations';
    use HasFactory;
    protected $guarded = [];


    public function assign()
    {
        return $this->hasMany(EscalationAssignTo::class, 'escalationId', 'escalationId');
    }

    public function detail()
    {
        return $this->hasMany(EscalationDetail::class, 'escalationId', 'escalationId');
    }

    public function patient()
    {
        return $this->hasOne(Patient::class, 'id', 'referenceId');
    }

    public function type()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'typeId');
    }

    public function status()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'statusId');
    }

    public function createdByName()
    {
        return $this->hasOne(User::class, 'id', 'createdBy');
    }

    public function escalationAction()
    {
        return $this->hasMany(EscalationAction::class,'escalationId','escalationId');
    }

    public function escalationClose()
    {
        return $this->hasOne(EscalationClose::class,'escalationId','escalationId');
    }

    public function escalationAuditDescription()
    {
        return $this->hasOne(EscalationAuditDescription::class,'escalationId','escalationId');
    }
}
