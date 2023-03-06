<?php

namespace App\Models\Provider;

use App\Models\Provider\SubLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Provider\ProviderLocationState;
use App\Models\Provider\ProviderLocationProgram;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProviderLocationCity extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'providerLocationCities';
    use HasFactory;
    protected $guarded = [];

    public function state()
    {
        return $this->hasOne(ProviderLocationState::class, 'id', 'stateId');
    }

    public function subLocation()
    {
        return $this->hasOne(SubLocation::class, 'subLocationParent', 'id')->where('entityType', 'City');
    }

    public function providerLocationProgram()
    {
        return $this->hasMany(ProviderLocationProgram::class,'referenceId','id')->where('entityType', 'City');
    }
}
