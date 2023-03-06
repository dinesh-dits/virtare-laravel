<?php

namespace App\Models\Provider;

use App\Models\Program\Program;
use App\Models\Provider\Provider;
use App\Models\Provider\SubLocation;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Provider\ProviderLocationState;
use App\Models\Provider\ProviderLocationProgram;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProviderLocation extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'providerLocations';
    use HasFactory;
    protected $guarded = [];

    protected $parentColumn = 'parent';

    public function parentName()
    {
        return $this->belongsTo(ProviderLocation::class, $this->parentColumn);
    }

    public function providerLocationProgram()
    {
        return $this->hasMany(ProviderLocationProgram::class, 'referenceId', 'id')->where('entityType', 'Country');
    }

    public function provider()
    {
        return $this->hasOne(Provider::class, 'providerId', 'id');
    }

    public function program()
    {
        return $this->hasOne(Program::class, 'programId', 'id');
    }

    public function country()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'countryId');
    }

    public function state()
    {
        $provider = request()->segment(2);
        if (request()->entityType == 'State') {
            $location = ProviderLocation::where('providerId', $provider)->first();
            return $this->hasMany(ProviderLocationState::class, 'countryId')->where('countryId', $location->id);
        } else {
            return $this->hasMany(ProviderLocationState::class, 'countryId');
        }
    }

    public function stateLatest()
    {
        return $this->hasMany(ProviderLocationState::class, 'countryId')->latest();
    }

    public function subLocation()
    {
        return $this->hasMany(SubLocation::class, 'providerLocationId');
    }
}
