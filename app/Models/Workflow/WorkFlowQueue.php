<?php

namespace App\Models\Workflow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\CustomForm\customFormAssignedToUser;
use App\Models\Workflow\WorkFlowQueueStepAction;


class WorkFlowQueue extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = true;
    protected $table = 'workFlowQueue';
    protected $guarded = [];

    public function actions()
    {
        return $this->hasMany(WorkFlowQueueStepAction::class,'workFlowQueueStepId','workFlowQueueId');
    }

    public function assignedForms()
    {
        return $this->belongsTo(customFormAssignedToUser::class,'customFormAssignedId','id');
    }

    public function widgetAccess(){
        return $this->hasManyThrough(
            Widget::class,  
            WidgetAccess::class,  
            'widgetId', // Foreign key on the types table...  
            'widgetModuleId', // Foreign key on the items table...            
            'id', // Local key on the users table...        
            'id' // Local key on the categories table...
           
     );
    }
}
