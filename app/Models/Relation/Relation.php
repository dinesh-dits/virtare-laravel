<?php

namespace App\Models\Relation;

use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Relation extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'relations';
    use HasFactory;
    protected $guarded = [];

    public function relation(){
        return $this->belongsTo(GlobalCode::class, 'reverseRelationId', 'id');
    }
}
