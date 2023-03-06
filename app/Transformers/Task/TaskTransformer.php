<?php

namespace App\Transformers\Task;

use Illuminate\Support\Facades\DB;
use League\Fractal\TransformerAbstract;
use App\Transformers\Task\TaskAssignedTransformer;
use App\Transformers\Task\TaskCategoryTransformer;


class TaskTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        //
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($data)
    {
        $category=DB::table('taskCategory')->select('taskCategory.*','globalCodes.name as name')
        ->leftJoin('globalCodes','globalCodes.id','=','taskCategory.taskCategoryId')
        ->where('taskCategory.taskId',$data->id)->get();
        $assigned=DB::table('taskAssignedTo')->selectRaw('taskAssignedTo.*, patients.firstName as patientFirstName,
        patients.lastName as patientLastName,staffs.firstName  as staffFirstName,staffs.lastName as staffLastName,patients.udid as patentUdid, staffs.udid as staffUdid
        , patients.id patientsId,staffs.id staffId')
        ->leftJoin('staffs', 'staffs.id', '=', 'taskAssignedTo.assignedTo')
        ->leftJoin('patients', 'patients.id', '=', 'taskAssignedTo.assignedTo')->where("taskAssignedTo.taskId",$data->id)
        ->get();

        if($data->name || $data->priority->name){
            if($data->priority->name=='Urgent' || $data->name=='Urgent'){
                $color='#E63049';
            }elseif($data->priority->name=='Medium' || $data->name=='Medium'){
                $color='#269B8F';
            }elseif($data->priority->name=='Normal' || $data->name=='Normal'){
                $color='#4690FF';
            }else{
                $color='';
            }
        }


        return[
           'id'=>$data->id,
           'title'=>$data->title,
           'description'=>$data->description,
           'taskTypeId'=>$data->taskTypeId,
           'taskType'=>($data->name)?$data->name:$data->taskType->name,
           'taskStatusId'=>$data->taskStatusId,
           'taskStatus'=>(!empty($data->name))?@$data->name:@$data->taskStatus->name,
           'priority'=>($data->name)?$data->name:$data->priority->name,
           'priorityColor'=>$color,
           'priorityId'=>$data->priorityId,
           'category'=>(!empty($data->taskCategory))  ? fractal()->collection($data->taskCategory)->transformWith(new TaskCategoryTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():
            ($category? fractal()->collection($category)->transformWith(new TaskCategoryTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray(): array()),
           'startDate'=>strtotime($data->startDate),
           'dueDate'=>strtotime($data->dueDate),
           'assignedTo'=>$data->assignedTo?fractal()->collection($data->assignedTo)->transformWith(new TaskAssignedTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() :array(),
           'assignedBy'=>(!empty($data->user))?$data->user->staff->firstName.' '.$data->user->staff->lastName:ucfirst(@$data->firstName).' '.ucfirst(@$data->lastName),
           'assignedById'=>(!empty($data->user))?$data->user->staff->udid:'',
           'isActive'=>$data->isActive? True:False,
        ];

    }
}
