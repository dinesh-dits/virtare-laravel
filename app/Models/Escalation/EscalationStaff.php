<?php

namespace App\Models\Escalation;

use App\Models\GlobalCode\GlobalCode;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EscalationStaff extends Model
{
    use SoftDeletes;
    protected $softDelete = true; 
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'escalationStaff';
    use HasFactory;
	protected $guarded = [];

    public function escalationType1(){
        return $this->hasMany(EscalationType::class, 'escalationId', 'escalationId')
        ->select("escalationTypes.*","globalCodes.name as globalCodeName")
        ->join('globalCodes', 'globalCodes.id', '=', 'escalationTypes.escalationTypeId')
        ->where("escalationTypes.isActive",1);
    }

    public function escalationType(){
        return $this->hasMany(EscalationType::class, 'escalationId', 'escalationId')
        // ->select("escalationTypes.*","globalCodes.name as globalCodeName")
        // ->join('globalCodes', 'globalCodes.id', '=', 'escalationTypes.escalationTypeId')
        ->where("escalationTypes.isActive",1);
    }

    public function escalationStaff(){
        return $this->hasMany(EscalationStaff::class, 'escalationId', 'escalationId')->where("isActive",1);
    }

    public function escalationVital(){
        return $this->hasMany(EscalationVital::class, 'escalationId', 'escalationId')->where("isActive",1);
    }

    public function escalationNotes(){
        return $this->hasMany(EscalationNotes::class, 'escalationId', 'escalationId')->where("isActive",1);
    }

    public function escalationFlag(){
        return $this->hasMany(EscalationFlag::class, 'escalationId', 'escalationId')->where("isActive",1);
    }

    public function escalationCarePlan(){
        return $this->hasMany(EscalationCarePlan::class, 'escalationId', 'escalationId')->where("isActive",1);
    }
}
