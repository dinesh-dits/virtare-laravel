<?php

namespace App\Models\Group;

use App\Models\Program\Program;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupProgram extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'groupPrograms';
    use HasFactory;
    protected $guarded = [];

    public function program()
    {
        return $this->belongsTo(Program::class,'programId');
    }
}
