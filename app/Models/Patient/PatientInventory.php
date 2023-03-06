<?php

namespace App\Models\Patient;

use App\Models\Patient\Patient;
use App\Models\Inventory\Inventory;
use App\Models\Patient\PatientGoal;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Vital\VitalTypeField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientInventory extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'patientInventories';
    use HasFactory;
    protected $guarded = [];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventoryId');
    }

    public function deviceTypes()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'deviceType');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patientId');
    }

    public function patientGoal()
    {
        return $this->belongsTo(PatientGoal::class,'vitalFieldId');
    }

    
}
