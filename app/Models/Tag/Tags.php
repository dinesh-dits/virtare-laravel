<?php

namespace App\Models\Tag;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tags extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'tag';
    use HasFactory;
	protected $guarded = [];

    public static function updateTag($tag,$referenceId,$entityType)
    {
        Tags::where("referenceId",$referenceId)
                ->where("entityType",$entityType)
                ->update($tag);
    }
}
