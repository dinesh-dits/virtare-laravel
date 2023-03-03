<?php

namespace App\Models\Group;

use App\Models\Staff\Staff;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffGroup extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'staffGroups';
    use HasFactory;
    protected $guarded = [];

    public function staff()
    {
        return $this->belongsTo(Staff::class,'staffId');
    }

    public function group()
    {
        return $this->belongsTo(Group::class,'groupId','groupId');
    }
}
