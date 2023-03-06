<?php

namespace App\Models\Escalation;

use App\Models\Staff\Staff;
use App\Models\Referral\Referral;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EscalationAssignTo extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'escalationAssignTo';
    use HasFactory;
    protected $guarded = [];

    public function staff()
    {
        return $this->hasOne(Staff::class,'id', 'referenceId');
    }
    public function reffral()
    {
        return $this->hasOne(Referral::class,'id', 'referenceId');
    }
}
