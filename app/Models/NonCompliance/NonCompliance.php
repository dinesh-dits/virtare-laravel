<?php

namespace App\Models\NonCompliance;

use App\Models\Patient\Patient;
use Illuminate\Database\Eloquent\Model;
use App\Models\Patient\PatientInventory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NonCompliance extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const Created_AT = 'createdAt';
    const Updated_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'nonCompliances';
    use HasFactory;
    protected $guarded = [];

    public function patient()
    {
        return $this->hasOne(Patient::class,'id','patientId');
    }

    public function patientInventory()
    {
        return $this->hasOne(PatientInventory::class,'id','patientInventoryId');
    }
}
