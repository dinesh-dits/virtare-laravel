<?php

namespace App\Models\Provider;

use App\Models\Program\Program;
use App\Models\Provider\SubLocation;
use Illuminate\Database\Eloquent\Model;
use App\Models\Provider\ProviderLocation;
use App\Models\Provider\ProviderLocationCity;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Provider\ProviderLocationState;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProviderLocationProgram extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'providerLocationPrograms';
    use HasFactory;
    protected $guarded = [];

    public function program()
    {
        return $this->hasOne(Program::class, 'id', 'programId');
    }

    public function location()
    {
        return $this->hasOne(ProviderLocation::class, 'id', 'referenceId')->where('entityType','providerLocation');
    }

    public function state()
    {
        return $this->hasOne(ProviderLocationState::class, 'id', 'referenceId')->where('entityType','providerLocationState');
    }

    public function city()
    {
        return $this->hasOne(ProviderLocationCity::class, 'id', 'referenceId')->where('entityType','providerLocationCity');
    }

    public function subLocation()
    {
        return $this->hasOne(SubLocation::class, 'id', 'referenceId')->where('entityType','subLocation');
    }
}
