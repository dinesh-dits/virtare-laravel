<?php

namespace App\Models\Staff;

use App\Models\Program\Program;
use App\Models\Provider\Provider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffProgram extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    public $timestamps = false;
    protected $table = 'staffPrograms';
    use HasFactory;
    protected $guarded = [];

    public function program()
    {
        return  $this->hasOne(Program::class,'id', 'programId');
    }
}
