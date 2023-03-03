<?php

namespace App\Models\Workflow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\CustomForm\customFormAssignedToUser;


class WorkFlowQueueStepAction extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = true;
    protected $table = 'workFlowQueueStepActions';
    protected $guarded = [];

    public function assignedForms()
    {
        return $this->belongsTo(customFormAssignedToUser::class,'customFormAssignedId','id');
    }
}
