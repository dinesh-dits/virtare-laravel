<?php

namespace App\Models\Staff;

use App\Models\Staff\Staff;
use App\Models\Provider\SubLocation;
use Illuminate\Database\Eloquent\Model;
use App\Models\Provider\ProviderLocation;
use App\Models\Provider\ProviderLocationCity;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Provider\ProviderLocationState;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffLocation extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    public $timestamps = false;
    protected $table = 'staffLocations';
    use HasFactory;
    protected $guarded = [];

    public function staff()
    {
        return  $this->hasOne(Staff::class,'id','staffId');
    }

    public function location()
    {
        return  $this->hasOne(ProviderLocation::class,'id','locationId');
    }

    public function state()
	{
		return $this->hasOne(ProviderLocationState::class, 'id', 'locationId');
	}

	public function city()
	{
		return $this->hasOne(ProviderLocationCity::class, 'id', 'locationId');
	}

	public function subLocation()
	{
		return $this->hasOne(SubLocation::class, 'id', 'locationId');
	}
}
