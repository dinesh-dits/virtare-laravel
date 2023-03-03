<?php

namespace App\Models\Task;

use App\Models\Patient\Patient;
use App\Models\Staff\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskAssignedTo extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    public $timestamps = false;
	protected $table = 'taskAssignedTo';
    use HasFactory;
	protected $guarded = [];

    public function assigned()
    {
        return $this->belongsTo(Staff::class, 'assignedTo');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'assignedTo');
    }
    public function task()
    {
        return $this->belongsTo(Task::class,'taskId');
    }
}
