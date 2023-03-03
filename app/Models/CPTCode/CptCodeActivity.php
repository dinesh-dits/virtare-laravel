<?php

namespace App\Models\CPTCode;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CptCodeActivity extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'cptCodeActivities';
    use HasFactory;
    protected $guarded = [];

    public function cptCode()
    {
        return $this->belongsTo(CPTCode::class,'cptCodeId');
    }
}
