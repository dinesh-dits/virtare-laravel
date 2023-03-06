<?php

namespace App\Models\Patient;

use App\Models\Group\Group;
use App\Models\Patient\Patient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientGroup extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'patientGroups';
    use HasFactory;
    protected $guarded = [];

    public function patient()
    {
        return $this->hasOne(Patient::class,'id' ,'patient');
    }

    public function group()
    {
        return $this->hasOne(Group::class,'groupId' ,'groupId');
    }

}
