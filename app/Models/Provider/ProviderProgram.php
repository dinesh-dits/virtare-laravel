<?php

namespace App\Models\Provider;

use App\Models\Program\Program;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProviderProgram extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'providerPrograms';
    use HasFactory;
    protected $guarded = [];

    public function program(){
        return $this->hasOne(Program::class,'id','programId');
    }

    public function provider(){
        return $this->hasOne(Provider::class,'id','providerId');
    }
}
