<?php

namespace App\Models\Task;

use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskCategory extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    public $timestamps = false;
	protected $table = 'taskCategory';
    use HasFactory;
	protected $guarded = [];

    public function taskCategory()
    {
        return $this->belongsTo(GlobalCode::class, 'taskCategoryId');
    }
}
