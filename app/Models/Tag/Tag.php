<?php

namespace App\Models\Tag;

use App\Models\Document\Document;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tag extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'tags';
    use HasFactory;
	protected $guarded = [];

    public function tags()
    {
        return $this->hasOne(GlobalCode::class,'id','tag');
    }
}
