<?php

namespace App\Models\Staff;

use App\Helper;
use Carbon\Carbon;
use App\Models\Role\Role;
use App\Models\User\User;
use App\Models\Provider\Provider;
use App\Models\UserRole\UserRole;
use App\Models\Staff\StaffProgram;
use App\Models\Staff\StaffLocation;
use App\Models\Patient\PatientStaff;
use App\Models\Provider\SubLocation;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Appointment\Appointment;
use App\Models\Dashboard\Timezone;
use App\Models\Group\StaffGroup;
use Illuminate\Database\Eloquent\Model;
use App\Models\Provider\ProviderLocationCity;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Provider\ProviderLocationState;
use App\Models\Staff\StaffProvider\StaffProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Staff extends Model
{
	use SoftDeletes;
	protected $softDelete = true;
	const DELETED_AT = 'deletedAt';
	const CREATED_AT = 'createdAt';
	const UPDATED_AT = 'updatedAt';
	public $timestamps = false;
	protected $table = 'staffs';
	use HasFactory;
	protected $guarded = [];

	public function network()
	{
		return $this->belongsTo(GlobalCode::class, 'networkId');
	}

	public function type()
	{
		return $this->belongsTo(GlobalCode::class, 'typeId');
	}

	public function specialization()
	{
		return $this->belongsTo(GlobalCode::class, 'specializationId');
	}

	public function designation()
	{
		return $this->belongsTo(GlobalCode::class, 'designationId');
	}

	public function gender()
	{
		return $this->belongsTo(GlobalCode::class, 'genderId');
	}

	public function roles()
	{
		return $this->belongsTo(Role::class, 'roleId');
	}

	public function appointment()
	{
		if (Helper::currentPatient('App\Models\Patient\Patient')) {
			return $this->hasMany(Appointment::class, 'staffId')->where('patientId', Helper::currentPatient('App\Models\Patient\Patient'))->orderBy("id", "DESC");
		} else {
			return $this->hasMany(Appointment::class, 'staffId');
		}
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'userId');
	}

	public function todayAppointment()
	{
		return $this->appointment()->where('startDate', Carbon::today());
	}

	public function patientStaff()
	{
		if (Helper::currentPatient('App\Models\Patient\Patient')) {
			return $this->hasMany(PatientStaff::class, 'staffId')->where('patientId', Helper::currentPatient('App\Models\Patient\Patient'));
		} else {
			return $this->hasMany(PatientStaff::class, 'staffId');
		}
	}

	public function userRole()
	{
		return $this->hasMany(UserRole::class, 'staffId');
	}

	public function provider()
	{
		return $this->hasOne(Provider::class, 'id', 'providerId');
	}

	public function defaultLocation()
	{
		return $this->hasOne(StaffLocation::class, 'staffId')->where('isDefault', 1);
	}

	public function program()
	{
		return $this->hasOne(StaffProgram::class, 'staffId');
	}

	public function state()
	{
		return $this->hasOne(ProviderLocationState::class, 'id', 'providerLocationId');
	}

	public function city()
	{
		return $this->hasOne(ProviderLocationCity::class, 'id', 'providerLocationId');
	}

	public function subLocation()
	{
		return $this->hasOne(SubLocation::class, 'id', 'providerLocationId');
	}

	public function group()
	{
		return $this->hasOne(StaffGroup::class, 'staffId');
	}

	public function defaultProvider()
	{
		return $this->hasOne(StaffProvider::class, 'staffId')->where('isDefault', 1);
	}

	public function staffProvider()
	{
		return $this->hasMany(StaffProvider::class, 'staffId')->where('isDefault', 0);
	}

	// Insert Staff 
	public function peopleAdd(array $data)
	{
		return self::create($data);
	}

	// Update Staff 
	public function updateStaff($id, array $staff)
	{
		return self::where(['udid' => $id])->update($staff);
	}

	// sites
	public function sites()
	{
		return $this->hasMany(Site::class, 'staffId')->where('isDefault', 0);
	}

	
}
