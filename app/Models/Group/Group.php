<?php

namespace App\Models\Group;

use App\Models\Group\StaffGroup;
use App\Models\Provider\Provider;
use App\Models\Group\GroupComposition;
use Illuminate\Database\Eloquent\Model;
use App\Models\Provider\ProviderLocation;
use App\Models\Provider\ProviderLocationCity;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Provider\ProviderLocationState;
use App\Models\Provider\SubLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Group extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'groups';
    use HasFactory;
    protected $guarded = [];



    public function staff()
    {
        return $this->hasMany(StaffGroup::class, 'groupId', 'groupId');
    }

    public function location()
    {
        return $this->hasOne(ProviderLocation::class, 'id', 'providerLocationId');
    }

    public function state()
    {
        return $this->hasOne(ProviderLocationState::class, 'id', 'providerLocationId');
    }

    public function city()
    {
        return $this->hasOne(ProviderLocationCity::class, 'id', 'providerLocationId');
    }

    public function provider()
    {
        return $this->hasOne(Provider::class, 'id', 'providerId');
    }

    public function composition()
    {
        return $this->hasMany(GroupComposition::class, 'groupId', 'groupId');
    }

    public function subLocation()
    {
        return $this->hasOne(SubLocation::class, 'id', 'providerLocationId');
    }
}
