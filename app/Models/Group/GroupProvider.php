<?php

namespace App\Models\Group;

use App\Models\Provider\Provider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupProvider extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'groupProviders';
    use HasFactory;
    protected $guarded = [];

    public function provider()
    {
        return $this->belongsTo(Provider::class,'programId');
    }

    public function group()
    {
        return $this->hasMany(Group::class,'groupId','groupId');
    }
}
