<?php

namespace App\Models\Group;

use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupComposition extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = true;
    protected $table = 'groupCompositions';
    use HasFactory;
    protected $guarded = [];

    public function group(){

        return $this->hasOne(Group::class,'groupId','groupId');
    }

    public function designation(){

        return $this->hasOne(GlobalCode::class,'id','designationId');
    }
}
