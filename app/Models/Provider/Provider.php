<?php

namespace App\Models\Provider;

use App\Models\GlobalCode\GlobalCode;
use App\Models\Provider\ProviderDomain;
use Illuminate\Database\Eloquent\Model;
use App\Models\Provider\ProviderProgram;
use App\Models\Provider\ProviderLocation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Provider extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'providers';
    use HasFactory;
    protected $guarded = [];


    public function country()
    {
        return $this->belongsTo(GlobalCode::class, 'countryId');
    }

    public function state()
    {
        return $this->belongsTo(GlobalCode::class, 'stateId');
    }

    public function providerProgram()
    {
        return $this->hasMany(ProviderProgram::class, 'providerId')->where("isActive",1);
    }
    public function location()
    {
        return $this->hasMany(ProviderLocation::class, 'providerId')->where("isActive",1);
    }

    public function default()
    {
        return $this->hasOne(ProviderLocation::class, 'providerId')->where("isActive",1)->where('isDefault', 1);
    }

    public function domain()
    {
        return $this->hasOne(ProviderDomain::class,'id', 'domainId');
    }

    public function statusId()
    {
        return $this->hasOne(GlobalCode::class,'id', 'status');
    }

    public function type()
    {
        return $this->hasOne(GlobalCode::class,'id', 'clientType');
    }
}
