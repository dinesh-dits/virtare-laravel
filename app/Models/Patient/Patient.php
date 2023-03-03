<?php

namespace App\Models\Patient;

use App\Helper;
use App\Models\Note\Note;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patient extends Model
{
    use SoftDeletes;

    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'patients';
    use HasFactory;

    protected $guarded = [];


    // public function initials(): string
    // {
    // 	return substr($this->firstName, 0, 1);
    // }


    public function gender()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'genderId');
    }

    public function language()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'languageId');
    }

    public function otherLanguage()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'otherLanguageId');
    }

    public function contactType()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'contactTypeId');
    }

    public function contactTime()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'contactTimeId');
    }

    public function state()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'stateId');
    }


    public function country()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'countryId');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'userId');
    }

    public function family()
    {
        if (Helper::currentPatient('App\Models\Patient\PatientFamilyMember')) {
            return $this->belongsTo(PatientFamilyMember::class, 'id', 'patientId')->where('userId', auth()->user()->id);
        } else {
            return $this->belongsTo(PatientFamilyMember::class, 'id', 'patientId')->where('isPrimary', 1);
        }
    }

    public function emergency()
    {
        return $this->hasOne(PatientEmergencyContact::class, 'patientId');
    }

    public function physician()
    {
        return $this->belongsTo(PatientPhysician::class, 'id', 'patientId');
    }


    public function vitals()
    {
        $patentId = $this->id;
        return $this->hasMany(PatientVital::class, 'patientId')->whereIn(DB::raw('(patientVitals.takeTime,vitalFieldId)'), function ($query) use ($patentId) {
            return $query->from('patientVitals')
                ->selectRaw('max(`takeTime`),vitalFieldId')
                ->where('patientId', $patentId)
                ->groupBy("vitalFieldId");
        })->groupBy('vitalFieldId');
    }

    public function vitals_list($patientId)
    {
        return PatientVital::where('patientId', $patientId)->whereIn(DB::raw('(patientVitals.takeTime,vitalFieldId)'), function ($query) use ($patientId) {
            return $query->from('patientVitals')
                ->selectRaw('max(`takeTime`),vitalFieldId')
                ->where('patientId', $patientId)
                ->groupBy("vitalFieldId")->orderBy('vitalFieldId', 'asc');
        })->groupBy('vitalFieldId')->orderBy('vitalFieldId', 'asc')->get();
    }

    public static function getVitalsByPatinetId($patientId, $fromDate = "", $toDate = "", $vitalFieldIds = "", $deviceTypeId = "")
    {

        if (!empty($fromDate) && !empty($toDate) && !empty($patientId) && !empty($patientId)) {
            if (!empty($deviceTypeId)) {
                return PatientVital::with('vitalFieldNames')->where('patientId', $patientId)
                    ->whereIn('vitalFieldId', $vitalFieldIds)
                    ->whereIn('deviceTypeId', $deviceTypeId)
                    ->whereBetween('takeTime', [$fromDate, $toDate])
                    ->get();
            } else {
                return PatientVital::with('vitalFieldNames')->where('patientId', $patientId)
                    ->whereIn('vitalFieldId', $vitalFieldIds)
                    ->whereBetween('takeTime', [$fromDate, $toDate])
                    ->get();
            }
        } else {
            if (!empty($patientId)) {
                return PatientVital::with('vitalFieldNames', 'deviceType', 'patient', 'notes')->where('patientId', $patientId)->get();
            } else {
                return false;
            }
        }
    }

    public function conditions()
    {
        return $this->belongsTo(PatientCondition::class, 'id', 'patientId');
    }

    public function flags()
    {
        return $this->hasOne(PatientFlag::class, 'patientId');
    }

    public function inventories()
    {
        return $this->hasMany(PatientInventory::class, 'patientId');
    }

    public function patientStaff()
    {
        return $this->belongsTo(PatientStaff::class, 'id', 'patientId');
    }

    public function vital()
    {
        return $this->hasMany(PatientVital::class, 'patientId')->whereRaw('id IN (select MAX(id) FROM patientVitals GROUP BY vitalFieldId)')
            ->orderBy('createdAt', 'desc');
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'referenceId');
    }

    public function insurance()
    {
        return $this->hasOne(PatientInsurance::class, 'patientId');
    }

    public function patientReferral()
    {
        return $this->belongsTo(PatientReferral::class, 'id', 'patientId');
    }

    public function staffPatient()
    {
        return $this->belongsTo(PatientStaff::class, 'id', 'patientId')->where('isPrimary', 1);
    }

    public function vitalData()
    {
        return $this->hasMany(PatientVital::class, 'patientId');
    }

    public function defaultProvider()
    {
        return $this->hasOne(PatientProvider::class, 'patientId')->where('isDefault', 1);
    }

    public function patientProvider()
    {
        return $this->hasMany(PatientProvider::class, 'patientId')->where('isDefault', 0);
    }
}
