<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use App\Models\Task\Task;
use App\Models\Staff\Staff;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\Patient\Patient;
use App\Models\Task\TaskCategory;
use App\Library\ErrorLogGenerator;
use Illuminate\Support\Facades\DB;
use App\Models\Task\TaskAssignedTo;
use Illuminate\Support\Facades\Auth;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Support\Facades\Schema;
use App\Models\Patient\PatientTimeLine;
use App\Models\Notification\Notification;
use App\Transformers\Task\TaskTransformer;
use App\Models\ConfigMessage\ConfigMessage;
use App\Transformers\Task\TaskAssignedToTransformer;
use App\Transformers\Patient\PatientCountTransformer;
use App\Transformers\Task\TaskDurationCountTransformer;
use Carbon\Carbon;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Events\NotificationEvent;

class TaskService
{

    // Add Task
    public function addTask($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $startDate = Helper::date($request->input('startDate'));
            $dueDate = Helper::date($request->input('dueDate'));
            $input = [
                'udid' => Str::uuid()->toString(),
                'title' => $request->title,
                'description' => $request->input('description'),
                'startDate' => $startDate,
                'dueDate' => $dueDate,
                'taskTypeId' => 69,
                'priorityId' => $request->priority,
                'taskStatusId' => $request->taskStatus,
                'createdBy' => Auth::user()->id,
                'providerId' => $provider,
                'providerLocationId' => $providerLocation
            ];
            $task = Task::create($input);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'tasks', 'tableId' => $task->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            $taskCategoryId = $request->taskCategory;
            foreach ($taskCategoryId as $taskCategory) {
                $taskCate = [
                    'taskId' => $task->id,
                    'taskcategoryId' => $taskCategory,
                    'providerId' => $provider,
                    'providerLocationId' => $providerLocation
                ];
                $category = TaskCategory::create($taskCate);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'taskCategory', 'tableId' => $category->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($taskCate), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
            }
            $assignedToId = $request->assignedTo;


            // email body message
            $msgObj = ConfigMessage::where("type", "taskAdd")
                ->where("entityType", "sendMail")
                ->first();

            // email body message
            $msgObj = ConfigMessage::where("type", "taskAdd")
                ->where("entityType", "sendMail")
                ->first();

            // email header
            $msgHeaderObj = ConfigMessage::where("type", "header")
                ->where("entityType", "sendMail")
                ->first();

            // email footer
            $msgFooterObj = ConfigMessage::where("type", "footer")
                ->where("entityType", "sendMail")
                ->first();

            $i = 0;
            $messageObj = array();
            foreach ($assignedToId as $assignedTo) {
                $assign = Helper::entity($request->entityType, $assignedTo);
                $assigned = [
                    'taskId' => $task->id,
                    'assignedTo' => $assign,
                    'entityType' => $request->entityType,
                    'providerId' => $provider
                ];
                $assignedData = TaskAssignedTo::create($assigned);

                if ($request->entityType == 'patient') {
                    $userId = auth()->user()->staff->id;
                    $user = Staff::where('id', $userId)->first();
                    $timeLine = [
                        'patientId' => $assign, 'heading' => 'New Task', 'title' => '"' . $request->title . '"' . ' Task Assigned <b> By' . ' ' . $user->firstName . ' ' . $user->lastName . '</b>', 'type' => 12,
                        'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $provider
                    ];
                    PatientTimeLine::create($timeLine);
                }

                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'taskAssignedTo', 'tableId' => $assignedData->id, 'providerId' => $provider,
                    'value' => json_encode($assigned), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);

                $fullName = "";
                $userEmailDefined = 0;
                $userPhoneDefined = 0;
                if ($request->entityType == 'staff') {
                    $userEmailDefined = 1;
                    $userPhoneDefined = 1;
                    $staff = Staff::with("user")->where('udid', $assignedTo)->first();
                    if($staff->user->email){
                        $userEmail[] = $staff->user->email;
                    }
                    $fullName = $staff->firstName . " " . $staff->lastName;
                    $UserId = $staff->userId;
                }
                $variablesArr = array(
                    "taskId" => $task->id,
                    "fullName" => $fullName
                );
                if (isset($msgObj->messageBody)) {
                    $messageBody = $msgObj->messageBody;
                    if (isset($msgHeaderObj->messageBody) && !empty($msgHeaderObj->messageBody)) {
                        $messageBody = $msgHeaderObj->messageBody . $messageBody;
                    }
                    if (isset($msgFooterObj->messageBody) && !empty($msgFooterObj->messageBody)) {
                        $messageBody = $messageBody . $msgFooterObj->messageBody;
                    }
                    $messageObj[] = Helper::getMessageBody($messageBody, $variablesArr);
                }
                $i++;
            }

            if ($request->entityType == 'staff') {
                if (isset($userEmail) && !empty($userEmail) && $userEmailDefined == 1) {
                    $to = $userEmail;
                    if (isset($msgObj->otherParameter)) {
                        $otherParameter = json_decode($msgObj->otherParameter);
                        if (isset($otherParameter->fromName)) {
                            $fromName = $otherParameter->fromName;
                        } else {
                            $fromName = "Virtare Health";
                        }
                    } else {
                        $fromName = "Virtare Health";
                    }
                    if (isset($msgObj->subject)) {
                        $subject = $msgObj->subject;
                    } else {
                        $subject = "New Task Added";
                    }
                    
                    if(count($messageObj)){
                        Helper::sendInBulkMail($to, $fromName, $messageObj, $subject);
                    }
                    $notificationData = [
                        'body' => 'New Task Added.',
                        'title' => 'Task Added',
                        'userId' => $UserId,
                        'isSent' => 0,
                        'entity' => 'task',
                        'referenceId' => $task->id,
                        'providerId' => $provider
                    ];
                    $notification = Notification::create($notificationData);
                    event(new NotificationEvent($notification));
                    //    push notifications done

                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'notifications', 'tableId' => $notification->id, 'providerId' => $provider,
                        'value' => json_encode($notificationData), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                }
            }

            $taskData = Task::where('id', $task->id)->with('assignedTo.assigned', 'assignedTo.patient')->first();
            $message = ['message' => trans('messages.createdSuccesfully')];
            $result = fractal()->item($taskData)->transformWith(new TaskTransformer())->toArray();
            $data = array_merge($message, $result);
            return $data;
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // List Task
    public function listTask($request)
    {
        try {
            $data = Task::select('tasks.*')->with('taskCategory', 'taskType', 'priority', 'taskStatus', 'user')->leftJoin('taskCategory', 'taskCategory.taskId', '=', 'tasks.id')->whereNull('taskCategory.deletedAt')->whereNull('tasks.deletedAt')
                ->leftJoin('globalCodes as g1', 'g1.id', '=', 'taskCategory.taskCategoryId')
                ->leftJoin('globalCodes as g2', 'g2.id', '=', 'tasks.taskStatusId')
                ->leftJoin('globalCodes as g3', 'g3.id', '=', 'tasks.priorityId')
                ->leftJoin('taskAssignedTo', 'taskAssignedTo.taskId', '=', 'tasks.id')
                ->join('users', 'users.id', '=', 'tasks.createdBy')
                ->join('staffs', 'staffs.userId', '=', 'users.id')
                ->whereNull('tasks.deletedAt');

            // $data->where('tasks.dueDate', '>=', Carbon::today());

            if (auth()->user()->roleId == 3) {
                if (Helper::haveAccessAction(null, 490)) {
                    $data;
                } else {
                    $data->where([['taskAssignedTo.assignedTo', auth()->user()->staff->id], ['taskAssignedTo.entityType', 'staff']]);
                }
            }

            // $data->leftJoin('providers', 'providers.id', '=', 'tasks.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'tasks.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('tasks.providerLocationId', '=', 'providerLocations.id')->where('tasks.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('tasks.providerLocationId', '=', 'providerLocationStates.id')->where('tasks.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('tasks.providerLocationId', '=', 'providerLocationCities.id')->where('tasks.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('tasks.providerLocationId', '=', 'subLocations.id')->where('tasks.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('tasks.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['tasks.providerLocationId', $providerLocation], ['tasks.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['tasks.providerLocationId', $providerLocation], ['tasks.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['tasks.providerLocationId', $providerLocation], ['tasks.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['tasks.providerLocationId', $providerLocation], ['tasks.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['tasks.programId', $program], ['tasks.entityType', $entityType]]);
            // }
            if ($request->filter) {
                if ($request->filter == 'Total Tasks') {
                    $data;
                } else {
                    $data->where(function ($query) use ($request) {
                        $query->where('g1.name', $request->filter)
                            ->orWhere('g3.name', $request->filter)
                            ->orWhere('g2.name', $request->filter);
                    });
                }
            }
            if ($request->search) {
                $data->where('title', 'LIKE', '%' . $request->search . '%');
            }
            if ($request->assignedTo) {
                $assignedTo = explode(',', $request->assignedTo);
                $input = Staff::selectRaw("group_concat(id) as StaffId")->whereIn('udid', $assignedTo)->first();
                $staffId = explode(',', $input['StaffId']);
                $data->whereIn('taskAssignedTo.assignedTo', $staffId)->where('taskAssignedTo.entityType', 'staff');
            }
            if ($request->assignedBy) {
                $assignedBy = explode(',', $request->assignedBy);
                $input = Staff::selectRaw("group_concat(userId) as StaffId")->whereIn('udid', $assignedBy)->first();
                $staffId = explode(',', $input['StaffId']);
                $data->where('tasks.createdBy', $staffId);
            }
            $fromDateStr = "";
            $toDateStr = "";
            // if ((!empty($request->input('fromDate')) && !empty($request->input('toDate')))) {
            //     $fromDateStr =  Helper::date($request->input('fromDate'));
            //     $toDateStr =  Helper::date($request->input('toDate'));
            //     $data->whereBetween('dueDate', [$fromDateStr, $toDateStr]);
            // }

            if ((!empty($request->input('fromDate')) && !empty($request->input('toDate')))) {
                $fromDateStr = Helper::dateOnly($request->input('fromDate'));
                $toDateStr = Helper::dateOnly($request->input('toDate'));
                $data->whereBetween('dueDate', [$fromDateStr, $toDateStr]);
            } else {
                $now = Carbon::today();
                $fromDate = $now;
                $custommDate = date("Y-m-d", strtotime($now));
                $fromDate = $custommDate . " 00:00:00";
                $toDate = $custommDate . " 23:59:59";
                // $data->whereBetween('dueDate', [$fromDate, $toDate]);
                $data->where('dueDate','<=', $toDate);
            }

            if ($request->status == 'notIn') {
                $data->where('taskStatusId', '!=', 63)->whereBetween('dueDate', [$fromDateStr, $toDateStr]);
            }
            if ($request->orderField == 'taskStatus') {
                $data->orderBy('g2.name', $request->orderBy);
            } elseif ($request->orderField == 'priority') {
                $data->orderBy('g3.name', $request->orderBy);
            } elseif ($request->orderField == 'category') {
                $data->orderBy('g1.name', $request->orderBy);
            } elseif (Schema::hasColumn('tasks', request()->orderField)) {
                $data->orderBy($request->orderField, $request->orderBy);
            } else {
                // if($data->where('tasks.dueDate', '>=', Carbon::today()) &&  $data->where('g2.name', '==', 'Incompleted')){
                // $data->orderBy('g2.priority', 'ASC');
                $data->orderBy('tasks.dueDate', 'ASC');
                // }else{
                //     $data->orderBy('tasks.createdAt', 'DESC');
                // }
            }
            $data->groupBy('tasks.id');
            if (isset($request->islimit) && !empty($request->islimit)) {
                $islimit = $request->islimit;
                $data = $data->paginate($islimit);
            } else {
                $data = $data->paginate(env('PER_PAGE', 20));
            }
            return fractal()->collection($data)->transformWith(new TaskTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // List Task Entity Based
    public function entityTaskList($request, $entity, $id)
    {
        try {
            $data = Task::select('tasks.*')->with('taskCategory', 'taskType', 'priority', 'taskStatus', 'user');

            // $data->leftJoin('providers', 'providers.id', '=', 'tasks.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'tasks.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('tasks.providerLocationId', '=', 'providerLocations.id')->where('tasks.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('tasks.providerLocationId', '=', 'providerLocationStates.id')->where('tasks.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('tasks.providerLocationId', '=', 'providerLocationCities.id')->where('tasks.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('tasks.providerLocationId', '=', 'subLocations.id')->where('tasks.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('tasks.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['tasks.providerLocationId', $providerLocation], ['tasks.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['tasks.providerLocationId', $providerLocation], ['tasks.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['tasks.providerLocationId', $providerLocation], ['tasks.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['tasks.providerLocationId', $providerLocation], ['tasks.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['tasks.programId', $program], ['tasks.entityType', $entityType]]);
            // }
            $reference = Helper::entity($entity, $id);
            if ($request->all) {
                $data->whereHas('assignedTo', function ($query) use ($entity, $reference) {
                    $query->where([['entityType', $entity], ['assignedTo', $reference]]);
                })->latest()->get();
                return fractal()->collection($data)->transformWith(new TaskTransformer())->toArray();
            } else {
                $data->whereHas('assignedTo', function ($query) use ($entity, $reference) {
                    $query->where([['entityType', $entity], ['assignedTo', $reference]]);
                })->latest();
                $data = $data->paginate(env('PER_PAGE', 20));
                return fractal()->collection($data)->transformWith(new TaskTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
            }
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Task List According to Priorities
    public function priorityTask($request)
    {
        try {
            $now = Carbon::today();
            $custommDate = date("Y-m-d", strtotime($now));

            if (!empty($request->fromDate)) {
                $fromDate = Helper::date($request->input('fromDate'));
            } else {
                $fromDate = $custommDate . " 00:00:00";
            }

            if (!empty($request->toDate)) {
                $toDate = Helper::date($request->input('toDate'));
            } else {
                $toDate = $custommDate . " 23:59:59";
            }

            $data = DB::select(
                "CALL taskPriorityCount('" . $fromDate . "','" . $toDate . "')",
            );
            $taskArray = array();
            foreach ($data as $task) {
                array_push($taskArray, $task->text);
            }
            $taskData = GlobalCode::where('globalCodeCategoryId', '7')->get();
            $taskFinalCount = array();
            foreach ($taskData as $key => $value) {
                $taskArrayNew = new \stdClass();
                if (!in_array($value['name'], $taskArray)) {
                    if ($value['id'] == 70) {
                        $color = '#E63049';
                    } elseif ($value['id'] == 71) {
                        $color = '#269B8F';
                    } else {
                        $color = '#4690FF';
                    }
                    $taskArrayNew->total = 0;
                    $taskArrayNew->color = $color;
                    $taskArrayNew->text = $value['name'];
                    array_push($taskFinalCount, $taskArrayNew);
                } else {
                    $key = array_search($value['name'], $taskArray);
                    array_push($taskFinalCount, $data[$key]);
                }
            }

            return fractal()->item($taskFinalCount)->transformWith(new PatientCountTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Task List According to status
    public function statusTask($request)
    {
        try {
            $now = Carbon::today();
            $custommDate = date("Y-m-d", strtotime($now));
            $pastDate = Carbon::today()->subYears(2);
            $pastCutomDate = date("Y-m-d", strtotime($pastDate));
            if (!empty($request->fromDate)) {
                $fromDate = Helper::date($request->input('fromDate'));
            } else {
                $fromDate = $pastCutomDate . " 00:00:00";
            }

            if (!empty($request->toDate)) {
                $toDate = Helper::date($request->input('toDate'));
            } else {
                $toDate = $custommDate . " 23:59:59";
            }
            $tasks = DB::select(
                'CALL taskStatusCount("' . $fromDate . '","' . $toDate . '")'
            );
            $total = DB::select(
                'CALL totalTasksCount("' . $fromDate . '","' . $toDate . '")'
            );
            $taskArray = array();
            foreach ($tasks as $task) {
                array_push($taskArray, $task->text);
            }
            $flagData = GlobalCode::where('globalCodeCategoryId', '5')->get();
            $taskFinalCount = array();
            foreach ($flagData as $key => $value) {
                $taskArrayNew = new \stdClass();
                if (!in_array($value['name'], $taskArray)) {
                    if ($value['id'] == 61) {
                        $color = '#267DFF';
                    } elseif ($value['id'] == 62) {
                        $color = '#FF6061';
                    } else {
                        $color = '#62CFD7';
                    }
                    $taskArrayNew->total = 0;
                    $taskArrayNew->color = $color;
                    $taskArrayNew->text = $value['name'];
                    array_push($taskFinalCount, $taskArrayNew);
                } else {
                    $key = array_search($value['name'], $taskArray);
                    array_push($taskFinalCount, $tasks[$key]);
                }
            }
            $data = array_merge($taskFinalCount, $total);
            return fractal()->item($data)->transformWith(new PatientCountTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // List Task with Duration,Time for 24hurs
    public function taskTotalWithTimeDuration($request)
    {
        try {
            $timelineId = $request->timelineId;
            $data = DB::select(
                'CALL getTotalTaskSummaryCountInGraph()',
            );
            return fractal()->collection($data)->transformWith(new TaskDurationCountTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }

            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Task Complete Count
    public function taskCompletedRates($request)
    {
        try {
            $timelineId = $request->timelineId;
            $data = DB::select(
                'CALL taskCompletedRates()',
            );
            if (isset($data[0])) {
                $data = $data[0];
            }
            return fractal()->item($data)->transformWith(new PatientCountTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }

            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Update Task
    public function updateTask($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            if ($id == 'undefined') {
                $startDate = Helper::date($request->input('startDate'));
                $dueDate = Helper::date($request->input('dueDate'));
                $input = [
                    'udid' => Str::uuid()->toString(),
                    'title' => $request->title,
                    'description' => $request->input('description'),
                    'startDate' => $startDate,
                    'dueDate' => $dueDate,
                    'taskTypeId' => 69,
                    'priorityId' => $request->priority,
                    'taskStatusId' => $request->taskStatus,
                    'createdBy' => Auth::user()->id,
                    'providerId' => $provider,
                    'providerLocationId' => $providerLocation
                ];
                $task = Task::create($input);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'tasks', 'tableId' => $task->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
                $taskCategoryId = $request->taskCategory;
                foreach ($taskCategoryId as $taskCategory) {
                    $taskCate = [
                        'taskId' => $task->id,
                        'taskcategoryId' => $taskCategory,
                        'providerId' => $provider,
                        'providerLocationId' => $providerLocation
                    ];
                    $category = TaskCategory::create($taskCate);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'taskCategory', 'tableId' => $category->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($taskCate), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                }
                $assignedToId = $request->assignedTo;

                // email body message
                $msgObj = ConfigMessage::where("type", "taskUpdate")
                    ->where("entityType", "sendMail")
                    ->first();

                // email header
                $msgHeaderObj = ConfigMessage::where("type", "header")
                    ->where("entityType", "sendMail")
                    ->first();

                // email footer
                $msgFooterObj = ConfigMessage::where("type", "footer")
                    ->where("entityType", "sendMail")
                    ->first();

                $i = 0;
                $messageObj = array();
                $userIds = array();
                foreach ($assignedToId as $assignedTo) {
                    $assign = Helper::entity($request->entityType, $assignedTo);
                    $assigned = [
                        'taskId' => $task->id,
                        'assignedTo' => $assign,
                        'entityType' => $request->entityType,
                        'providerId' => $provider,
                        'providerLocationId' => $providerLocation
                    ];
                    $assignedData = TaskAssignedTo::create($assigned);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'taskAssignedTo', 'tableId' => $assignedData->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($assigned), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                    $fullName = "";
                    $userEmailDefined = 0;
                    $userPhoneDefined = 0;
                    if ($request->entityType == 'patient') {
                        $patient = Patient::with("user")->where('udid', $assignedTo)->first();
                        $userEmail[] = $patient->user->email;
                        $fullName = $patient->firstName . " " . $patient->lastName;
                        if (isset($patient->user->userDefined) && $patient->user->userDefined == 1) {
                            $userEmailDefined = 1;
                        }

                        if (isset($patient->userDefined) && $patient->userDefined == 1) {
                            $userPhoneDefined = 1;
                        }
                    } elseif ($request->entityType == 'staff') {
                        $userEmailDefined = 1;
                        $userPhoneDefined = 1;
                        $staff = Staff::with("user")->where('udid', $assignedTo)->first();
                        $userEmail[] = $staff->user->email;
                        $fullName = $staff->firstName . " " . $staff->lastName;
                        $userIds[$staff->user->email] = $staff->user->id;
                    }

                    $variablesArr = array(
                        "fullName" => $fullName,
                        "taskId" => $task->id
                    );

                    if (isset($msgObj->messageBody)) {
                        $messageBody = $msgObj->messageBody;

                        if (isset($msgHeaderObj->messageBody) && !empty($msgHeaderObj->messageBody)) {
                            $messageBody = $msgHeaderObj->messageBody . $messageBody;
                        }

                        if (isset($msgFooterObj->messageBody) && !empty($msgFooterObj->messageBody)) {
                            $messageBody = $messageBody . $msgFooterObj->messageBody;
                        }

                        $messageObj[] = Helper::getMessageBody($messageBody, $variablesArr);
                    }
                    $i++;
                }

                if ($request->entityType == 'staff') {

                    if (isset($userEmail) && !empty($userEmail) && $userEmailDefined == 1) {
                        $to = $userEmail;

                        if (isset($msgObj->otherParameter)) {
                            $otherParameter = json_decode($msgObj->otherParameter);
                            if (isset($otherParameter->fromName)) {
                                $fromName = $otherParameter->fromName;
                            } else {
                                $fromName = "Virtare Health";
                            }
                        } else {
                            $fromName = "Virtare Health";
                        }

                        if (isset($msgObj->subject)) {
                            $subject = $msgObj->subject;
                        } else {
                            $subject = "Update Task";
                        }
                        Helper::sendInBulkMail($to, $fromName, $messageObj, $subject, array(), $userIds, 'New Task', $task->id);
                    }
                }
                $taskData = Task::where('id', $task->id)->with('assignedTo.assigned', 'assignedTo.patient')->first();
                $message = ['message' => trans('messages.createdSuccesfully')];
                $result = fractal()->item($taskData)->transformWith(new TaskTransformer())->toArray();
                $data = array_merge($message, $result);
                return $data;
            } else {
                $input = [
                    'title' => $request->title,
                    'description' => $request->description,
                    'taskStatusId' => $request->taskStatus,
                    'priorityId' => $request->priority,
                    'updatedBy' => auth()->user()->id,
                    'providerId' => $provider,
                    'providerLocationId' => $providerLocation
                ];
                $taskBefore = Task::where('id', $id)->first();
                Task::where('id', $id)->update($input);
                $taskAfter = Task::where('id', $id)->first();
                $data = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation];

                $assignee = TaskAssignedTo::where('taskId', $id)->get();
                TaskAssignedTo::where('taskId', $id)->update($data);
                TaskAssignedTo::where('taskId', $id)->delete();
                $assignedToId = $request->assignedTo;
                // email body message
                $msgObj = ConfigMessage::where("type", "taskUpdate")
                    ->where("entityType", "sendMail")
                    ->first();
                // email header
                $msgHeaderObj = ConfigMessage::where("type", "header")
                    ->where("entityType", "sendMail")
                    ->first();
                // email footer
                $msgFooterObj = ConfigMessage::where("type", "footer")
                    ->where("entityType", "sendMail")
                    ->first();
                $i = 0;
                $messageObj = array();
                $userIds = array();
                foreach ($assignedToId as $assignedTo) {
                    $assign = Helper::entity($request->entityType, $assignedTo);
                    $assigned = [
                        'taskId' => $id,
                        'assignedTo' => $assign,
                        'entityType' => $request->entityType,
                        'createdBy' => Auth::id(),
                        'providerId' => $provider,
                        'providerLocationId' => $providerLocation
                    ];
                    TaskAssignedTo::create($assigned);

                    $fullName = "";
                    if ($request->entityType == 'patient') {
                        $patient = Patient::with("user")->where('id', $assign)->first();
                        foreach ($assignee as $value) {
                            $userId = auth()->user()->staff->id;
                            $user = Staff::where('id', $userId)->first();
                            if ($value->assignedTo == $assign) {
                                $timeLine = [
                                    'patientId' => $assign, 'heading' => 'Update Task', 'title' => '"' . $taskBefore->title . '"' . ' Task Updated <b> By' . ' ' . $user->firstName . ' ' . $user->lastName . '</b>', 'type' => 12,
                                    'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $provider
                                ];
                                PatientTimeLine::create($timeLine);
                            } else {
                                $timeLine = [
                                    'patientId' => $assign, 'heading' => 'New Task', 'title' => '"' . $request->title . '"' . ' Task Assigned <b> By' . ' ' . $user->firstName . ' ' . $user->lastName . '</b>', 'type' => 12,
                                    'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $provider
                                ];
                                PatientTimeLine::create($timeLine);
                            }
                        }
                        $fullName = $patient->firstName . " " . $patient->lastName;
                    }

                    if ($request->entityType == 'staff') {
                        $staff = Staff::with("user")->where('id', $assign)->first();
                        foreach ($assignee as $value) {
                            if ($value->assignedTo != $staff->id) {
                                $name = $staff->firstName . " " . $staff->lastName;
                                $userEmail[] = $staff->user->email;
                                $userIds[$staff->user->id] = $staff->user->email;
                            } elseif ($value->assignedTo == $staff->id) {
                                $nameBefore = $staff->firstName . " " . $staff->lastName;
                                $userEmail[] = $staff->user->email;
                                $userIds[$staff->user->id] = $staff->user->email;
                            }
                        }
                        $fullName = $staff->firstName . " " . $staff->lastName;
                        $UserId = $staff->userId;
                        if ($request->taskStatus) {
                            $status = GlobalCode::where('id', $request->taskStatus)->first();
                            $beforeStatus = $taskBefore->taskStatus->name;
                            $afterStatus = $status->name;
                        }
                        if ($request->priority) {
                            $prioriy = GlobalCode::where('id', $request->priority)->first();
                            $beforePriority = $taskBefore->priority->name;
                            $afterPriority = $prioriy->name;
                        }
                        $message = '';
                        if (!empty($name)) {
                            $message .= "<p><strong>You Have New Task Assigned Here's Details of the Task: </strong></p>";
                            $message .= '<p><strong>Status: </strong><span style="background-color:#e3fcef; padding: 5px 5px;">' . $afterStatus . '.</span></p>';
                            $message .= '<p><strong>Priority: </strong><span style="background-color:#e3fcef; padding: 5px 5px;">' . $afterPriority . '.</span></p>';
                            $message .= '<p><strong>Title: </strong><span style="background-color:#e3fcef; padding: 5px 5px;">' . $request->title . '.</span></p>';
                            $message .= '<p><strong>Description: </strong><span style="background-color:#e3fcef; padding: 5px 5px;">' . $request->description . '.</span></p>';
                        }
                        if ($request->taskStatus != $taskBefore->taskStatusId) {
                            if ($nameBefore) {
                                $message .= '<p><strong>Status: </strong> <del style="background-color: #ffebe6;padding: 5px 5px;">' . $beforeStatus . '</del> → <span style="background-color:#e3fcef; padding: 5px 5px;">' . $afterStatus . '.</span></p>';
                            }
                        }
                        if ($request->priority != $taskBefore->priorityId) {
                            if ($nameBefore) {
                                $message .= '<p><strong>Priority: </strong> <del style="background-color: #ffebe6;padding: 5px 5px;">' . $beforePriority . '</del> → <span style="background-color:#e3fcef; padding: 5px 5px;">' . $afterPriority . '.</span></p>';
                            }
                        }
                        if ($request->title != $taskBefore->title) {
                            if ($nameBefore) {
                                $message .= '<p><strong>Title: </strong> <del style="background-color: #ffebe6;padding: 5px 5px;">' . $taskBefore->title . '</del> → <span style="background-color:#e3fcef; padding: 5px 5px;">' . $request->title . '.</span></p>';
                            }
                        }
                        if ($request->description != $taskBefore->description) {
                            if ($nameBefore) {
                                $message .= '<p><strong>Description: </strong> <del style="background-color: #ffebe6;padding: 5px 5px;">' . $taskBefore->description . '</del> → <span style="background-color:#e3fcef; padding: 5px 5px;">' . $request->description . '.</span></p>';
                            }
                        }
                        $variablesArr = array(
                            "fullName" => $fullName,
                            "message" => $message
                        );
                        if (isset($msgObj->messageBody)) {
                            $messageBody = $msgObj->messageBody;
                            if (isset($msgHeaderObj->messageBody) && !empty($msgHeaderObj->messageBody)) {
                                $messageBody = $msgHeaderObj->messageBody . $messageBody;
                            }
                            if (isset($msgFooterObj->messageBody) && !empty($msgFooterObj->messageBody)) {
                                $messageBody = $messageBody . $msgFooterObj->messageBody;
                            }
                            $messageObj[] = Helper::getMessageBody($messageBody, $variablesArr);
                        }
                        $i++;
                    }
                }
                if ($request->entityType == 'staff') {

                    if (isset($userEmail) && !empty($userEmail)) {
                        $to = $userEmail;
                        if (isset($msgObj->otherParameter)) {
                            $otherParameter = json_decode($msgObj->otherParameter);
                            if (isset($otherParameter->fromName)) {
                                $fromName = $otherParameter->fromName;
                            } else {
                                $fromName = "Virtare Health";
                            }
                        } else {
                            $fromName = "Virtare Health";
                        }
                        $subject = $request->title;
                        Helper::sendInBulkMail($to, $fromName, $messageObj, $subject, array(), $userIds, 'Task Assigned', $taskBefore->id);
                    }
                }
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'tasks', 'tableId' => $id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
                $updatedData = Task::where('id', $id)->first();
                $message = ['message' => trans('messages.updatedSuccesfully')];
                $result = fractal()->item($updatedData)->transformWith(new TaskTransformer())->toArray();
                $endData = array_merge(
                    $message,
                    $result
                );
                return $endData;
            }
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Delete Task
    public function deleteTask($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $data = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            Task::where('id', $id)->update($data);
            $taskAssignTO = TaskAssignedTo::where('taskId', $id)->get();
            TaskAssignedTo::where('taskId', $id)->update($data);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'tasks', 'tableId' => $id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($data), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            Task::where('id', $id)->delete();
            TaskAssignedTo::where('taskId', $id)->delete();

            // email body message
            $msgObj = ConfigMessage::where("type", "taskDelete")
                ->where("entityType", "sendMail")
                ->first();

            // email header
            $msgHeaderObj = ConfigMessage::where("type", "header")
                ->where("entityType", "sendMail")
                ->first();

            // email footer
            $msgFooterObj = ConfigMessage::where("type", "footer")
                ->where("entityType", "sendMail")
                ->first();

            $i = 0;
            $messageObj = array();
            $userIds = array();
            foreach ($taskAssignTO as $val) {

                $fullName = "";
                if ($request->entityType == 'patient') {
                    $patient = Patient::with("user")->where('id', $val->assignedTo)->first();

                    if (isset($patient->user->userDefined) && $patient->user->userDefined == 1) {
                        $userEmail[] = $patient->user->email;
                    }
                    $fullName = $patient->firstName . " " . $patient->lastName;
                    $userIds[$patient->user->email] = $patient->user->id;
                } elseif ($request->entityType == 'staff') {
                    $staff = Staff::with("user")->where('udid', $val->assignedTo)->first();
                    $userEmail[] = $staff->user->email;
                    $fullName = $staff->firstName . " " . $staff->lastName;
                    $userIds[$staff->user->email] = $staff->user->id;
                }
                $variablesArr = array(
                    "fullName" => $fullName,
                    "taskId" => $id
                );
                if (isset($msgObj->messageBody)) {
                    $messageBody = $msgObj->messageBody;

                    if (isset($msgHeaderObj->messageBody) && !empty($msgHeaderObj->messageBody)) {
                        $messageBody = $msgHeaderObj->messageBody . $messageBody;
                    }

                    if (isset($msgFooterObj->messageBody) && !empty($msgFooterObj->messageBody)) {
                        $messageBody = $messageBody . $msgFooterObj->messageBody;
                    }

                    $messageObj[] = Helper::getMessageBody($messageBody, $variablesArr);
                }
                $i++;
            }
            if (isset($userEmail) && !empty($userEmail)) {
                $to = $userEmail;
                if (isset($msgObj->otherParameter)) {
                    $otherParameter = json_decode($msgObj->otherParameter);
                    if (isset($otherParameter->fromName)) {
                        $fromName = $otherParameter->fromName;
                    } else {
                        $fromName = "Virtare Health";
                    }
                } else {
                    $fromName = "Virtare Health";
                }
                if (isset($msgObj->subject)) {
                    $subject = $msgObj->subject;
                } else {
                    $subject = "New Task Added";
                }
                Helper::sendInBulkMail($to, $fromName, $messageObj, $subject, array(), $userIds, 'Task Deleted', $id);
            }

            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }

            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Get Task By Id
    public function taskById($request, $id)
    {
        try {
            $data = Task::select('tasks.*')->with('assignedTo');

            // $data->leftJoin('providers', 'providers.id', '=', 'tasks.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'tasks.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('tasks.providerLocationId', '=', 'providerLocations.id')->where('tasks.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('tasks.providerLocationId', '=', 'providerLocationStates.id')->where('tasks.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('tasks.providerLocationId', '=', 'providerLocationCities.id')->where('tasks.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('tasks.providerLocationId', '=', 'subLocations.id')->where('tasks.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('tasks.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['tasks.providerLocationId', $providerLocation], ['tasks.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['tasks.providerLocationId', $providerLocation], ['tasks.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['tasks.providerLocationId', $providerLocation], ['tasks.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['tasks.providerLocationId', $providerLocation], ['tasks.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['tasks.programId', $program], ['tasks.entityType', $entityType]]);
            // }
            $data = $data->where('tasks.id', $id)->first();
            return fractal()->item($data)->transformWith(new TaskTransformer())->toArray();
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Task Per Staff Count
    public function taskPerStaff($request)
    {
        try {
            $tasks = DB::select(
                'CALL taskPerStaff("' . Carbon::today() . '")',
            );
            return fractal()->item($tasks)->transformWith(new PatientCountTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Task Category Count
    public function taskPerCategory($request)
    {
        try {
            $tasks = DB::select(
                'CALL taskPerCategory("' . Carbon::today() . '")',
            );

            $taskArray = array();
            foreach ($tasks as $task) {
                array_push($taskArray, $task->text);
            }
            $taskData = GlobalCode::where('globalCodeCategoryId', '6')->get();
            $taskFinalCount = array();
            foreach ($taskData as $key => $value) {
                $taskArrayNew = new \stdClass();
                if (!in_array($value['name'], $taskArray)) {
                    // if ($value['id'] == 70) {
                    //     $color = '#E63049';
                    // } elseif ($value['id'] == 71) {
                    //     $color = '#269B8F';
                    // } else {
                    //     $color = '#4690FF';
                    // }
                    $taskArrayNew->total = 0;
                    // $taskArrayNew->color = $color;
                    $taskArrayNew->text = $value['name'];
                    array_push($taskFinalCount, $taskArrayNew);
                } else {
                    $key = array_search($value['name'], $taskArray);
                    array_push($taskFinalCount, $tasks[$key]);
                }
            }
            return fractal()->item($taskFinalCount)->transformWith(new PatientCountTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // List Assigned Task
    public function taskAssigneList($request, $id)
    {
        try {
            $patient = Helper::entity('patient', $id);
            $data = TaskAssignedTo::select('taskAssignedTo.*')->with('task', 'patient', 'assigned');

            $data->where([['taskAssignedTo.assignedTo', $patient], ['taskAssignedTo.entityType', 'patient']])
                ->leftJoin('tasks', 'tasks.id', '=', 'taskAssignedTo.taskId')
                ->leftJoin('taskCategory', 'taskCategory.taskId', '=', 'tasks.id')->whereNull('taskCategory.deletedAt')->whereNull('tasks.deletedAt')
                ->leftJoin('globalCodes as g1', 'g1.id', '=', 'taskCategory.taskCategoryId')
                ->leftJoin('globalCodes as g2', 'g2.id', '=', 'tasks.taskStatusId')
                ->leftJoin('globalCodes as g3', 'g3.id', '=', 'tasks.priorityId');

            // $data->leftJoin('providers', 'providers.id', '=', 'taskAssignedTo.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'taskAssignedTo.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('taskAssignedTo.providerLocationId', '=', 'providerLocations.id')->where('taskAssignedTo.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('taskAssignedTo.providerLocationId', '=', 'providerLocationStates.id')->where('taskAssignedTo.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('taskAssignedTo.providerLocationId', '=', 'providerLocationCities.id')->where('taskAssignedTo.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('taskAssignedTo.providerLocationId', '=', 'subLocations.id')->where('taskAssignedTo.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('taskAssignedTo.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['taskAssignedTo.providerLocationId', $providerLocation], ['taskAssignedTo.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['taskAssignedTo.providerLocationId', $providerLocation], ['taskAssignedTo.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['taskAssignedTo.providerLocationId', $providerLocation], ['taskAssignedTo.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['taskAssignedTo.providerLocationId', $providerLocation], ['taskAssignedTo.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['taskAssignedTo.programId', $program], ['taskAssignedTo.entityType', $entityType]]);
            // }


            if ($request->orderField == 'taskStatus') {
                $data->orderBy('g2.name', $request->orderBy);
            } elseif ($request->orderField == 'priority') {
                $data->orderBy('g3.name', $request->orderBy);
            } elseif ($request->orderField == 'category') {
                $data->orderBy('g1.name', $request->orderBy);
            } elseif ($request->orderField == 'dueDate') {
                $data->orderBy('tasks.dueDate', $request->orderBy);
            } elseif ($request->orderField == 'assignedBy') {
                $data->orderBy('tasks.createdBy', $request->orderBy);
            } elseif ($request->orderField == 'title') {
                $data->orderBy('tasks.title', $request->orderBy);
            } elseif (Schema::hasColumn('tasks', request()->orderField)) {
                $data->orderBy($request->orderField, $request->orderBy);
            } else {
                $data->orderBy('taskAssignedTo.createdAt', 'DESC');
            }
            $data = $data->groupBy('taskAssignedTo.id')->get();
            return fractal()->collection($data)->transformWith(new TaskAssignedToTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
