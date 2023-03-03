<?php

namespace App\Models\Task;

use App\Models\GlobalCode\GlobalCode;
use App\Models\Staff\Staff;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'tasks';
    use HasFactory;
	protected $guarded = [];

    public function taskCategory()
    {
        return $this->hasMany(TaskCategory::class, 'taskId');
    }

    public function priority()
    {
        return $this->belongsTo(GlobalCode::class, 'priorityId');
    }

    public function assignedTo()
    {
        return $this->hasMany(TaskAssignedTo::class, 'taskId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public function taskStatus()
    {
        return $this->belongsTo(GlobalCode::class, 'taskStatusId');
    }

    public function taskType()
    {
        return $this->belongsTo(GlobalCode::class, 'taskTypeId');
    }
}
