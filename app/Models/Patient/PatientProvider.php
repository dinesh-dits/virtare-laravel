<?php

namespace App\Models\Patient;

use App\Models\Provider\Provider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Client\CareTeam;
class PatientProvider extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    public $timestamps = false;
    protected $table = 'patientProviders';
    use HasFactory;
    protected $guarded = [];

    public function providers()
    {
        return  $this->belongsTo(Provider::class, 'providerId');
    }
    public function patients()
    {
        return  $this->belongsTo(Patient::class, 'patientId');
    }
    public function careteam()
    {
        return  $this->belongsTo(CareTeam::class, 'providerId','udid');
    }
}
