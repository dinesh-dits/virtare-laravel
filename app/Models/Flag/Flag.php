<?php

namespace App\Models\Flag;

use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Flag extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'flags';
    use HasFactory;
	protected $guarded = [];
    
    public function vitalType()
    {
        return $this->belongsTo(GlobalCode::class,'vitalTypeId');
    }

    public function typeId()
    {
        return $this->hasOne(GlobalCode::class,'id','type');
    }

   
}
