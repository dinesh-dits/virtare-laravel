<?php

namespace App\Models\Provider;

use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use App\Models\Provider\ProviderLocation;
use App\Models\Provider\ProviderLocationCity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProviderLocationState extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'providerLocationStates';
    use HasFactory;
    protected $guarded = [];


    public function country()
    {
        return $this->hasOne(ProviderLocation::class, 'id', 'countryId');
    }

    public function state()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'stateId');
    }

    public function city()
    {
        return $this->hasMany(ProviderLocationCity::class, 'stateId');
    }

    public function cityLatest()
    {
        return $this->hasMany(ProviderLocationCity::class, 'stateId')->latest();
    }

    public function providerLocationProgram()
    {
        return $this->hasMany(ProviderLocationProgram::class,'referenceId')->where('entityType', 'State');
    }
}
