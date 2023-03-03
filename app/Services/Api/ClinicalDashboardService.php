<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use App\Models\Flag\Flag;
use App\Models\Task\Task;
use App\Models\Staff\Staff;
use App\Models\Client\CareTeam;
use App\Models\Patient\Patient;
use App\Models\Client\Site\Site;
use App\Library\ErrorLogGenerator;
use Illuminate\Support\Facades\DB;
use App\Models\Patient\PatientFlag;
use Illuminate\Support\Facades\Auth;
use App\Models\Client\CareTeamMember;
use App\Models\Escalation\Escalation;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Escalation\EscalationStaff;
use App\Transformers\Patient\PatientCountTransformer;
use App\Transformers\Patient\NewPatientCountTransformer;


class ClinicalDashboardService
{

    // Escalation Count
    public function countEscalation($request)
    {
        try {

            $result = [];
            $fromDateStr = Helper::date($request->input('fromDate'));
            $toDateStr = Helper::date($request->input('toDate'));
            $global = GlobalCode::where("globalCodeCategoryId", 74)->get();
            $i = 0;
            foreach ($global as $g) {
                $data = Escalation::where('typeId', $g->id)->whereBetween('createdAt', [$fromDateStr, $toDateStr])->count();
                $result[$i]["text"] = $g->name;
                $result[$i]["total"] = $data;
                $result[$i]["color"] = $g->color;
                $i++;
            }
            if (!empty($result)) {
                return response()->json(['data' => $result], 200);
            } else {
                return response()->json(['data' => $result]);
            }

            //return fractal()->collection($data)->transformWith(new EscalationCountTransformer())->toArray();
            // $fromDate =  $request->input('fromDate');
            // $toDate =  $request->input('toDate');
            // $fromDateStr =  Helper::date($request->input('fromDate'));
            // $toDateStr =  Helper::date($request->input('toDate'));
            // if ($fromDate < $toDate) {
            //     $seconds_diff = $toDate - $fromDate;
            // } else {
            //     $seconds_diff = $fromDate - $toDate;
            // }
            // $idx = auth()->user()->id;
            // // if(auth()->user()->roleId==3){
            // //     $idx = auth()->user()->staff->id;
            // // }else{
            // //     $idx = '';
            // // }

            // $staff = Staff::where("userId", $idx)->first();

            // if ($seconds_diff / 3600 <= 24) {
            //     $escalationType = "261"; // flag
            //     $data[] = $this->getEscalation($request, $idx, $staff, $escalationType);
            //     $escalationType = "260"; // Notes
            //     $data[] = $this->getEscalation($request, $idx, $staff, $escalationType);
            //     $escalationType = "259"; // vital
            //     $data[] = $this->getEscalation($request, $idx, $staff, $escalationType);
            //     $escalationType = "262"; // care plan
            //     $data[] = $this->getEscalation($request, $idx, $staff, $escalationType);
            //     // return $data;
            //     // $data = DB::select(
            //     //     "CALL escalationCount('" . $idx . "','" . $fromDateStr . "','" . $toDateStr . "')"
            //     // );
            // } elseif ($seconds_diff / 3600 / 24 <= 8) {
            //     $escalationType = "261"; // flag
            //     $data[] = $this->getEscalation($request, $idx, $staff, $escalationType);
            //     $escalationType = "260"; // Notes
            //     $data[] = $this->getEscalation($request, $idx, $staff, $escalationType);
            //     $escalationType = "259"; // vital
            //     $data[] = $this->getEscalation($request, $idx, $staff, $escalationType);
            //     $escalationType = "262"; // care plan
            //     $data[] = $this->getEscalation($request, $idx, $staff, $escalationType);
            //     // $data = $this->getEscalation($request,$idx,$staff,$escalationType);
            //     // $data = DB::select(
            //     //     "CALL escalationWeekCount('" . $idx . "','" . $fromDateStr . "','" . $toDateStr . "')"
            //     // );
            // } elseif ($seconds_diff / 3600 / 24 <= 32) {
            //     $escalationType = "261"; // flag
            //     $data[] = $this->getEscalation($request, $idx, $staff, $escalationType);
            //     $escalationType = "260"; // Notes
            //     $data[] = $this->getEscalation($request, $idx, $staff, $escalationType);
            //     $escalationType = "259"; // vital
            //     $data[] = $this->getEscalation($request, $idx, $staff, $escalationType);
            //     $escalationType = "262"; // care plan
            //     $data[] = $this->getEscalation($request, $idx, $staff, $escalationType);
            //     // $data = $this->getEscalation($request,$idx,$staff,$escalationType);
            //     // $data = DB::select(
            //     //     "CALL escalationMonthCount('" . $idx . "','" . $fromDateStr . "','" . $toDateStr . "')"
            //     // );
            // } elseif ($seconds_diff / 3600 / 24 <= 366) {
            //     $escalationType = "261"; // flag
            //     $data[] = $this->getEscalation($request, $idx, $staff, $escalationType);
            //     $escalationType = "260"; // Notes
            //     $data[] = $this->getEscalation($request, $idx, $staff, $escalationType);
            //     $escalationType = "259"; // vital
            //     $data[] = $this->getEscalation($request, $idx, $staff, $escalationType);
            //     $escalationType = "262"; // care plan
            //     $data[] = $this->getEscalation($request, $idx, $staff, $escalationType);
            //     // $data = $this->getEscalation($request,$idx,$staff,$escalationType);
            //     // $data = DB::select(
            //     //     "CALL escalationYearCount('" . $idx . "','" . $fromDateStr . "','" . $toDateStr . "')"
            //     // );
            // }
            // return $data;
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // public function getEscalation($request, $userId, $staff, $escalationType)
    // {
    //     try {
    //         $data = $data = EscalationStaff::with("escalationVital", "escalationNotes", "escalationFlag", "escalationCarePlan")
    //             ->Select("escalations.*", DB::raw('hour(escalations.createdAt) as time'), "globalCodes.name as  text", "escalationStaff.staffId", "patients.firstName", "patients.lastName", "patients.udid as patientUdid", "flags.name as flagName", "flags.color as flagColor")
    //             ->join('escalations', 'escalationStaff.escalationId', '=', 'escalations.escalationId')
    //             ->join('patients', 'patients.id', '=', 'escalations.referenceId')
    //             ->join('escalationTypes', 'escalationTypes.escalationId', '=', 'escalations.escalationId')
    //             ->join('globalCodes', 'globalCodes.id', '=', 'escalationTypes.escalationTypeId')
    //             ->join('escalationFlag', 'escalations.escalationId', '=', 'escalationFlag.escalationId')
    //             ->join('flags', 'flags.id', '=', 'escalationFlag.flagId')
    //             ->whereNull('escalationTypes.deletedAt')->whereNull('escalations.deletedAt')->where('escalationStaff.isActive', 1);
    //         if ($escalationType) {
    //             $data->where('escalationTypes.escalationTypeId', $escalationType);
    //             $data->where('escalationTypes.isActive', 1);
    //         }
    //         $data->with('escalationType', function ($query) use ($request) {
    //             $query->select("escalationTypes.*", "globalCodes.name as globalCodeName");
    //             $query->join('globalCodes', 'globalCodes.id', '=', 'escalationTypes.escalationTypeId');

    //             $query->where("escalationTypes.isActive", 1);
    //         });
    //         $data->where(function ($query) use ($userId, $staff) {
    //             $query->where("escalationStaff.staffId", $staff->id);
    //             $query->orWhere('escalations.createdBy', $userId);
    //         });
    //         if (!empty($request->fromDate) && !empty($request->toDate)) {
    //             $fromDateStr = Helper::date($request->fromDate);
    //             $toDateStr = Helper::date($request->toDate);
    //             $data->whereBetween('escalations.createdAt', [$fromDateStr, $toDateStr]);
    //         }
    //         $data->orderBy('escalationId', 'DESC');
    //         $data->groupBy('escalations.escalationId');
    //         $data = $data->get();
    //         if (!empty($data)) {
    //             $data = $data->toArray();
    //             $result["total"] = count($data);
    //             $g = GlobalCode::where("id", $escalationType)->first();
    //             $result["text"] = $g->name;
    //             $result["id"] = $escalationType;
    //             $result["time"] = isset($data[0]) ? $data[0]["time"] : "";
    //             return $result;
    //         }
    //     } catch (\Exception $e) {
    //         throw new \RuntimeException($e);
    //     }
    // }

    // Get Task Count
    public function countTask($request): array
    {
        try {
            // $provider = request()->providerId ? Helper::providerId() : '';
            // $providerLocation = request()->providerLocation ? Helper::providerLocationId() : '';
            // $entityType = request()->entityType ? Helper::entityType() : '';

            if (auth()->user()->roleId == 3) {
                $staffId = Helper::haveAccessAction(null, 490) ? '' : auth()->user()->staff->id;
            } else {
                $staffId = '';
            }
            $fromDateStr = Helper::dateOnly($request->input('fromDate'));
            $toDateStr = Helper::dateOnly($request->input('toDate'));
            // $dueDate = Carbon::today();
            // DB::enableQueryLog(); // Enable query log
            $data = Task::select(DB::raw('COUNT(taskCategory.taskCategoryId) as total,g1.name AS text, hour(tasks.dueDate) as time'))
                ->leftJoin('taskCategory', 'taskCategory.taskId', '=', 'tasks.id')
                ->leftJoin('globalCodes as g1', 'g1.id', '=', 'taskCategory.taskCategoryId')
                ->leftJoin('taskAssignedTo', 'taskAssignedTo.taskId', '=', 'tasks.id')
                ->whereNull('tasks.deletedAt')
                ->whereNull('taskCategory.deletedAt');

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
            //     $data->where('tasks.providerLocationId', $providerLocation);
            // }

            // if ($request->entityType) {
            //     $data->where('tasks.entityType', $entityType);
            // }

            if ($staffId) {
                $data->where('taskAssignedTo.assignedTo', $staffId);
                $data->where('taskAssignedTo.entityType', 'staff');
            }
            $data->whereBetween('dueDate', [$fromDateStr, $toDateStr]);
            $data = $data->groupBy('taskCategory.taskCategoryId')->get();
            // dd(DB::getQueryLog()); // Show results of log
            return $data->toArray();
            // print_r($data->toArray());
            // die;
            // $data = DB::select(
            //     "CALL taskCount('" . $provider . "','" . $providerLocation . "','" . $entityType . "','" . $fromDateStr . "','" . $toDateStr . "','" . $staffId . "','" . $dueDate . "')"
            // );
            // return $data;
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // patient flag Count
    public function patientFlagListCount($request)
    {
        try {
            // $provider = Helper::providerId();
            // $providerLocation = Helper::providerLocationId();
            // if ($request->input('fromDate') && $request->input('toDate')) {
            //     $fromDateStr = Helper::date($request->input('fromDate'));
            //     $toDateStr = Helper::date($request->input('toDate'));
            // } else {
            //     $fromDateStr = '';
            //     $toDateStr = '';
            // }
            // $date1 = date_create($fromDateStr);
            // $date2 = date_create($toDateStr);
            // $diff = date_diff($date1, $date2);
            // $diffrence = $diff->format("%a");
            // $flag = '';
            // $staffId = auth()->user()->staff->id;
            // if (auth()->user()->roleId == 3) {
            //     if (Helper::haveAccessAction(null, 490)) {
            //         if ($diffrence < 3) {
            //             $data = DB::select(
            //                 "CALL patientFlagDayCount('" . $flag . "','" . $provider . "','" . $providerLocation . "')"
            //             );
            //         } else {
            //             $data = DB::select(
            //                 "CALL getPatientConditionsCount('" . $fromDateStr . "','" . $provider . "','" . $providerLocation . "')",
            //             );
            //         }
            //     } else {
            //         if ($diffrence < 3) {
            //             $data = DB::select(
            //                 "CALL patientFlagDayStaffCount('" . $flag . "','" . $staffId . "','" . $provider . "','" . $providerLocation . "')"
            //             );
            //         } else {
            //             $data = DB::select(
            //                 "CALL getPatientConditionsStaffCount('" . $fromDateStr . "','" . $staffId . "','" . $provider . "','" . $providerLocation . "')",
            //             );
            //         }
            //     }
            // } else {
            //     if (auth()->user()->roleId == 1) {
            //         if ($diffrence < 3) {
            //             $data = DB::select(
            //                 "CALL patientFlagDayCount('" . $flag . "','" . $provider . "','" . $providerLocation . "')"
            //             );
            //         } else {
            //             $data = DB::select(
            //                 "CALL getPatientConditionsCount('" . $fromDateStr . "','" . $provider . "','" . $providerLocation . "')",
            //             );
            //         }
            //     }else{
            //         if ($diffrence < 3) {
            //             $data = DB::select(
            //                 "CALL patientFlagDayStaffCount('" . $flag . "','" . $staffId . "','" . $provider . "','" . $providerLocation . "')"
            //             );
            //         } else {
            //             $data = DB::select(
            //                 "CALL getPatientConditionsStaffCount('" . $fromDateStr . "','" . $staffId . "','" . $provider . "','" . $providerLocation . "')",
            //             );
            //         }
            //     }

            // if ($diffrence < 3) {
            //     $data = DB::select(
            //         "CALL patientFlagDayCount('" . $flag . "','" . $provider . "','" . $providerLocation . "')"
            //     );
            // } else {
            //     $data = DB::select(
            //         "CALL getPatientConditionsCount('" . $fromDateStr . "','" . $provider . "','" . $providerLocation . "')",
            //     );
            // }
            // }
            // $flagArray = array();
            // foreach ($data as $dataFlag) {
            //     array_push($flagArray, $dataFlag->text);
            // }
            // $flagData = Flag::whereHas('typeId', function ($query) {
            //     $query->where('name', 'Patient')->orWhere('name', 'Both');
            // })->get();
            // $flagFinalCount = array();
            // foreach ($flagData as $key => $value) {
            //     $flagArrayNew = new \stdClass();
            //     if (!in_array($value['name'], $flagArray)) {
            //         $flagArrayNew->total = 0;
            //         $flagArrayNew->color = $value['color'];
            //         $flagArrayNew->text = $value['name'];
            //         $flagArrayNew->textColor = "#FFFFFF";
            //         $flagArrayNew->flagId = $value['id'];
            //         array_push($flagFinalCount, $flagArrayNew);
            //     } else {
            //         $key = array_search($value['name'], $flagArray);
            //         array_push($flagFinalCount, $data[$key]);
            //     }
            // }
            // return $flagFinalCount;

            // $fromDateStr = Helper::date($request->input('fromDate'));
            // $toDateStr = Helper::date($request->input('toDate'));
            // if ($fromDateStr && $toDateStr) {
            //     $fromDate = $fromDateStr;
            //     $toDate = $toDateStr;
            // } else {
            //     $fromDate = '';
            //     $toDate = '';
            // }
            // $date1 = date_create($fromDateStr);
            // $date2 = date_create($toDateStr);
            // $diff = date_diff($date1, $date2);
            // $diffrence = $diff->format("%a");
            // $flag = '';
            // $staffId = auth()->user()->staff->id;
            // if (auth()->user()->roleId == 3) {
            //     if (Helper::haveAccessAction(null, 490)) {
            //         if ($diffrence < 3) {
            //             $data = DB::select(
            //                 "CALL patientFlagDayCount('" . $flag . "','" . $provider . "','" . $providerLocation . "')"
            //             );
            //         } else {
            //             $data = DB::select(
            //                 "CALL getPatientConditionsCount('" . $fromDateStr . "','" . $provider . "','" . $providerLocation . "')",
            //             );
            //         }
            //     } else {
            //         if ($diffrence < 3) {
            //             $data = DB::select(
            //                 "CALL patientFlagDayStaffCount('" . $flag . "','" . $staffId . "','" . $provider . "','" . $providerLocation . "')"
            //             );
            //         } else {
            //             $data = DB::select(
            //                 "CALL getPatientConditionsStaffCount('" . $fromDateStr . "','" . $staffId . "','" . $provider . "','" . $providerLocation . "')",
            //             );
            //         }
            //     }
            // } else {
            //     if (auth()->user()->roleId == 1) {
            //         if ($diffrence < 3) {
            //             $data = DB::select(
            //                 "CALL patientFlagDayCount('" . $flag . "','" . $provider . "','" . $providerLocation . "')"
            //             );
            //         } else {
            //             $data = DB::select(
            //                 "CALL getPatientConditionsCount('" . $fromDateStr . "','" . $provider . "','" . $providerLocation . "')",
            //             );
            //         }
            //     } else {
            //         if ($diffrence < 3) {
            //             $data = DB::select(
            //                 "CALL patientFlagDayStaffCount('" . $flag . "','" . $staffId . "','" . $provider . "','" . $providerLocation . "')"
            //             );
            //         } else {
            //             $data = DB::select(
            //                 "CALL getPatientConditionsStaffCount('" . $fromDateStr . "','" . $staffId . "','" . $provider . "','" . $providerLocation . "')",
            //             );
            //         }
            //     }

            //     // if ($diffrence < 3) {
            //     //     $data = DB::select(
            //     //         "CALL patientFlagDayCount('" . $flag . "','" . $provider . "','" . $providerLocation . "')"
            //     //     );
            //     // } else {
            //     //     $data = DB::select(
            //     //         "CALL getPatientConditionsCount('" . $fromDateStr . "','" . $provider . "','" . $providerLocation . "')",
            //     //     );
            //     // }
            // }
            // $flagArray = array();
            // foreach ($data as $dataFlag) {
            //     array_push($flagArray, $dataFlag->text);
            // }
            // $flagData = Flag::whereHas('typeId', function ($query) {
            //     $query->where('name', 'Patient')->orWhere('name', 'Both');
            // })->get();
            // $flagFinalCount = array();
            // foreach ($flagData as $key => $value) {
            //     $flagArrayNew = new \stdClass();
            //     if (!in_array($value['name'], $flagArray)) {
            //         $flagArrayNew->total = 0;
            //         $flagArrayNew->color = $value['color'];
            //         $flagArrayNew->text = $value['name'];
            //         $flagArrayNew->textColor = "#FFFFFF";
            //         $flagArrayNew->flagId = $value['id'];
            //         array_push($flagFinalCount, $flagArrayNew);
            //     } else {
            //         $key = array_search($value['name'], $flagArray);
            //         array_push($flagFinalCount, $data[$key]);
            //     }
            // }
            // return $flagFinalCount;
            $fromDateStr = Helper::date($request->input('fromDate'));
            $toDateStr = Helper::date($request->input('toDate'));
            if ($fromDateStr && $toDateStr) {
                $fromDate = $fromDateStr;
                $toDate = $toDateStr;
            } else {
                $fromDate = '';
                $toDate = '';
            }
            return $this->patientFlags($request, $fromDate, $toDate);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Appointment Count
    public function countAppointment($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $fromDateStr = Helper::date($request->input('fromDate'));
            $toDateStr = Helper::date($request->input('toDate'));
            if (auth()->user()->roleId == 3) {
                $idx = auth()->user()->staff->id;
            } else {
                $idx = '';
            }
            $data = DB::select(
                "CALL appointmentTodayCount('" . $fromDateStr . "','" . $toDateStr . "','" . $idx . "','" . $provider . "','" . $providerLocation . "')"
            );
            return fractal()->collection($data)->transformWith(new NewPatientCountTransformer())->toArray();
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

    // Patient Count
    public function count($request)
    {
        try {
            // $provider = Helper::providerId();
            // $providerLocation = Helper::providerLocationId();
            // $fromDate = Helper::date($request->input('fromDate'));
            // $toDate = Helper::date($request->input('toDate'));
            // $date1 = date_create($fromDate);
            // $date2 = date_create($toDate);
            // $diff = date_diff($date1, $date2);
            // $diffrence = $diff->format("%a");
            // if (auth()->user()->roleId == 3) {
            //     $staff = PatientStaff::selectRaw("group_concat(patientId) as patientId")->where('staffId', auth()->user()->staff->id)->groupBy("staffId")->first();
            //     if ($staff) {
            //         $type = explode(',', $staff->patientId);
            //         // $idx = Patient::whereIn('id', $type)->get();
            //         $idx = '';
            //     } else {
            //         $idx = '';
            //     }
            // } else {
            //     $idx = '';
            // }
            // $staffIdx = auth()->user()->staff->id;
            // if (auth()->user()->roleId == 3) {
            // if (Helper::haveAccessAction(null, 490)) {
            //     $total = DB::select(
            //         "CALL getTotalPatientsCount('" . $idx . "','" . $provider . "','" . $providerLocation . "')",
            //     );
            //     if ($diffrence < 3) {
            //         $count = DB::select(
            //             "CALL patientFlagDayCount('','" . $provider . "','" . $providerLocation . "')",
            //         );
            //     } else {
            //         $count = DB::select(
            //             "CALL getPatientConditionsCount('" . $fromDate . "','" . $provider . "','" . $providerLocation . "')",
            //         );
            //     }
            //     $countNew = DB::select(
            //         "CALL getNewPatientCount('" . $fromDate . "','" . $toDate . "','" . $provider . "','" . $providerLocation . "')",
            //     );
            //     $patientActive = DB::select(
            //         "CALL getActivePatientCount('" . $idx . "','" . $provider . "','" . $providerLocation . "')",
            //     );
            //     $patientInActive = DB::select(
            //         "CALL getInActivePatientCount('" . $idx . "','" . $provider . "','" . $providerLocation . "')",
            //     );
            // } else {
            //     $total = DB::select(
            //         "CALL getTotalPatientsStaffCount('" . $idx . "','" . $staffIdx . "','" . $provider . "','" . $providerLocation . "')",
            //     );
            //     if ($diffrence < 3) {
            //         $count = DB::select(
            //             "CALL patientFlagDayStaffCount('" . '' . "','" . $staffIdx . "')",
            //         );
            //     } else {
            //         $count = DB::select(
            //             "CALL getPatientConditionsStaffCount('" . $fromDate . "','" . $staffIdx . "')",
            //         );
            //     }
            //     $countNew = DB::select(
            //         "CALL getNewPatientStaffCount('" . $fromDate . "','" . $toDate . "','" . $staffIdx . "')",
            //     );
            //     $patientActive = DB::select(
            //         "CALL getActivePatientStaffCount('" . $idx . "','" . $staffIdx . "')",
            //     );
            //     $patientInActive = DB::select(
            //         "CALL getInActivePatientStaffCount('" . $idx . "','" . $staffIdx . "')",
            //     );
            //     // }
            // } else {

            //     $total = DB::select(
            //         "CALL getTotalPatientsCount('" . $idx . "','" . $provider . "','" . $providerLocation . "')",
            //     );

            //     if ($diffrence < 3) {
            //         $count = DB::select(
            //             "CALL patientFlagDayCount('','" . $provider . "','" . $providerLocation . "')",
            //         );
            //     } else {
            //         $count = DB::select(
            //             "CALL getPatientConditionsCount('" . $fromDate . "','" . $provider . "','" . $providerLocation . "')",
            //         );
            //     }

            //     $countNew = DB::select(
            //         "CALL getNewPatientCount('" . $fromDate . "','" . $toDate . "','" . $provider . "','" . $providerLocation . "')",
            //     );
            //     $patientActive = DB::select(
            //         "CALL getActivePatientCount('" . $idx . "','" . $provider . "','" . $providerLocation . "')",
            //     );
            //     $patientInActive = DB::select(
            //         "CALL getInActivePatientCount('" . $idx . "','" . $provider . "','" . $providerLocation . "')",
            //     );
            // }
            // $flagArray = array();
            // foreach ($count as $flag) {
            //     array_push($flagArray, $flag->text);
            // }
            // $flagData = Flag::whereHas('typeId', function ($query) {
            //     $query->where('name', 'Patient')->orWhere('name', 'Both');
            // })->get();
            // $flagFinalCount = array();
            // foreach ($flagData as $key => $value) {
            //     $flagArrayNew = new \stdClass();
            //     if (!in_array($value['name'], $flagArray)) {
            //         $flagArrayNew->total = 0;
            //         $flagArrayNew->color = $value['color'];
            //         $flagArrayNew->text = $value['name'];
            //         $flagArrayNew->textColor = "#FFFFFF";
            //         $flagArrayNew->flagId = $value['id'];
            //         array_push($flagFinalCount, $flagArrayNew);
            //     } else {
            //         $key = array_search($value['name'], $flagArray);
            //         array_push($flagFinalCount, $count[$key]);
            //     }
            // }
            // if (empty($patientInActive)) {
            //     $inActiveArrayNew = new \stdClass();
            //     $inActiveArrayNew->total = 0;
            //     $inActiveArrayNew->color = '#0FB5C2';
            //     $inActiveArrayNew->text = 'inactivePatients';
            //     $inActiveArrayNew->textColor = "#FFFFFF";
            //     array_push($patientInActive, $inActiveArrayNew);
            // }
            // $data = array_merge(
            //     $flagFinalCount,
            //     $patientActive,
            //     $patientInActive,
            //     $countNew,
            //     $total
            // );
            // return fractal()->item($data)->transformWith(new PatientCountTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();


            // $fromDateStr = Helper::date($request->input('fromDate'));
            // $toDateStr = Helper::date($request->input('toDate'));
            // if ($fromDateStr && $toDateStr) {
            //     $fromDate = $fromDateStr;
            //     $toDate = $toDateStr;
            // } else {
            //     $fromDate = '';
            //     $toDate = '';
            // }
            // $patientFlag = $this->patientFlags($request, $fromDate, $toDate);
            // $patientTotal = $this->patientTotal($fromDate, $toDate);
            // $patientNew = $this->patientNew($fromDate, $toDate);
            // $patientActive = $this->patientActive($fromDate, $toDate);
            // $patientInActive = $this->patientInActive($fromDate, $toDate);
            // $data = array_merge($patientFlag, $patientTotal->toArray(), $patientNew->toArray(), $patientActive->toArray(), $patientInActive->toArray());
            // return fractal()->item($data)->transformWith(new PatientCountTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();

            // return $data;

            //            $fromDateStr = Helper::date($request->input('fromDate'));
            //            $toDateStr = Helper::date($request->input('toDate'));
            //            if ($fromDateStr && $toDateStr) {
            //                $fromDate = $fromDateStr;
            //                $toDate = $toDateStr;
            //            } else {
            //                $fromDate = '';
            //                $toDate = '';
            //            }
            //            $staffIdx = auth()->user()->staff->id;
            //            if (auth()->user()->roleId == 3) {
            //                if (Helper::haveAccessAction(null, 490)) {
            //                    $total = DB::select(
            //                        "CALL getTotalPatientsCount('" . $idx . "','" . $provider . "','" . $providerLocation . "')",
            //                    );
            //                    if ($diffrence < 3) {
            //                        $count = DB::select(
            //                            "CALL patientFlagDayCount('','" . $provider . "','" . $providerLocation . "')",
            //                        );
            //                    } else {
            //                        $count = DB::select(
            //                            "CALL getPatientConditionsCount('" . $fromDate . "','" . $provider . "','" . $providerLocation . "')",
            //                        );
            //                    }
            //                    $countNew = DB::select(
            //                        "CALL getNewPatientCount('" . $fromDate . "','" . $toDate . "','" . $provider . "','" . $providerLocation . "')",
            //                    );
            //                    $patientActive = DB::select(
            //                        "CALL getActivePatientCount('" . $idx . "','" . $provider . "','" . $providerLocation . "')",
            //                    );
            //                    $patientInActive = DB::select(
            //                        "CALL getInActivePatientCount('" . $idx . "','" . $provider . "','" . $providerLocation . "')",
            //                    );
            //                } else {
            //                    $total = DB::select(
            //                        "CALL getTotalPatientsStaffCount('" . $idx . "','" . $staffIdx . "','" . $provider . "','" . $providerLocation . "')",
            //                    );
            //                    if ($diffrence < 3) {
            //                        $count = DB::select(
            //                            "CALL patientFlagDayStaffCount('" . '' . "','" . $staffIdx . "')",
            //                        );
            //                    } else {
            //                        $count = DB::select(
            //                            "CALL getPatientConditionsStaffCount('" . $fromDate . "','" . $staffIdx . "')",
            //                        );
            //                    }
            //                    $countNew = DB::select(
            //                        "CALL getNewPatientStaffCount('" . $fromDate . "','" . $toDate . "','" . $staffIdx . "')",
            //                    );
            //                    $patientActive = DB::select(
            //                        "CALL getActivePatientStaffCount('" . $idx . "','" . $staffIdx . "')",
            //                    );
            //                    $patientInActive = DB::select(
            //                        "CALL getInActivePatientStaffCount('" . $idx . "','" . $staffIdx . "')",
            //                    );
            //                }
            //            } else {
            //
            //                $total = DB::select(
            //                    "CALL getTotalPatientsCount('" . $idx . "','" . $provider . "','" . $providerLocation . "')",
            //                );
            //
            //                if ($diffrence < 3) {
            //                    $count = DB::select(
            //                        "CALL patientFlagDayCount('','" . $provider . "','" . $providerLocation . "')",
            //                    );
            //                } else {
            //                    $count = DB::select(
            //                        "CALL getPatientConditionsCount('" . $fromDate . "','" . $provider . "','" . $providerLocation . "')",
            //                    );
            //                }
            //
            //                $countNew = DB::select(
            //                    "CALL getNewPatientCount('" . $fromDate . "','" . $toDate . "','" . $provider . "','" . $providerLocation . "')",
            //                );
            //                $patientActive = DB::select(
            //                    "CALL getActivePatientCount('" . $idx . "','" . $provider . "','" . $providerLocation . "')",
            //                );
            //                $patientInActive = DB::select(
            //                    "CALL getInActivePatientCount('" . $idx . "','" . $provider . "','" . $providerLocation . "')",
            //                );
            //
            //            }
            //            $flagArray = array();
            //            foreach ($count as $flag) {
            //                array_push($flagArray, $flag->text);
            //            }
            //            $flagData = Flag::whereHas('typeId', function ($query) {
            //                $query->where('name', 'Patient')->orWhere('name', 'Both');
            //            })->get();
            //            $flagFinalCount = array();
            //            foreach ($flagData as $key => $value) {
            //                $flagArrayNew = new \stdClass();
            //                if (!in_array($value['name'], $flagArray)) {
            //                    $flagArrayNew->total = 0;
            //                    $flagArrayNew->color = $value['color'];
            //                    $flagArrayNew->text = $value['name'];
            //                    $flagArrayNew->textColor = "#FFFFFF";
            //                    $flagArrayNew->flagId = $value['id'];
            //                    array_push($flagFinalCount, $flagArrayNew);
            //                } else {
            //                    $key = array_search($value['name'], $flagArray);
            //                    array_push($flagFinalCount, $count[$key]);
            //                }
            //            }
            //            if (empty($patientInActive)) {
            //                $inActiveArrayNew = new \stdClass();
            //                $inActiveArrayNew->total = 0;
            //                $inActiveArrayNew->color = '#0FB5C2';
            //                $inActiveArrayNew->text = 'inactivePatients';
            //                $inActiveArrayNew->textColor = "#FFFFFF";
            //                array_push($patientInActive, $inActiveArrayNew);
            //            }
            //            $data = array_merge(
            //                $flagFinalCount,
            //                $patientActive,
            //                $patientInActive,
            //                $countNew,
            //                $total
            //            );
            //            return fractal()->item($data)->transformWith(new PatientCountTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();


            if ($request->fromDate && $request->fromDate) {
                $fromDate = Helper::date($request->input('fromDate'));
                $toDate = Helper::date($request->input('toDate'));
            } else {
                $fromDate = '';
                $toDate = '';
            }
            $patientFlag = $this->patientFlags($request, $fromDate, $toDate);
            $patientTotal = $this->patientTotal();
            $patientNew = $this->patientNew($fromDate, $toDate);
            $patientActive = $this->patientActive();
            $patientInActive = $this->patientInActive();
            $data = array_merge($patientFlag, $patientActive->toArray(), $patientInActive->toArray(), $patientNew->toArray(), $patientTotal->toArray());
            return fractal()->item($data)->transformWith(new PatientCountTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Get Patient Flags Count
    public function patientFlags($request, $fromDate, $toDate)
    {
        $patient = PatientFlag::select(DB::raw('COUNT(patientFlags.flagId) as total,flags.name AS text,
            flags.color AS color, flags.id AS flagId'))
            ->leftJoin('patients', 'patients.id', '=', 'patientFlags.patientId')
            ->leftJoin('flags', 'flags.id', '=', 'patientFlags.flagId')
            ->leftJoin('patientProviders', 'patientProviders.patientId', '=', 'patients.id')
            ->leftJoin('globalCodes', 'globalCodes.id', '=', 'flags.type');
        $patient->whereIn('globalCodes.name', ['Patient', 'Both']);
        $patient->whereNull('patientFlags.deletedAt')->whereNull('flags.deletedAt')->whereNull('patients.deletedAt');
        if ($fromDate && $toDate) {
            $date1 = date_create($fromDate);
            $date2 = date_create($toDate);
            $diff = date_diff($date1, $date2);
            $diffrence = $diff->format("%a");
            if ($diffrence < 3) {
                $patient;
            } else {
                $patient->where('patientFlags.createdAt', '>=', 'fromDate');
            }
        }
        $siteHead = Site::where(['siteHead' => Auth::id()])->first();
        if (auth()->user()->roleId == 2) {
            $client = Staff::where(['userId' => Auth::id()])->get('clientId');
            $careTeam = CareTeam::whereIn('clientId', $client)->get('id');
            $patient->whereIn('patientProviders.providerId', $careTeam);
            $patient->orWhere(function ($query) {
                $query->where('patients.createdBy', Auth::id());
            });
        } elseif (auth()->user()->roleId == 5 || auth()->user()->roleId == 7 || auth()->user()->roleId == 9) {
            if ($siteHead) {
                $careTeam = CareTeam::where(['siteId' => $siteHead->id])->get('id');
            } else {
                $careTeam = CareTeamMember::where(['contactId' => Auth::id()])->get('careTeamId');
            }
            $patient->whereIn('patientProviders.providerId', $careTeam);
        } else {
            $patient;
        }
        $patient = $patient->groupBy('flagId')->get();
        $flagArray = array();
        foreach ($patient as $dataFlag) {
            array_push($flagArray, $dataFlag->text);
        }
        $flagData = Flag::whereHas('typeId', function ($query) {
            $query->where('name', 'Patient')->orWhere('name', 'Both');
        })->get();
        $flagFinalCount = array();
        foreach ($flagData as $key => $value) {
            $flagArrayNew = new \stdClass();
            if (!in_array($value['name'], $flagArray)) {
                $flagArrayNew->total = 0;
                $flagArrayNew->color = $value['color'];
                $flagArrayNew->text = $value['name'];
                $flagArrayNew->textColor = "#FFFFFF";
                $flagArrayNew->flagId = $value['id'];
                array_push($flagFinalCount, $flagArrayNew);
            } else {
                $key = array_search($value['name'], $flagArray);
                array_push($flagFinalCount, $patient[$key]);
            }
        }
        return $flagFinalCount;
    }

    // Get Patient Total Count
    public function patientTotal()
    {
        $patient = Patient::select(DB::raw('COUNT(patients.id) as total,"Total Patients" AS text,"Type" AS type,
        "#FFFFFF" AS color, "#111111" AS textColor'))->whereNull('patients.deletedAt');
        $patient->leftJoin('patientProviders', 'patientProviders.patientId', '=', 'patients.id');
        $siteHead = Site::where(['siteHead' => Auth::id()])->first();
        if (auth()->user()->roleId == 2) {
            $client = Staff::where(['userId' => Auth::id()])->get('clientId');
            $careTeam = CareTeam::whereIn('clientId', $client)->get('id');
            $patient->whereIn('patientProviders.providerId', $careTeam);
            $patient->orWhere(function ($query) {
                $query->where('patients.createdBy', Auth::id());
            });
        } elseif (auth()->user()->roleId == 5 || auth()->user()->roleId == 7 || auth()->user()->roleId == 9) {
            if ($siteHead) {
                $careTeam = CareTeam::where(['siteId' => $siteHead->id])->get('id');
            } else {
                $careTeam = CareTeamMember::where(['contactId' => Auth::id()])->get('careTeamId');
            }
            $patient->whereIn('patientProviders.providerId', $careTeam);
        } else {
            $patient;
        }
        $patient = $patient->get();
        return $patient;
    }

    // Get Patient New Count
    public function patientNew($fromDate, $toDate)
    {
        $patient = Patient::select(DB::raw('COUNT(patients.id) as total,"New Patients" AS text,
        "#8E60FF" AS color, "#ffffff" AS textColor'))->whereNull('patients.deletedAt');
        $patient->leftJoin('patientProviders', 'patientProviders.patientId', '=', 'patients.id');
        if ($fromDate && $toDate) {
            $patient->where([['patients.createdAt', '>=', $fromDate], ['patients.createdAt', '<=', $toDate]]);
        }
        $siteHead = Site::where(['siteHead' => Auth::id()])->first();
        if (auth()->user()->roleId == 2) {
            $client = Staff::where(['userId' => Auth::id()])->get('clientId');
            $careTeam = CareTeam::whereIn('clientId', $client)->get('id');
            $patient->whereIn('patientProviders.providerId', $careTeam);
            $patient->orWhere(function ($query) {
                $query->where('patients.createdBy', Auth::id());
            });
        } elseif (auth()->user()->roleId == 5 || auth()->user()->roleId == 7 || auth()->user()->roleId == 9) {
            if ($siteHead) {
                $careTeam = CareTeam::where(['siteId' => $siteHead->id])->get('id');
            } else {
                $careTeam = CareTeamMember::where(['contactId' => Auth::id()])->get('careTeamId');
            }
            $patient->whereIn('patientProviders.providerId', $careTeam);
        } else {
            $patient;
        }
        $patient = $patient->get();
        return $patient;
    }

    // Get Patient Active Count
    public function patientActive()
    {
        $patient = Patient::select(DB::raw('COUNT(patients.id) as total,"Active Patients" AS text,
        "#267DFF" AS color,"Type" AS type,"#FFFFFF" AS textColor'))->where(['patients.isActive' => 1])->whereNull('patients.deletedAt');
        $patient->leftJoin('patientProviders', 'patientProviders.patientId', '=', 'patients.id');
        $siteHead = Site::where(['siteHead' => Auth::id()])->first();
        if (auth()->user()->roleId == 2) {
            $client = Staff::where(['userId' => Auth::id()])->get('clientId');
            $careTeam = CareTeam::whereIn('clientId', $client)->get('id');
            $patient->whereIn('patientProviders.providerId', $careTeam);
            $patient->orWhere(function ($query) {
                $query->where('patients.createdBy', Auth::id());
            });
        } elseif (auth()->user()->roleId == 5 || auth()->user()->roleId == 7 || auth()->user()->roleId == 9) {
            if ($siteHead) {
                $careTeam = CareTeam::where(['siteId' => $siteHead->id])->get('id');
            } else {
                $careTeam = CareTeamMember::where(['contactId' => Auth::id()])->get('careTeamId');
            }
            $patient->whereIn('patientProviders.providerId', $careTeam);
        } else {
            $patient;
        }
        $patient = $patient->get();
        return $patient;
    }

    // Get Patient In Active Count
    public function patientInActive()
    {
        $patient = Patient::select(DB::raw('COUNT(patients.id) as total,"Inactive Patients" AS text,
        "#0FB5C2" AS color,"Type" AS type,"#FFFFFF" AS textColor'))->where('patients.isActive', 0)->whereNull('patients.deletedAt');
        $patient->leftJoin('patientProviders', 'patientProviders.patientId', '=', 'patients.id');
        if (auth()->user()->roleId == 2) {
            $client = Staff::where(['userId' => Auth::id()])->get('clientId');
            $careTeam = CareTeam::whereIn('clientId', $client)->get('id');
            $patient->whereIn('patientProviders.providerId', $careTeam);
        } elseif (auth()->user()->roleId == 5 || auth()->user()->roleId == 7 || auth()->user()->roleId == 9) {
            $careTeam = CareTeamMember::where(['contactId' => Auth::id()])->get('careTeamId');
            $patient->whereIn('patientProviders.providerId', $careTeam);
        } else {
            $patient;
        }
        $patient = $patient->get();
        return $patient;
    }
}
