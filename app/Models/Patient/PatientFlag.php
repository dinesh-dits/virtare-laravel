<?php

namespace App\Models\Patient;

use App\Models\Flag\Flag;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Note\Note;
use App\Models\User\User;
use App\Models\Patient\Patient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientFlag extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'patientFlags';
    use HasFactory;
    protected $guarded = [];

    public function flag()
    {
        return $this->belongsTo(Flag::class, 'flagId');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'id');
    }

    public function patientId()
    {
        return $this->HasOne(Patient::class, 'id', 'patientId');
    }

    public function flags()
    {
        return $this->hasOne(Flag::class, 'id', 'flagId');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'deletedBy');
    }

    public function reason()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'removalReasonId');
    }

    public function reasonNote()
    {
        return $this->hasOne(Note::class, 'referenceId');
    }
    public function flagReason()
    {
        return $this->hasOne(Note::class, 'referenceId', 'id')->where('entityType', 'patientFlag');
    }
}
