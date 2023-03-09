<?php

namespace App\Models\User;

use App\Models\Address\Address;	
use App\Models\Contact\Contact;
use Carbon\Carbon;
use App\Models\Role\Role;
use App\Models\Staff\Staff;
use App\Models\Patient\Patient;
use App\Models\Dashboard\Timezone;
use App\Models\Patient\PatientPhysician;
use App\Models\Patient\PatientFamilyMember;
use App\Models\Patient\PatientInsurance;	
use App\Models\Patient\PatientProvider;	
use App\Models\PatientNew\PatientDetail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject, AuthenticatableContract, AuthorizableContract
{
    use HasFactory, Notifiable, SoftDeletes; 
    
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function roles()
    {
        return $this->belongsTo(Role::class, 'roleId');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'id', 'userId');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'id', 'userId');
    }

    public function physician()
    {
        return $this->belongsTo(PatientPhysician::class, 'id', 'userId');
    }

    public function familyMember()
    {
        return $this->belongsTo(PatientFamilyMember::class, 'id', 'userId');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getAgeAttribute($dateOfBirth)
    {
        return Carbon::parse($dateOfBirth)->age;
    }

    // Insert User into database
    public function userAdd(array $data)
    {
        return self::create($data);
    }

    // Update Staff 
	public function updateUser($id, array $user)
    {
        return self::where(['id' => $id])->update($user);
    }

    // timeZone
	public function timeZone()
	{
		return $this->hasOne(Timezone::class,'id', 'timeZoneId');
	}

    public function patientDetail()	
    {	
        return $this->hasOne(PatientDetail::class, 'userId', 'udid');	
    }	
    public function address()	
    {	
        return $this->hasOne(Address::class, 'udid', 'userId');	
    }	
    public function insurance()	
    {	
        return $this->hasOne(PatientInsurance::class, 'udid', 'patientId');	
    }	
    public function contact()	
    {	
        return $this->hasOne(Contact::class, 'referenceId', 'udid');	
    }	
    public function careTeam()	
    {	
        return $this->hasOne(PatientProvider::class, 'udid', 'patientId');	
    }
}
