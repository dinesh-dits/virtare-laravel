<?php

namespace App\Models\CPTCode;

use App\Models\Condition\Condition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CptCodeServiceCondition extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'cptCodeServiceConditions';
    use HasFactory;
    protected $guarded = [];

    public function condition()
    {
        return $this->belongsTo(Condition::class,'conditionId');
    }
}
