<?php

namespace App\Models\CPTCode;

use App\Models\GlobalCode\GlobalCode;
use App\Models\Patient\Patient;
use App\Models\Provider\Provider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CptCodeService extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'cptCodeServices';
    use HasFactory;
    protected $guarded = [];

    public function cptCodeActivity()
    {
        return $this->belongsTo(CptCodeActivity::class,'cptCodeActivityId');
    }
    
    public function getCptCodeActivity()
    {
        return $this->belongsTo(CptCodeActivity::class,'cptCodeActivityId')->where("id",2);
    }
    public function patient()
    {
        return $this->belongsTo(Patient::class,'patientId')->withTrashed();
    }
    public function service()
    {
        return $this->belongsTo(Service::class,'serviceId');
    }
    public function cptCodeStatus()
    {
        return $this->belongsTo(GlobalCode::class,'status');
    }
   public function placesOfService()
   {
       return $this->belongsTo(GlobalCode::class,'placeOfService');
   }
   public function cptCodeServiceCondition()
   {
       return $this->hasMany(CptCodeServiceCondition::class,'serviceId');
   }
}
