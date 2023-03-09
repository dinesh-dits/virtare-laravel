<?php

namespace App\Models\PatientNew;

use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientDetail extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = true;
    protected $table = 'patientDetails';
    use HasFactory;
    protected $guarded = [];

    public function primary()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'primaryLanguageId');
    }

    public function secondary()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'secondaryLanguageId');
    }

    public function contactMethod()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'contactMethodId');
    }

    public function bestTimeToCall()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'bestTimeToCallId');
    }

    public function placeOfService()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'placeOfServiceId');
    }
}
