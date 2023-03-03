<?php

namespace App\Services\Api;

use Exception;

// use App\Library\PhpExcelExport;
use App\Helper;
use Carbon\Carbon;
use App\Models\Task\Task;
use App\Models\User\User;
use App\Models\Staff\Staff;
use App\Models\Client\CareTeam;
use App\Models\CPTCode\CPTCode;
use App\Models\Patient\Patient;
use App\Models\Program\Program;
use App\Models\Role\AccessRole;
use App\Models\Client\Site\Site;
use App\Models\Provider\Provider;
use App\Models\Referral\Referral;
use App\Models\Template\Template;
use Illuminate\Support\Facades\DB;
use App\Models\Client\CareTeamMember;
use App\Models\Escalation\Escalation;
use App\Models\Patient\PatientTimeLog;
use Illuminate\Support\Facades\Schema;
use App\Models\TimeApproval\TimeApproval;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Transformers\Task\TaskTransformer;
use App\Models\Communication\Communication;
use App\Models\Patient\PatientFamilyMember;
use App\Transformers\Staff\StaffTransformer;
use App\Transformers\Role\RoleListTransformer;
use App\Transformers\Patient\PatientTransformer;
use App\Transformers\Program\ProgramTransformer;
use App\Transformers\Provider\ProviderTransformer;
use App\Transformers\Referral\ReferralTransformer;
use App\Transformers\Task\TaskCategoryTransformer;
use App\Transformers\Patient\PatientVitalTransformer;
use App\Models\GeneralParameter\GeneralParameterGroup;
use App\Transformers\Escalation\EscalationTransformer;
use App\Models\ExportReportRequest\ExportReportRequest;
use App\Transformers\CPTCode\CPTCodeServiceTransformer;
use App\Transformers\Patient\PatientTimeLogTransformer;
use App\Transformers\TimeApproval\TimeApprovalTransformer;
use App\Transformers\Communication\CommunicationTransformer;
use App\Models\CPTCode\CptCodeService as CPTCodeCptCodeService;
use App\Transformers\GeneralParameter\GeneralParameterTransformer;
use App\Transformers\GeneralParameter\GeneralParameterGroupTransformer;

class ExcelGeneratorService
{
    // Timelog Excel Export
    public static function excelTimeLogExport($request, $id): void
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $writer = new Xlsx($spreadsheet);
            $post = $request->all();
            $timezone = "";
            if ($id) {
                $exportRequest = ExportReportRequest::where("udid", $id)->first()->toArray();
                if (!empty($exportRequest)) {
                    if (isset($request->timezone) && !empty($request->timezone)) {
                        $timezone = $request->timezone;
                    } else {
                        if (isset($exportRequest["customTimezone"])) {
                            $timezone = $exportRequest["customTimezone"];
                        }
                    }
                    $user = User::find($exportRequest["userId"])->toArray();
                    if (isset($user['roleId']) && $user['roleId'] == 3) {
                        $userStaff = User::with(['roles', 'staff'])->where("id", $exportRequest["userId"])->first();
                    } else {
                        $userStaff = "";
                    }
                }
            } else {
                $exportRequest = "";
                $user = "";
                $userStaff = "";
            }
            //from: 1648080000 and to: 1649808000
            if (isset($request->orderBy) && !empty($request->orderBy)) {
                if (in_array($request->orderBy, array("ASC", "DESC"))) {
                    $orderBy = $request->orderBy;
                } else {
                    $orderBy = "ASC";
                }
            } else {
                $orderBy = "ASC";
            }
            $data = PatientTimeLog::select('patientTimeLogs.*')->with('logged', 'performed', 'patient')
                ->leftJoin('staffs as s1', 's1.id', '=', 'patientTimeLogs.performedId')
                ->leftJoin('staffs as s2', 's2.id', '=', 'patientTimeLogs.loggedId')
                ->leftJoin('cptCodeActivities', 'cptCodeActivities.id', '=', 'patientTimeLogs.cptCodeId')
                ->leftJoin('cptCodes', 'cptCodes.id', '=', 'cptCodeActivities.cptCodeId')
                ->leftJoin('patients', 'patients.id', '=', 'patientTimeLogs.patientId')
                ->where(function ($query) {
                    $query->where('cptCodes.id', '!=', 6)
                        ->orWhere('patientTimeLogs.cptCodeId', 0)
                        ->orWhereNull('patientTimeLogs.cptCodeId');
                });
            // $data->leftJoin('providers', 'providers.id', '=', 'patientTimeLogs.providerId')
            //     ->where('providers.isActive', 1)
            //     ->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'patientTimeLogs.programId')
            //     ->where('programs.isActive', 1)
            //     ->whereNull('programs.deletedAt');
            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('patientTimeLogs.providerLocationId', '=', 'providerLocations.id')
            //         ->where('patientTimeLogs.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');
            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('patientTimeLogs.providerLocationId', '=', 'providerLocationStates.id')
            //         ->where('patientTimeLogs.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');
            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('patientTimeLogs.providerLocationId', '=', 'providerLocationCities.id')
            //         ->where('patientTimeLogs.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');
            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('patientTimeLogs.providerLocationId', '=', 'subLocations.id')
            //         ->where('patientTimeLogs.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            if ($request->search) {
                $value = explode(',', $request->search);
                $data->where(function ($query) use ($value) {
                    foreach ($value as $key => $search) {
                        if ($key == '0') {
                            $query->where(DB::raw("CONCAT(trim(`s1`.`firstName`), ' ', trim(`s1`.`lastName`))"), 'LIKE', "%" . $search . "%")
                                ->orWhere(DB::raw("CONCAT(trim(`s2`.`firstName`), ' ', trim(`s2`.`lastName`))"), 'LIKE', "%" . $search . "%")
                                ->orWhere(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $search . "%");
                        } else {
                            $query->orwhere(DB::raw("CONCAT(trim(`s1`.`firstName`), ' ', trim(`s1`.`lastName`))"), 'LIKE', "%" . $search . "%")
                                ->orWhere(DB::raw("CONCAT(trim(`s2`.`firstName`), ' ', trim(`s2`.`lastName`))"), 'LIKE', "%" . $search . "%")
                                ->orWhere(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $search . "%");
                        }
                    }
                });
            }
            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('patientTimeLogs.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') === 'Country') {
            //         $data->where([['patientTimeLogs.providerLocationId', $providerLocation], ['patientTimeLogs.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') === 'State') {
            //         $data->where([['patientTimeLogs.providerLocationId', $providerLocation], ['patientTimeLogs.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') === 'City') {
            //         $data->where([['patientTimeLogs.providerLocationId', $providerLocation], ['patientTimeLogs.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') === 'subLocation') {
            //         $data->where([['patientTimeLogs.providerLocationId', $providerLocation], ['patientTimeLogs.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['patientTimeLogs.programId', $program], ['patientTimeLogs.entityType', $entityType]]);
            // }
            if ($request->filter) {
                $data->where('cptCodes.name', $request->filter);
            }
            if (!empty($request->fromDate) && !empty($request->toDate)) {
                // $data->whereBetween('date', [$request->fromDate, $request->toDate]);
                $fromDate = $request->get("fromDate") . " 00:00:00";
                $toDate = $request->get("toDate") . " 23:59:59";
                $data->where([['date', '>=', $fromDate], ['date', '<=', $toDate]]);
            }

            if ($request->orderField === 'performedBy') {
                $data->orderBy('s1.firstName', $request->orderBy);
            } elseif ($request->orderField === 'patient') {
                $data->orderBy('patients.firstName', $request->orderBy);
            } elseif ($request->orderField === 'timeAmount' || $request->orderField === 'date') {
                $data->orderBy($request->orderField, $request->orderBy);
            } else {
                $data->orderBy('patientTimeLogs.createdAt', "DESC");
            }
            $data->groupBy('patientTimeLogs.id');
            $data = $data->get();
            $responseData = fractal()->collection($data)->transformWith(new PatientTimeLogTransformer())->toArray();

            $headingFrom = "A1"; // or any value
            $headingTo = "J1"; // or any value
            $sheet->setCellValue($headingFrom, 'TimeLog Report')->mergeCells($headingFrom . ':' . $headingTo);
            $sheet->getStyle($headingFrom)->getFont()->setSize(16);
            $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
            $sheet->getStyle("A2:J2")->getFont()->setBold(true);
            $sheet->getColumnDimension('A')->setWidth(80, 'pt');
            $sheet->getColumnDimension('B')->setWidth(80, 'pt');
            $sheet->getColumnDimension('C')->setWidth(80, 'pt');
            $sheet->getColumnDimension('D')->setWidth(120, 'pt');
            $sheet->getColumnDimension('E')->setWidth(120, 'pt');
            $sheet->getColumnDimension('F')->setWidth(120, 'pt');
            $sheet->getColumnDimension('G')->setWidth(120, 'pt');
            $sheet->getColumnDimension('H')->setWidth(120, 'pt');
            $sheet->getColumnDimension('I')->setWidth(120, 'pt');
            $sheet->getColumnDimension('J')->setWidth(120, 'pt');
            $sheet->setCellValue('A2', 'Care Coordinator')
                ->setCellValue('B2', 'Patient')
                ->setCellValue('C2', 'Date ')
                ->setCellValue('D2', 'Time (HH:MM:SS)')
                ->setCellValue('E2', 'Category')
                ->setCellValue('F2', 'Activity')
                ->setCellValue('G2', 'Cpt Code')
                ->setCellValue('H2', 'Amount')
                ->setCellValue('I2', 'Priority')
                ->setCellValue('J2', 'Notes');
            $k = 3;
            $dataObj = array();
            if (!empty($responseData) && count($responseData) > 0) {
                $dataObj = $responseData["data"];
                for ($i = 0, $iMax = count($dataObj); $i < $iMax; $i++) {
                    $staff_name = $dataObj[$i]["staff"];
                    $patient_name = $dataObj[$i]["patient"];
                    $cpt_code = $dataObj[$i]["cptCodeDetail"];
                    $activity = $dataObj[$i]["cptCode"];
                    $time = $dataObj[$i]["timeAmount"] / 60;
                    $timeAmount = Helper::secondTotimeConvert($dataObj[$i]["timeAmount"]);
                    $time = round($time, 2);
                    $notes = $dataObj[$i]["note"];
                    $date = date('M d, Y', $dataObj[$i]["date"]);
                    $category = "";
                    if (!empty($dataObj[$i]["category"])) {
                        $category = $dataObj[$i]["category"];
                    }
                    $billingAmount = $dataObj[$i]["billingAmount"];
                    $flagName = $dataObj[$i]["flagName"];
                    $sheet->setCellValue('A' . $k, $staff_name);
                    $sheet->setCellValue('B' . $k, $patient_name);
                    $sheet->setCellValue('C' . $k, $date);
                    $sheet->setCellValue('D' . $k, $timeAmount);
                    $sheet->setCellValue('E' . $k, $category);
                    $sheet->setCellValue('F' . $k, $activity);
                    $sheet->setCellValue('G' . $k, $cpt_code);
                    $sheet->setCellValue('H' . $k, $billingAmount);
                    $sheet->setCellValue('I' . $k, $flagName);
                    $sheet->setCellValue('J' . $k, $notes);
                    $k++;
                }
            }
            if (!empty($timezone)) {
                date_default_timezone_set($timezone);
            }
            $fileName = "timeLogReport_" . time() . ".xlsx";
            ExcelGeneratorService::writerSave($writer, $fileName);
            exit;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }

    }

    // Task Report Export
    public static function taskReportExportOld($request, $id)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $writer = new Xlsx($spreadsheet);
            $post = $request->all();
            $timezone = "";
            if ($id) {
                $exportRequest = ExportReportRequest::where("udid", $id)->first()->toArray();
                if (!empty($exportRequest)) {
                    if (isset($request->timezone) && !empty($request->timezone)) {
                        $timezone = $request->timezone;
                    } else {
                        if (isset($exportRequest["customTimezone"])) {
                            $timezone = $exportRequest["customTimezone"];
                        }
                    }
                    $user = User::find($exportRequest["userId"])->toArray();
                    if (isset($user['roleId']) && $user['roleId'] == 3) {
                        $userStaff = User::with(['roles', 'staff'])->where("id", $exportRequest["userId"])->first();
                    } else {
                        $userStaff = "";
                    }
                }
            } else {
                $exportRequest = "";
                $user = "";
                $userStaff = "";
            }
            if (!empty($timezone)) {
                date_default_timezone_set($timezone);
            }
            if (isset($post["fromDate"]) && !empty($post["fromDate"])) {
                $fromDate = date('Y-m-d', strtotime($request->get("fromDate")));
            } else {
                $fromDate = "";
            }
            if ($request->get("toDate")) {
                $toDate = date('Y-m-d', strtotime($request->get("toDate")));
            } else {
                $toDate = "";
            }
            if (!empty($fromDate) && !empty($toDate)) {
                if (isset($user['roleId']) && $user['roleId'] == 3) {
                    $resultData = Task::whereHas('assignedTo', function ($query) use ($userStaff) {
                        $query->where('assignedTo', $userStaff->staff->id);
                    })->with('taskCategory', 'taskType', 'priority', 'taskStatus', 'user');
                    if (!empty($fromDate) && !empty($toDate)) {
                        $resultData->whereBetween('dueDate', [$fromDate, $toDate]);
                    }
                    if (isset($request->search)) {
                        $resultData->where('title', 'LIKE', '%' . $request->search . '%');
                    }
                    $resultData = $resultData->orderBy('title', 'ASC')->get();
                } else {
                    $resultData = Task::with('taskCategory', 'taskType', 'priority', 'taskStatus', 'user');

                    if (!empty($fromDate) && !empty($toDate)) {
                        $resultData->whereBetween('dueDate', [$fromDate, $toDate]);
                    }
                    if (isset($request->search)) {
                        $resultData->where('title', 'LIKE', '%' . $request->search . '%');
                    }
                    $resultData = $resultData->orderBy('title', 'ASC')->get();
                }
            } else {
                if (isset($user['roleId']) && $user['roleId'] == 3) {
                    $resultData = Task::whereHas('assignedTo', function ($query) use ($userStaff) {
                        $query->where([['assignedTo', $userStaff->staff->id], ['entityType', 'staff']]);
                    });
                    if (isset($request->search)) {
                        $resultData->where('title', 'LIKE', '%' . $request->search . '%');
                    }
                    $resultData = $resultData->with('taskCategory', 'taskType', 'priority', 'taskStatus', 'user')->orderBy('title', 'ASC')->get();
                } else {
                    $resultData = Task::with('taskCategory', 'taskType', 'priority', 'taskStatus', 'user');

                    if (isset($request->search)) {
                        $resultData->where('title', 'LIKE', '%' . $request->search . '%');
                    }
                    $resultData = $resultData->orderBy('title', 'ASC')->get();
                }
            }

            $headingFrom = "A1"; // or any value
            $headingTo = "F1"; // or any value
            $sheet->setCellValue('A1', 'Task Report')->mergeCells('A1:F1');
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
            $sheet->getStyle("A2:F2")->getFont()->setBold(true);
            $sheet->getColumnDimension('A')->setWidth(80, 'pt');
            $sheet->getColumnDimension('B')->setWidth(80, 'pt');
            $sheet->getColumnDimension('C')->setWidth(80, 'pt');
            $sheet->getColumnDimension('D')->setWidth(120, 'pt');
            $sheet->getColumnDimension('E')->setWidth(80, 'pt');
            $sheet->getColumnDimension('F')->setWidth(80, 'pt');
            $sheet->setCellValue('A2', 'Task Name')
                ->setCellValue('B2', 'Task Status')
                ->setCellValue('C2', 'Priority')
                ->setCellValue('D2', 'Category')
                ->setCellValue('E2', 'Due Date')
                ->setCellValue('F2', 'Assigned By');
            $k = 3;
            if (!empty($resultData)) {
                $cat_list = "";
                foreach ($resultData as $iValue) {
                    if (!empty($iValue->taskCategory)) {
                        $taskCategory = fractal()->collection($iValue->taskCategory)->transformWith(new TaskCategoryTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
                        if (!empty($taskCategory)) {
                            $cat_list = "";
                            foreach ($taskCategory as $cat) {
                                $cat_list .= $cat["taskCategory"] . ",";
                            }
                            $cat_string = substr($cat_list, 0, -2);
                        }
                    } else {
                        $cat_string = "";
                    }
                    $dueDate = date('M d, Y', strtotime($iValue->dueDate));
                    $data = DB::select(
                        'CALL findUserByUserId("' . $iValue->user->id . '")',
                    );
                    if (isset($data[0])) {
                        $fullName = $data[0]->firstName . " " . $data[0]->lastName;
                    } else {
                        $fullName = $iValue->user->email;
                    }
                    $sheet->setCellValue('A' . $k, $iValue->title);
                    $sheet->setCellValue('B' . $k, $iValue->taskStatus->name);
                    $sheet->setCellValue('C' . $k, $iValue->priority->name);
                    $sheet->setCellValue('D' . $k, $cat_string);
                    $sheet->setCellValue('E' . $k, $dueDate);
                    $sheet->setCellValue('F' . $k, $fullName);
                    $k++;
                }
            }
            $fileName = "TaskReport_" . time() . ".xlsx";
            ExcelGeneratorService::writerSave($writer, $fileName);
            exit;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Task Report Export
    public static function taskReportExport($request, $id): void
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $writer = new Xlsx($spreadsheet);
            $post = $request->all();
            $timezone = "";
            if ($id) {
                $exportRequest = ExportReportRequest::where("udid", $id)->first()->toArray();
                if (!empty($exportRequest)) {
                    if (isset($request->timezone) && !empty($request->timezone)) {
                        $timezone = $request->timezone;
                    } else {
                        if (isset($exportRequest["customTimezone"])) {
                            $timezone = $exportRequest["customTimezone"];
                        }
                    }
                    $user = User::find($exportRequest["userId"])->toArray();
                    if (isset($user['roleId']) && $user['roleId'] == 3) {
                        $userStaff = User::with(['roles', 'staff'])->where("id", $exportRequest["userId"])->first();
                    } else {
                        $userStaff = "";
                    }
                }
            } else {
                $exportRequest = "";
                $user = "";
                $userStaff = "";
            }
            if (!empty($timezone)) {
                date_default_timezone_set($timezone);
            }
            if (isset($post["fromDate"]) && !empty($post["fromDate"])) {
                $fromDate = date('Y-m-d', strtotime($request->get("fromDate")));
            } else {
                $fromDate = "";
            }
            if ($request->get("toDate")) {
                $toDate = date('Y-m-d', strtotime($request->get("toDate")));
            } else {
                $toDate = "";
            }

            $data = Task::select('tasks.*')->with('taskCategory', 'taskType', 'priority', 'taskStatus', 'user')->leftJoin('taskCategory', 'taskCategory.taskId', '=', 'tasks.id')->whereNull('taskCategory.deletedAt')->whereNull('tasks.deletedAt')
                ->leftJoin('globalCodes as g1', 'g1.id', '=', 'taskCategory.taskCategoryId')
                ->leftJoin('globalCodes as g2', 'g2.id', '=', 'tasks.taskStatusId')
                ->leftJoin('globalCodes as g3', 'g3.id', '=', 'tasks.priorityId')
                ->leftJoin('taskAssignedTo', 'taskAssignedTo.taskId', '=', 'tasks.id')
                ->join('users', 'users.id', '=', 'tasks.createdBy')
                ->join('staffs', 'staffs.userId', '=', 'users.id')
                ->whereNull('tasks.deletedAt');

            if (isset($user['roleId']) && $user['roleId'] == 3 && isset($userStaff->staff->id)) {
                $data->where([['taskAssignedTo.assignedTo', $userStaff->staff->id], ['taskAssignedTo.entityType', 'staff']]);
            }

            $data->leftJoin('providers', 'providers.id', '=', 'tasks.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            $data->leftJoin('programs', 'programs.id', '=', 'tasks.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            $data->leftJoin('providerLocations', function ($join) {
                $join->on('tasks.providerLocationId', '=', 'providerLocations.id')->where('tasks.entityType', '=', 'Country');
            })->whereNull('providerLocations.deletedAt');

            $data->leftJoin('providerLocationStates', function ($join) {
                $join->on('tasks.providerLocationId', '=', 'providerLocationStates.id')->where('tasks.entityType', '=', 'State');
            })->whereNull('providerLocationStates.deletedAt');

            $data->leftJoin('providerLocationCities', function ($join) {
                $join->on('tasks.providerLocationId', '=', 'providerLocationCities.id')->where('tasks.entityType', '=', 'City');
            })->whereNull('providerLocationCities.deletedAt');

            $data->leftJoin('subLocations', function ($join) {
                $join->on('tasks.providerLocationId', '=', 'subLocations.id')->where('tasks.entityType', '=', 'subLocation');
            })->whereNull('subLocations.deletedAt');

            if (request()->header('providerId')) {
                $provider = Helper::providerId();
                $data->where('tasks.providerId', $provider);
            }
            if (request()->header('providerLocationId')) {
                $providerLocation = Helper::providerLocationId();
                if (request()->header('entityType') === 'Country') {
                    $data->where([['tasks.providerLocationId', $providerLocation], ['tasks.entityType', 'Country']]);
                }
                if (request()->header('entityType') === 'State') {
                    $data->where([['tasks.providerLocationId', $providerLocation], ['tasks.entityType', 'State']]);
                }
                if (request()->header('entityType') === 'City') {
                    $data->where([['tasks.providerLocationId', $providerLocation], ['tasks.entityType', 'City']]);
                }
                if (request()->header('entityType') === 'subLocation') {
                    $data->where([['tasks.providerLocationId', $providerLocation], ['tasks.entityType', 'subLocation']]);
                }
            }
            if (request()->header('programId')) {
                $program = Helper::programId();
                $entityType = Helper::entityType();
                $data->where([['tasks.programId', $program], ['tasks.entityType', $entityType]]);
            }
            if ($request->filter && $request->filter !== 'undefined') {
                if ($request->filter !== 'Total Tasks') {
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
                $input = Staff::selectRaw("group_concat(id) as StaffId")
                    ->whereIn('udid', $assignedTo)->first();
                $staffId = explode(',', $input['StaffId']);
                $data->whereIn('taskAssignedTo.assignedTo', $staffId)
                    ->where('taskAssignedTo.entityType', 'staff');
            }
            if ($request->assignedBy) {
                $assignedBy = explode(',', $request->assignedBy);
                $input = Staff::selectRaw("group_concat(userId) as StaffId")->whereIn('udid', $assignedBy)->first();
                $staffId = explode(',', $input['StaffId']);
                $data->where('tasks.createdBy', $staffId);
            }
            $fromDateStr = "";
            $toDateStr = "";
            if ((!empty($request->input('fromDate')) && !empty($request->input('toDate')))) {
                $fromDateStr = Helper::date(strtotime($request->input('fromDate')));
                $toDateStr = Helper::date(strtotime($request->input('toDate')));
                $data->whereBetween('dueDate', [$fromDateStr, $toDateStr]);
            } else {
                $now = Carbon::today();
                $fromDate = $now;
                $custommDate = date("Y-m-d", strtotime($now));
                $fromDate = $custommDate . " 00:00:00";
                $toDate = $custommDate . " 11:59:59";
                $data->whereBetween('dueDate', [$fromDate, $toDate]);
            }

            if ($request->status === 'notIn') {
                $data->where('taskStatusId', '!=', 63)->whereBetween('dueDate', [$fromDateStr, $toDateStr]);
            }
            if ($request->orderField === 'taskStatus') {
                $data->orderBy('g2.name', $request->orderBy);
            } elseif ($request->orderField === 'priority') {
                $data->orderBy('g3.name', $request->orderBy);
            } elseif ($request->orderField === 'category') {
                $data->orderBy('g1.name', $request->orderBy);
            } elseif (Schema::hasColumn('tasks', request()->orderField)) {
                $data->orderBy($request->orderField, $request->orderBy);
            } else {
                $data->orderBy('g2.priority', 'ASC');
            }
            $resultData = $data->groupBy('tasks.id')->get();
            $resultData = fractal()->collection($data)->transformWith(new TaskTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();

            $headingFrom = "A1"; // or any value
            $headingTo = "G1"; // or any value
            $sheet->setCellValue('A1', 'Task Report')->mergeCells('A1:G1');
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
            $sheet->getStyle("A2:G2")->getFont()->setBold(true);
            $sheet->getColumnDimension('A')->setWidth(80, 'pt');
            $sheet->getColumnDimension('B')->setWidth(80, 'pt');
            $sheet->getColumnDimension('C')->setWidth(80, 'pt');
            $sheet->getColumnDimension('D')->setWidth(120, 'pt');
            $sheet->getColumnDimension('E')->setWidth(80, 'pt');
            $sheet->getColumnDimension('F')->setWidth(80, 'pt');
            $sheet->getColumnDimension('G')->setWidth(80, 'pt');
            $sheet->setCellValue('A2', 'Priority')
                ->setCellValue('B2', 'Due Date')
                ->setCellValue('C2', 'Assigned To')
                ->setCellValue('D2', 'Title')
                ->setCellValue('E2', 'Status')
                ->setCellValue('F2', 'Category')
                ->setCellValue('G2', 'Assigned By');
            $k = 3;
            if (!empty($resultData)) {
                foreach ($resultData as $i => $iValue) {
                    $cat_list = "";
                    $cat_string = "";
                    // for category
                    if (count($iValue["category"]) > 0) {
                        foreach ($iValue["category"] as $cat) {
                            $cat_list .= $cat["taskCategory"] . ",";
                        }
                        $cat_string = substr($cat_list, 0, -2);
                    }

                    // for assigned list
                    $assignedTo = "";
                    if (count($iValue["assignedTo"]) > 0) {
                        foreach ($iValue["assignedTo"] as $assignedList) {
                            $assignedTo .= $assignedList["name"] . ",";
                        }
                        $assignedTo = substr($assignedTo, 0, -2);
                    }
                    $dueDate = date('M d, Y', strtotime($iValue["dueDate"]));
                    if (isset($data[0])) {
                        $assignedBy = $resultData[$i]["assignedBy"];
                    } else {
                        $assignedBy = "";
                    }
                    $sheet->setCellValue('A' . $k, $iValue["priority"]);
                    $sheet->setCellValue('B' . $k, $dueDate);
                    $sheet->setCellValue('C' . $k, $assignedTo);
                    $sheet->setCellValue('D' . $k, $iValue["title"]);
                    $sheet->setCellValue('E' . $k, $iValue["taskStatus"]);
                    $sheet->setCellValue('F' . $k, $cat_string);
                    $sheet->setCellValue('G' . $k, $assignedBy);
                    $k++;
                }
            }
            $fileName = "TaskReport_" . time() . ".xlsx";
            ExcelGeneratorService::writerSave($writer, $fileName);
            exit;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // CPT Code Excel Export
    public static function excelCptCodeExport($request): void
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $writer = new Xlsx($spreadsheet);
            $resultData = CPTCode::with('provider', 'service', 'duration');
            if (isset($request->search)) {
                $resultData->where('name', 'LIKE', '%' . $request->search . '%')->orWhere('description', 'LIKE', '%' . $request->search . '%');
            }
            $resultData = $resultData->orderBy('createdAt', 'DESC')->get();
            $headingFrom = "A1"; // or any value
            $headingTo = "D1"; // or any value
            $sheet->setCellValue('A1', 'Cpt Code Report')->mergeCells('A1:D1');
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
            $sheet->getStyle("A2:D2")->getFont()->setBold(true);
            $sheet->getColumnDimension('A')->setWidth(80, 'pt');
            $sheet->getColumnDimension('B')->setWidth(80, 'pt');
            $sheet->getColumnDimension('C')->setWidth(80, 'pt');
            $sheet->getColumnDimension('D')->setWidth(120, 'pt');
            $sheet->setCellValue('A2', 'Cpt Code')
                ->setCellValue('B2', 'Description')
                ->setCellValue('C2', 'Billing Amout')
                ->setCellValue('D2', 'Active/Inactive');
            $k = 3;
            if (!empty($resultData)) {
                foreach ($resultData as $iValue) {
                    $status = $iValue->isActive ? "True" : "False";
                    $sheet->setCellValue('A' . $k, $iValue->name);
                    $sheet->setCellValue('B' . $k, $iValue->description);
                    $sheet->setCellValue('C' . $k, $iValue->billingAmout);
                    $sheet->setCellValue('D' . $k, $status);
                    $k++;
                }
            }
            $fileName = "cptCodeReport_" . time() . ".xlsx";
            ExcelGeneratorService::writerSave($writer, $fileName);
            exit;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // General Parameter Excel Export
    public static function generalParameterExcelExport($request): void
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $writer = new Xlsx($spreadsheet);
            $data = GeneralParameterGroup::select('generalParameterGroups.*');
            // $data->leftJoin('providers', 'providers.id', '=', 'generalParameterGroups.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'generalParameterGroups.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');
            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('generalParameterGroups.providerLocationId', '=', 'providerLocations.id')->where('generalParameterGroups.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('generalParameterGroups.providerLocationId', '=', 'providerLocationStates.id')->where('generalParameterGroups.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('generalParameterGroups.providerLocationId', '=', 'providerLocationCities.id')->where('generalParameterGroups.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('generalParameterGroups.providerLocationId', '=', 'subLocations.id')->where('generalParameterGroups.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');
            // $data->where('generalParameterGroups.name', 'LIKE', '%' . $request->search . '%');
            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('generalParameterGroups.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') === 'Country') {
            //         $data->where([['generalParameterGroups.providerLocationId', $providerLocation], ['generalParameterGroups.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') === 'State') {
            //         $data->where([['generalParameterGroups.providerLocationId', $providerLocation], ['generalParameterGroups.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') === 'City') {
            //         $data->where([['generalParameterGroups.providerLocationId', $providerLocation], ['generalParameterGroups.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') === 'subLocation') {
            //         $data->where([['generalParameterGroups.providerLocationId', $providerLocation], ['generalParameterGroups.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['generalParameterGroups.programId', $program], ['generalParameterGroups.entityType', $entityType]]);
            // }
            if ($request->orderField === 'deviceType') {
                $data->join('globalCodes as deviceType', 'deviceType.id', '=', 'generalParameterGroups.deviceTypeId')->orderBy('deviceType.name', $request->orderBy);
            } elseif ($request->orderField === 'vitalFieldName') {
                $data->join('generalParameters', 'generalParameters.generalParameterGroupId', '=', 'generalParameterGroups.id')
                    ->join('vitalFields', 'vitalFields.id', '=', 'generalParameters.vitalFieldId')
                    ->orderBy('vitalFields.name', $request->orderBy);
            } elseif ($request->orderField === 'generalParameterGroup') {
                $data->orderBy('generalParameterGroups.name', $request->orderBy);
            } else {
                $data->orderBy('generalParameterGroups.name', 'ASC');
            }
            $resultData = $data->get();
            
            $headingFrom = "A1"; // or any value
            $headingTo = "E1"; // or any value
            $sheet->setCellValue('A1', 'General Parameter Report')->mergeCells('A1:E1');
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
            $sheet->getStyle("A2:E2")->getFont()->setBold(true);
            $sheet->getColumnDimension('A')->setWidth(80, 'pt');
            $sheet->getColumnDimension('B')->setWidth(80, 'pt');
            $sheet->getColumnDimension('C')->setWidth(80, 'pt');
            $sheet->getColumnDimension('D')->setWidth(120, 'pt');
            $sheet->getColumnDimension('E')->setWidth(120, 'pt');
            $sheet->setCellValue('A2', 'General Parameters Group')
                ->setCellValue('B2', 'Device Type')
                ->setCellValue('C2', 'Type')
                ->setCellValue('D2', 'High Limit')
                ->setCellValue('E2', 'Low Limit');
            $k = 3;
            if (!empty($resultData)) {
                $generalParameter = fractal()->collection($resultData)->transformWith(new GeneralParameterGroupTransformer())->toArray();
                foreach ($generalParameter["data"] as $i => $iValue) {
                    if (count($generalParameter["data"]) > 0) {
                        $type = "";
                        $deviceType = (!empty($iValue["deviceType"])) ? $iValue["deviceType"] : '';
                        $name = (!empty($iValue["name"])) ? $iValue["name"] : '';
                        if (isset($iValue["generalparameter"]["data"])) {
                            $jMax = count($iValue["generalparameter"]["data"]);
                            for ($j = 0; $j < $jMax; $j++) {
                                $g = $generalParameter["data"][$i]["generalparameter"]["data"][$j];
                                $type = $g->vitalFieldName;
                                $highLimit = $g->highLimit;
                                $lowLimit = $g->lowLimit;
                                $sheet->setCellValue('A' . $k, $name);
                                $sheet->setCellValue('B' . $k, $deviceType);
                                $sheet->setCellValue('C' . $k, $type);
                                $sheet->setCellValue('D' . $k, $highLimit);
                                $sheet->setCellValue('E' . $k, $lowLimit);
                                $k++;
                            }
                        } else {
                            $type = "";
                            $highLimit = "";
                            $lowLimit = "";
                            $sheet->setCellValue('A' . $k, $name);
                            $sheet->setCellValue('B' . $k, $deviceType);
                            $sheet->setCellValue('C' . $k, $type);
                            $sheet->setCellValue('D' . $k, $highLimit);
                            $sheet->setCellValue('E' . $k, $lowLimit);
                        }
                    } else {
                        $name = '';
                        $deviceType = '';
                        $type = "";
                        $highLimit = "";
                        $lowLimit = "";
                        $sheet->setCellValue('A' . $k, $name);
                        $sheet->setCellValue('B' . $k, $deviceType);
                        $sheet->setCellValue('C' . $k, $type);
                        $sheet->setCellValue('D' . $k, $highLimit);
                        $sheet->setCellValue('E' . $k, $lowLimit);
                        $k++;
                    }
                }
            }
            $fileName = "generalParameterReport_" . time() . ".xlsx";
            ExcelGeneratorService::writerSave($writer, $fileName);
            exit;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Template Excel Report
    public static function templateExcelExport($request): void
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $writer = new Xlsx($spreadsheet);
            $resultData = Template::all();
            if (isset($request->search)) {
                $resultData = Template::where('name', 'LIKE', '%' . $request->search . '%')->get();
            }
            $headingFrom = "A1"; // or any value
            $headingTo = "B1"; // or any value
            $sheet->setCellValue('A1', 'Template Report')->mergeCells('A1:B1');
            $sheet->getStyle('A1')->getFont()->setSize(15);
            $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
            $sheet->getStyle("A2:B2")->getFont()->setBold(true);
            $sheet->getColumnDimension('A')->setWidth(80, 'pt');
            $sheet->getColumnDimension('B')->setWidth(80, 'pt');
            $sheet->setCellValue('A2', 'Template')
                ->setCellValue('B2', 'Status');
            $k = 3;
            if (!empty($resultData)) {
                foreach ($resultData as $iValue) {
                    $status = $iValue->isActive ? "true" : "false";
                    $sheet->setCellValue('A' . $k, $iValue->name);
                    $sheet->setCellValue('B' . $k, $status);
                    $k++;
                }
            }
            $fileName = "templateReport_" . time() . ".xlsx";
            ExcelGeneratorService::writerSave($writer, $fileName);
            exit;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Inventory Excel Export
    public static function inventoryExcelExport($request, $id, $isAvailable = "", $deviceType = "", $active = "1", $search = ""): void
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $writer = new Xlsx($spreadsheet);
            if (isset($request->search)) {
                $search = $request->search;
            }
            $resultData = DB::select('CALL inventoryList("' . $isAvailable . '","' . $deviceType . '","' . $active . '","' . $search . '")');
            if (isset($request->search) && !empty($request->search)) {
                $search = $request->search;
            }
            $resultData = DB::select('CALL inventoryList("","","","' . $search . '")');
            // echo "<pre>";
            // print_r($resultData);
            // die;
            $headingFrom = "A1"; // or any value
            $headingTo = "F1"; // or any value
            $sheet->setCellValue('A1', 'Inventory Report')->mergeCells('A1:F1');
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
            $sheet->getStyle("A2:F2")->getFont()->setBold(true);
            $sheet->getColumnDimension('A')->setWidth(80, 'pt');
            $sheet->getColumnDimension('B')->setWidth(80, 'pt');
            $sheet->getColumnDimension('C')->setWidth(80, 'pt');
            $sheet->getColumnDimension('D')->setWidth(120, 'pt');
            $sheet->getColumnDimension('E')->setWidth(120, 'pt');
            $sheet->getColumnDimension('F')->setWidth(120, 'pt');
            $sheet->setCellValue('A2', 'Device Type')
                ->setCellValue('B2', 'Model Number')
                ->setCellValue('C2', 'Serial Number')
                ->setCellValue('D2', 'Mac Address')
                ->setCellValue('E2', 'Active/Inactive')
                ->setCellValue('F2', 'Availability');
            $k = 3;
            if (!empty($resultData)) {
                foreach ($resultData as $iValue) {
                    $deviceType = (!empty($iValue->model->deviceType->name)) ? $iValue->model->deviceType->name : $iValue->deviceType;
                    $modelNumber = $iValue->modelNumber ?: $iValue->model->modelName;
                    $status = $iValue->isActive ? "True" : "False";
                    if ($iValue->isAvailable) {
                        $isAvailable = "";
                    } else {
                        $isAvailable = "Assigned";
                    }
                    $sheet->setCellValue('A' . $k, $deviceType);
                    $sheet->setCellValue('B' . $k, $modelNumber);
                    $sheet->setCellValue('C' . $k, $iValue->serialNumber);
                    $sheet->setCellValue('D' . $k, $iValue->macAddress);
                    $sheet->setCellValue('E' . $k, $status);
                    $sheet->setCellValue('F' . $k, $isAvailable);
                    $k++;
                }
            }
            $fileName = "inventoryReport_" . time() . ".xlsx";
            ExcelGeneratorService::writerSave($writer, $fileName);
            exit;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Communication Excel Export
    public static function communicationExcelExport($request, $id): void
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $writer = new Xlsx($spreadsheet);
            $timezone = "";
            if ($id) {
                $exportRequest = ExportReportRequest::where("udid", $id)->first()->toArray();
                if (!empty($exportRequest)) {
                    if (isset($request->timezone) && !empty($request->timezone)) {
                        $timezone = $request->timezone;
                    } else {
                        if (isset($exportRequest["customTimezone"])) {
                            $timezone = $exportRequest["customTimezone"];
                        }
                    }
                    $user = User::find($exportRequest["userId"])->toArray();
                    if (isset($user['roleId']) && $user['roleId'] == 3) {
                        $userStaff = User::with(['roles', 'staff'])->where("id", $exportRequest["userId"])->first();
                    } else {
                        $userStaff = "";
                    }
                }
            } else {
                $exportRequest = "";
                $user = "";
                $userStaff = "";
            }
            

            $data = Communication::sms()->selectRaw('communications.*,if(patients.firstName IS NULL, staffs.firstName , patients.firstName) as fromName')
                ->join('users as u1', 'u1.id', '=', 'communications.from')
                ->join('staffs', 'staffs.userId', '=', 'u1.id', 'LEFT')
                ->join('globalCodes as g1', 'g1.id', '=', 'communications.messageCategoryId')
                ->join('globalCodes as g2', 'g2.id', '=', 'communications.messageTypeId')
                ->leftJoin('communicationCallRecords', 'communicationCallRecords.communicationId', '=', 'communications.id')
                ->leftJoin('callRecords', 'callRecords.communicationCallRecordId', '=', 'communicationCallRecords.id')
                ->leftJoin('staffs as s1', 's1.id', '=', 'callRecords.staffId')
                ->leftJoin('globalCodes as g3', 'g3.id', '=', 'communicationCallRecords.callStatusId')
                ->join('patients', 'patients.userId', '=', 'u1.id', 'LEFT');

            // $data->leftJoin('providers', 'providers.id', '=', 'communications.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'communications.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('communications.providerLocationId', '=', 'providerLocations.id')->where('communications.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('communications.providerLocationId', '=', 'providerLocationStates.id')->where('communications.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('communications.providerLocationId', '=', 'providerLocationCities.id')->where('communications.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');
            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('communications.providerLocationId', '=', 'subLocations.id')->where('communications.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');
            if (isset($user['roleId']) && $user['roleId'] == 3) {
                $data->where('communications.from', $user['id'])->orWhere('referenceId', $user['id']);
            }
            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('communications.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') === 'Country') {
            //         $data->where([['communications.providerLocationId', $providerLocation], ['communications.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') === 'State') {
            //         $data->where([['communications.providerLocationId', $providerLocation], ['communications.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') === 'City') {
            //         $data->where([['communications.providerLocationId', $providerLocation], ['communications.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') === 'subLocation') {
            //         $data->where([['communications.providerLocationId', $providerLocation], ['communications.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['communications.programId', $program], ['communications.entityType', $entityType]]);
            // }
            if ($request->filter) {
                $staff = Staff::where('udid', $request->filter)->first();
                if ($staff) {
                    $data->where(function ($query) use ($staff) {
                        $query->where('callRecords.staffId', $staff->id);
                    });
                } else {
                    $data->where(function ($query) use ($request) {
                        $query->where('g2.name', $request->filter)
                            ->orWhere('g3.name', $request->filter);
                    });
                }
            }
            if ($request->fromDate && $request->toDate) {
                $fromDateStr = Helper::date($request->input('fromDate'));
                $toDateStr = Helper::date($request->input('toDate'));
                if ($request->filter === 'Completed' || $request->filter === 'Waiting' || $request->filter === 'In Progress') {
                    $data->where([['communicationCallRecords.createdAt', '>=', $fromDateStr], ['communicationCallRecords.createdAt', '<=', $toDateStr]]);
                } elseif ($request->filter === 'App Message' || $request->filter === 'Reminder' || $request->filter === 'App Call' || $request->filter === 'Email') {
                    $data->where([['communications.createdAt', '>=', $fromDateStr], ['communicationCallRecords.createdAt', '<=', $toDateStr]]);
                } else {
                    $data->where([['callRecords.createdAt', '>=', $fromDateStr], ['callRecords.createdAt', '<=', $toDateStr]]);
                }
            }

            if ($request->search) {
                $data->where(function ($query) use ($request) {
                    $query->where('communications.updatedAt', 'LIKE', "%" . $request->search . "%");
                    $query->orWhereHas('sender', function ($que) use ($request) {
                        $que->whereHas('patient', function ($q) use ($request) {
                            $q->where(DB::raw("CONCAT(trim(`firstName`), ' ', trim(`lastName`))"), 'LIKE', "%" . $request->search . "%");
                        });
                        $que->orWhereHas('staff', function ($q) use ($request) {
                            $q->where(DB::raw("CONCAT(trim(`firstName`), ' ', trim(`lastName`))"), 'LIKE', "%" . $request->search . "%");
                        });
                    });
                    $query->orWhereHas('receiver', function ($que) use ($request) {
                        $que->whereHas('patient', function ($q) use ($request) {
                            $q->where(DB::raw("CONCAT(trim(`firstName`), ' ', trim(`lastName`))"), 'LIKE', "%" . $request->search . "%");
                        });
                        $que->orWhereHas('staff', function ($q) use ($request) {
                            $q->where(DB::raw("CONCAT(trim(`firstName`), ' ', trim(`lastName`))"), 'LIKE', "%" . $request->search . "%");
                        });
                    });
                    $query->orWhereHas('globalCode', function ($que) use ($request) {
                        $que->where('name', 'LIKE', "%" . $request->search . "%");
                    });
                });
            }

            if ($request->orderField === 'from') {
                $data->orderBy('communications.fromName', $request->orderBy);
            } elseif ($request->orderField === 'category') {
                $data->orderBy('g1.name', $request->orderBy);
            } elseif ($request->orderField === 'to') {
                $data->orderBy('communications.fromName', $request->orderBy);
            } elseif ($request->orderField === 'createdAt') {
                $data->orderBy('updatedAt', $request->orderBy);
            } else {
                $data->orderBy('communications.updatedAt', 'DESC');
            }
            $resultData = $data->groupBy('communications.id')->get();
            $resultData = fractal()->collection($resultData)->transformWith(new CommunicationTransformer())->toArray();
            if (!empty($timezone)) {
                date_default_timezone_set($timezone);
            }
            $headingFrom = "A1"; // or any value
            $headingTo = "F1"; // or any value
            $sheet->setCellValue('A1', 'Communication Report')->mergeCells('A1:F1');
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
            $sheet->getStyle("A2:F2")->getFont()->setBold(true);
            $sheet->getColumnDimension('A')->setWidth(80, 'pt');
            $sheet->getColumnDimension('B')->setWidth(80, 'pt');
            $sheet->getColumnDimension('C')->setWidth(80, 'pt');
            $sheet->getColumnDimension('D')->setWidth(120, 'pt');
            $sheet->getColumnDimension('E')->setWidth(120, 'pt');
            $sheet->getColumnDimension('F')->setWidth(120, 'pt');
            $sheet->setCellValue('A2', 'From')
                ->setCellValue('B2', 'To')
                ->setCellValue('C2', 'Type')
                ->setCellValue('D2', 'Priority')
                ->setCellValue('E2', 'Category')
                ->setCellValue('F2', 'Last Update');
            $k = 3;
            if (!empty($resultData)) {
                foreach ($resultData["data"] as $iValue) {
                    $dateSent = date("M d, Y, h:i A", $iValue["createdAt"]);
                    $sheet->setCellValue('A' . $k, $iValue["from"]);
                    $sheet->setCellValue('B' . $k, $iValue["to"]);
                    $sheet->setCellValue('C' . $k, $iValue["type"]);
                    $sheet->setCellValue('D' . $k, $iValue["priority"]);
                    $sheet->setCellValue('E' . $k, $iValue["category"]);
                    $sheet->setCellValue('F' . $k, $dateSent);
                    $k++;
                }
            }
            $fileName = "communicationReport_" . time() . ".xlsx";
            ExcelGeneratorService::writerSave($writer, $fileName);
            exit;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Care Coordinator Excel Export
    public static function careCoordinatorExcelExport($request, $id): void
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $writer = new Xlsx($spreadsheet);
            $timezone = "";
            if ($id) {
                $exportRequest = ExportReportRequest::where("udid", $id)->first()->toArray();
                if (!empty($exportRequest)) {

                    if (isset($request->timezone) && !empty($request->timezone)) {
                        $timezone = $request->timezone;
                    } else {
                        if (isset($exportRequest["customTimezone"])) {
                            $timezone = $exportRequest["customTimezone"];
                        }
                    }
                    $user = User::find($exportRequest["userId"])->toArray();
                    if (isset($user['roleId']) && $user['roleId'] == 3) {
                        $userStaff = User::with(['roles', 'staff'])->where("id", $exportRequest["userId"])->first();
                    } else {
                        $userStaff = "";
                    }
                }
            } else {
                $exportRequest = "";
                $user = "";
                $userStaff = "";
            }
            $resultData = Staff::select('staffs.*')->leftjoin('userRoles', 'userRoles.staffId', '=', 'staffs.id')
                            ->leftjoin('accessRoles', 'accessRoles.id', '=', 'userRoles.accessRoleId')
                            ->leftJoin('globalCodes as g1', 'g1.id', '=', 'staffs.specializationId')
                            ->leftJoin('users', 'users.id', '=', 'staffs.userId')
                            ->leftJoin('globalCodes as g4', 'g4.id', '=', 'staffs.designationId')
                            ->leftJoin('globalCodes as g3', 'g3.id', '=', 'staffs.typeId')
                            ->leftJoin('staffProviders', 'staffProviders.staffId', '=', 'staffs.id')
                            ->leftJoin('staffLocations', 'staffLocations.staffId', '=', 'staffs.id')
                            ->leftJoin('globalCodes as g2', 'g2.id', '=', 'staffs.networkId')
                            ->with('provider');

            // $resultData->leftJoin('providers', 'providers.id', '=', 'staffProviders.providerId')->whereNull('providers.deletedAt')->whereNull('staffProviders.deletedAt');
            // $resultData->leftJoin('programs', 'programs.id', '=', 'staffs.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $resultData->leftJoin('providerLocations', function ($join) {
            //     $join->on('staffLocations.providerLocationId', '=', 'providerLocations.id')->where('staffLocations.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $resultData->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('staffLocations.providerLocationId', '=', 'providerLocationStates.id')->where('staffLocations.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $resultData->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('staffLocations.providerLocationId', '=', 'providerLocationCities.id')->where('staffLocations.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $resultData->leftJoin('subLocations', function ($join) {
            //     $join->on('staffLocations.providerLocationId', '=', 'subLocations.id')->where('staffLocations.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (auth()->user()->roleId == 3) {
            //     $data->where('staffs.id', auth()->user()->staff->id);
            // }

            if ($request->search) {
                $resultData->where(DB::raw("CONCAT(trim(`staffs`.`firstName`), ' ', trim(`staffs`.`lastName`))"), 'LIKE', "%" . $request->search . "%")
                    ->orWhere(DB::raw("CONCAT(trim(`staffs`.`lastName`), ' ', trim(`staffs`.`firstName`))"), 'LIKE', "%" . $request->search . "%")
                    ->orWhere('g3.name', 'LIKE', "%" . $request->search . "%")
                    ->orWhere('g2.name', 'LIKE', "%" . $request->search . "%")
                    ->orWhere('g1.name', 'LIKE', "%" . $request->search . "%")
                    ->orWhere('g4.name', 'LIKE', "%" . $request->search . "%")
                    ->orWhere('staffs.organisation', 'LIKE', "%" . $request->search . "%")
                    ->orWhere('staffs.location', 'LIKE', "%" . $request->search . "%")
                    ->orWhere('users.email', 'LIKE', $request->search);
            }

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $resultData->where('staffProviders.providerId', $provider);
            // }

            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $resultData->where([['staffLocations.locationId', $providerLocation], ['staffLocations.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $resultData->where([['staffLocations.locationId', $providerLocation], ['staffLocations.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $resultData->where([['staffLocations.locationId', $providerLocation], ['staffLocations.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $resultData->where([['staffLocations.locationId', $providerLocation], ['staffLocations.entityType', 'subLocation']]);
            //     }
            // }

            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $resultData->where([['staffs.programId', $program], ['staffs.entityType', $entityType]]);
            // }

            if ($request->isActive) {
                $resultData->where('staffs.isActive', 1);
            }
            if ($request->type) {
                $resultData->where('g3.name', $request->type);
            }

            if (isset($user['roleId']) && $user['roleId'] == 2) {
                $resultData->where('staffs.id', auth()->user()->staff->id);
            }

            if ($request->filter && $request->filter != 'undefined') {
                $resultData->where(function ($query) use ($request) {
                    $query
                        ->where('g1.name', $request->filter)
                        ->orWhere('g2.name', $request->filter);
                });
            }

            if ($request->orderField == 'fullName') {
                $resultData->orderBy('staffs.firstName', $request->orderBy);
            } elseif ($request->orderField == 'createdAt') {
                $resultData->orderBy('staffs.createdAt', $request->orderBy);
            } elseif ($request->orderField == 'role') {
                $resultData->orderByRaw('group_concat(accessRoles.roles) ' . $request->orderBy)->whereNull('userRoles.deletedAt');
            } elseif ($request->orderField == 'specialization') {
                $resultData->orderBy('g1.name', $request->orderBy);
            } elseif ($request->orderField == 'organisation') {
                $resultData->orderBy('staffs.organisation', $request->orderBy);
            } elseif ($request->orderField == 'location') {
                $resultData->orderBy('staffs.location', $request->orderBy);
            } else {
                $resultData->orderBy('staffs.createdAt', "DESC");
            }

            $resultData = $resultData->groupBy('staffs.id')->get();
            $resultData = fractal()->collection($resultData)->transformWith(new StaffTransformer())->toArray();
           
            if (!empty($timezone)) {
                date_default_timezone_set($timezone);
            }
            $headingFrom = "A1"; // or any value
            $headingTo = "F1"; // or any value
            $sheet->setCellValue('A1', 'Care Coordinator Report')->mergeCells('A1:F1');
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
            $sheet->getStyle("A2:F2")->getFont()->setBold(true);
            $sheet->getColumnDimension('A')->setWidth(80, 'pt');
            $sheet->getColumnDimension('B')->setWidth(80, 'pt');
            $sheet->getColumnDimension('C')->setWidth(80, 'pt');
            $sheet->getColumnDimension('D')->setWidth(120, 'pt');
            $sheet->getColumnDimension('E')->setWidth(120, 'pt');
            $sheet->getColumnDimension('F')->setWidth(120, 'pt');
            $sheet->setCellValue('A2', 'Name')
                ->setCellValue('B2', 'Role')
                ->setCellValue('C2', 'Specialization')
                ->setCellValue('D2', 'Network')
                ->setCellValue('E2', 'Created At')
                ->setCellValue('F2', 'Status');
            $k = 3;
            if (!empty($resultData)) {
                foreach ($resultData["data"] as $iValue) {
                    if ($iValue["isActive"]) {
                        $isActive = 'Active';
                    } else {
                        $isActive = 'Inactive';
                    }

                    $roleName = '';
                    if(isset($iValue["roleCustom"]) && count($iValue["roleCustom"]) > 0){
                        foreach($iValue["roleCustom"] as $roleCustom){
                            $roleName = $roleCustom["name"].",";
                        }
                        $roleName = rtrim($roleName, ',');
                    }
                    $dateSent = date("M d, Y, h:i A", $iValue["createdAt"]);
                    $sheet->setCellValue('A' . $k, $iValue["fullName"]);
                    $sheet->setCellValue('B' . $k, $roleName);
                    $sheet->setCellValue('C' . $k, $iValue["specialization"]);
                    $sheet->setCellValue('D' . $k, $iValue["network"]);
                    $sheet->setCellValue('E' . $k, $dateSent);
                    $sheet->setCellValue('F' . $k, $isActive);
                    $k++;
                }
            }
            $fileName = "careCoordinatorReport_" . time() . ".xlsx";
            ExcelGeneratorService::writerSave($writer, $fileName);
            exit;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }


    // Specialists Excel Export
    public static function specialistsExcelExport($request, $id): void
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $writer = new Xlsx($spreadsheet);
            $timezone = "";
            if ($id) {
                $exportRequest = ExportReportRequest::where("udid", $id)->first()->toArray();
                if (!empty($exportRequest)) {
                    if (isset($request->timezone) && !empty($request->timezone)) {
                        $timezone = $request->timezone;
                    } else {
                        if (isset($exportRequest["customTimezone"])) {
                            $timezone = $exportRequest["customTimezone"];
                        }
                    }
                    $user = User::find($exportRequest["userId"])->toArray();
                    if (isset($user['roleId']) && $user['roleId'] == 3) {
                        $userStaff = User::with(['roles', 'staff'])->where("id", $exportRequest["userId"])->first();
                    } else {
                        $userStaff = "";
                    }
                }
            } else {
                $exportRequest = "";
                $user = "";
                $userStaff = "";
            }
            $resultData = Staff::select('staffs.*')->leftjoin('userRoles', 'userRoles.staffId', '=', 'staffs.id')
                ->leftjoin('accessRoles', 'accessRoles.id', '=', 'userRoles.accessRoleId')
                ->leftJoin('globalCodes as g1', 'g1.id', '=', 'staffs.specializationId')
                ->leftJoin('users', 'users.id', '=', 'staffs.userId')
                ->leftJoin('globalCodes as g4', 'g4.id', '=', 'staffs.designationId')
                ->leftJoin('globalCodes as g3', 'g3.id', '=', 'staffs.typeId')
                ->leftJoin('staffProviders', 'staffProviders.staffId', '=', 'staffs.id')
                ->leftJoin('staffLocations', 'staffLocations.staffId', '=', 'staffs.id')
                ->leftJoin('globalCodes as g2', 'g2.id', '=', 'staffs.networkId')
                ->with('provider');
            $resultData->leftJoin('providers', 'providers.id', '=', 'staffProviders.providerId')
                ->whereNull('providers.deletedAt')->whereNull('staffProviders.deletedAt');
            $resultData->leftJoin('programs', 'programs.id', '=', 'staffs.programId')
                ->where('programs.isActive', 1)->whereNull('programs.deletedAt');
            $resultData->leftJoin('providerLocations', function ($join) {
                $join->on('staffLocations.providerLocationId', '=', 'providerLocations.id')
                    ->where('staffLocations.entityType', '=', 'Country');
            })->whereNull('providerLocations.deletedAt');
            $resultData->leftJoin('providerLocationStates', function ($join) {
                $join->on('staffLocations.providerLocationId', '=', 'providerLocationStates.id')
                    ->where('staffLocations.entityType', '=', 'State');
            })->whereNull('providerLocationStates.deletedAt');
            $resultData->leftJoin('providerLocationCities', function ($join) {
                $join->on('staffLocations.providerLocationId', '=', 'providerLocationCities.id')
                    ->where('staffLocations.entityType', '=', 'City');
            })->whereNull('providerLocationCities.deletedAt');
            $resultData->leftJoin('subLocations', function ($join) {
                $join->on('staffLocations.providerLocationId', '=', 'subLocations.id')
                    ->where('staffLocations.entityType', '=', 'subLocation');
            })->whereNull('subLocations.deletedAt');
            // if (auth()->user()->roleId == 3) {
            //     $data->where('staffs.id', auth()->user()->staff->id);
            // }

            if ($request->search) {
                $resultData->where(DB::raw("CONCAT(trim(`staffs`.`firstName`), ' ', trim(`staffs`.`lastName`))"), 'LIKE', "%" . $request->search . "%")
                    ->orWhere(DB::raw("CONCAT(trim(`staffs`.`lastName`), ' ', trim(`staffs`.`firstName`))"), 'LIKE', "%" . $request->search . "%")
                    ->orWhere('g3.name', 'LIKE', "%" . $request->search . "%")
                    ->orWhere('g2.name', 'LIKE', "%" . $request->search . "%")
                    ->orWhere('g1.name', 'LIKE', "%" . $request->search . "%")
                    ->orWhere('g4.name', 'LIKE', "%" . $request->search . "%")
                    ->orWhere('staffs.organisation', 'LIKE', "%" . $request->search . "%")
                    ->orWhere('staffs.location', 'LIKE', "%" . $request->search . "%")
                    ->orWhere('users.email', 'LIKE', $request->search);
            }
            if (request()->header('providerId')) {
                $provider = Helper::providerId();
                $resultData->where('staffProviders.providerId', $provider);
            }
            if (request()->header('providerLocationId')) {
                $providerLocation = Helper::providerLocationId();
                if (request()->header('entityType') === 'Country') {
                    $resultData->where([['staffLocations.locationId', $providerLocation], ['staffLocations.entityType', 'Country']]);
                }
                if (request()->header('entityType') === 'State') {
                    $resultData->where([['staffLocations.locationId', $providerLocation], ['staffLocations.entityType', 'State']]);
                }
                if (request()->header('entityType') === 'City') {
                    $resultData->where([['staffLocations.locationId', $providerLocation], ['staffLocations.entityType', 'City']]);
                }
                if (request()->header('entityType') === 'subLocation') {
                    $resultData->where([['staffLocations.locationId', $providerLocation], ['staffLocations.entityType', 'subLocation']]);
                }
            }

            if (request()->header('programId')) {
                $program = Helper::programId();
                $entityType = Helper::entityType();
                $resultData->where([['staffs.programId', $program], ['staffs.entityType', $entityType]]);
            }

            if ($request->isActive) {
                $resultData->where('staffs.isActive', 1);
            }

            $resultData->where('g3.name', 'specialist');

            if ($request->filter && $request->filter !== 'undefined') {
                $resultData->where(function ($query) use ($request) {
                    $query
                        ->where('g1.name', $request->filter)
                        ->orWhere('g2.name', $request->filter);
                });
            }

            if ($request->orderField === 'fullName') {
                $resultData->orderBy('staffs.firstName', $request->orderBy);
            } elseif ($request->orderField === 'createdAt') {
                $resultData->orderBy('staffs.createdAt', $request->orderBy);
            } elseif ($request->orderField === 'role') {
                $resultData->orderByRaw('group_concat(accessRoles.roles) ' . $request->orderBy)->whereNull('userRoles.deletedAt');
            } elseif ($request->orderField === 'specialization') {
                $resultData->orderBy('g1.name', $request->orderBy);
            } elseif ($request->orderField === 'organisation') {
                $resultData->orderBy('staffs.organisation', $request->orderBy);
            } elseif ($request->orderField === 'location') {
                $resultData->orderBy('staffs.location', $request->orderBy);
            } else {
                $resultData->orderBy('staffs.createdAt', "DESC");
            }
            $resultData = $resultData->groupBy('staffs.id')->get();
            $resultData = fractal()->collection($resultData)->transformWith(new StaffTransformer())->toArray();
            if (!empty($timezone)) {
                date_default_timezone_set($timezone);
            }
            $headingFrom = "A1"; // or any value
            $headingTo = "E1"; // or any value
            $sheet->setCellValue('A1', 'Specialists Report')->mergeCells('A1:E1');
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
            $sheet->getStyle("A2:E2")->getFont()->setBold(true);
            $sheet->getColumnDimension('A')->setWidth(80, 'pt');
            $sheet->getColumnDimension('B')->setWidth(80, 'pt');
            $sheet->getColumnDimension('C')->setWidth(80, 'pt');
            $sheet->getColumnDimension('D')->setWidth(120, 'pt');
            $sheet->getColumnDimension('E')->setWidth(120, 'pt');
            $sheet->setCellValue('A2', 'Name')
                ->setCellValue('B2', 'Specialization')
                ->setCellValue('C2', 'Organization')
                ->setCellValue('D2', 'Location')
                ->setCellValue('E2', 'Created At');
            $k = 3;
            if (!empty($resultData)) {
                foreach ($resultData["data"] as $iValue) {
                    $dateSent = date("M d, Y, h:i A", $iValue["createdAt"]);
                    $sheet->setCellValue('A' . $k, $iValue["fullName"]);
                    $sheet->setCellValue('B' . $k, $iValue["specialization"]);
                    $sheet->setCellValue('C' . $k, $iValue["organisation"]);
                    $sheet->setCellValue('D' . $k, $iValue["location"]);
                    $sheet->setCellValue('E' . $k, $dateSent);
                    $k++;
                }
            }
            $fileName = "specialistsReport_" . time() . ".xlsx";
            ExcelGeneratorService::writerSave($writer, $fileName);
            exit;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Program Excel Export
    public static function programExcelExport($request): void
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $writer = new Xlsx($spreadsheet);
            $resultData = Program::where('isActive', 1);
            if (isset($request->search)) {
                $resultData->where('name', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('description', 'LIKE', '%' . $request->search . '%');
            }
            if (isset($request->orderBy) && !empty($request->orderBy) && isset($request->orderField) && !empty($request->orderField)) {
                $resultData = $resultData->with('type')->orderBy($request->orderField, $request->orderBy)->get();
            } else {
                $resultData = $resultData->with('type')->orderBy('name', 'ASC')->get();
            }
            $resultData = fractal()->collection($resultData)->transformWith(new ProgramTransformer())->toArray();
            $headingFrom = "A1"; // or any value
            $headingTo = "C1"; // or any value
            $sheet->setCellValue('A1', 'Program Report')->mergeCells('A1:C1');
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
            $sheet->getStyle("A2:C2")->getFont()->setBold(true);
            $sheet->getColumnDimension('A')->setWidth(80, 'pt');
            $sheet->getColumnDimension('B')->setWidth(120, 'pt');
            $sheet->getColumnDimension('C')->setWidth(80, 'pt');
            $sheet->setCellValue('A2', 'Program Name')
                ->setCellValue('B2', 'Description')
                ->setCellValue('C2', 'Active/Inactive');
            $k = 3;
            if (!empty($resultData)) {
                foreach ($resultData["data"] as $iValue) {
                    if ($iValue["isActive"] == "1") {
                        $status = "Active";
                    } else {
                        $status = "Inactive";
                    }
                    $sheet->setCellValue('A' . $k, $iValue["name"]);
                    $sheet->setCellValue('B' . $k, $iValue["description"]);
                    $sheet->setCellValue('C' . $k, $status);
                    $k++;
                }
            }
            $fileName = "programReport_" . time() . ".xlsx";
            ExcelGeneratorService::writerSave($writer, $fileName);
            exit;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Provider Excel Export
    public static function providerExcelExport($request): void
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $writer = new Xlsx($spreadsheet);
            $resultData = Provider::all();
            if (isset($request->search)) {
                $resultData = Provider::where('name', 'LIKE', '%' . $request->search . '%')->get();
            }
            $resultData = fractal()->collection($resultData)->transformWith(new ProviderTransformer())->toArray();
            $headingFrom = "A1"; // or any value
            $headingTo = "C1"; // or any value
            $sheet->setCellValue('A1', 'Provider Report')->mergeCells('A1:C1');
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
            $sheet->getStyle("A2:C2")->getFont()->setBold(true);
            $sheet->getColumnDimension('A')->setWidth(80, 'pt');
            $sheet->getColumnDimension('B')->setWidth(120, 'pt');
            $sheet->getColumnDimension('C')->setWidth(80, 'pt');
            $sheet->setCellValue('A2', 'Provider Name')
                ->setCellValue('B2', 'Provider Address')
                ->setCellValue('C2', 'Active/Inactive');
            $k = 3;
            if (!empty($resultData)) {
                foreach ($resultData["data"] as $iValue) {
                    if ($iValue["isActive"]) {
                        $status = "Active";
                    } else {
                        $status = "Inactive";
                    }
                    $sheet->setCellValue('A' . $k, $iValue["name"]);
                    $sheet->setCellValue('B' . $k, $iValue["address"]);
                    $sheet->setCellValue('C' . $k, $status);
                    $k++;
                }
            }
            $fileName = "providerReport_" . time() . ".xlsx";
            ExcelGeneratorService::writerSave($writer, $fileName);
            exit;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Roles and Permission Excel Export
    public static function roleAndPermissionExcelExport($request)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $writer = new Xlsx($spreadsheet);
            $resultData = AccessRole::orderBy('roles', 'ASC');
            $resultData->select('accessRoles.*');
            if (isset($request->search)) {
                $resultData->where('roles', 'LIKE', '%' . $request->search . '%');
            }

            $resultData->leftJoin('globalCodes as g1', 'g1.id', '=', 'accessRoles.roleTypeId')
            ->leftJoin('globalCodes as g2', 'g2.id', '=', 'accessRoles.levelId');

            if ($request->search) {
                $resultData->where([['accessRoles.roles', 'LIKE', '%' . $request->search . '%']])
                    ->orWhere([['g1.name', 'LIKE', '%' . $request->search . '%']])
                    ->orWhere([['g2.name', 'LIKE', '%' . $request->search . '%']]);
            }
            if ($request->orderField == 'roleType') {
                $resultData->orderBy('g1.name', $request->orderBy);
            } elseif ($request->orderField == 'name') {
                $resultData->orderBy('accessRoles.roles', $request->orderBy);
            } elseif ($request->orderField == 'description') {
                $resultData->orderBy('accessRoles.roleDescription', $request->orderBy);
            } else {
                $resultData->orderBy('accessRoles.roles', 'ASC');
            }
            $resultData = $resultData->get();
            $resultData = fractal()->collection($resultData)->transformWith(new RoleListTransformer())->toArray();
            $headingFrom = "A1"; // or any value
            $headingTo = "E1"; // or any value
            $sheet->setCellValue('A1', 'Role And Permission Report')->mergeCells('A1:E1');
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
            $sheet->getStyle("A2:E2")->getFont()->setBold(true);
            $sheet->getColumnDimension('A')->setWidth(80, 'pt');
            $sheet->getColumnDimension('B')->setWidth(80, 'pt');
            $sheet->getColumnDimension('C')->setWidth(120, 'pt');
            $sheet->getColumnDimension('D')->setWidth(80, 'pt');
            $sheet->getColumnDimension('E')->setWidth(80, 'pt');
            $sheet->setCellValue('A2', 'Role Name')
                ->setCellValue('B2', 'Type of Role')
                ->setCellValue('C2', 'Level')
                ->setCellValue('D2', 'Description')
                ->setCellValue('E2', 'Active/Inactive');
            $k = 3;
            if (!empty($resultData)) {
                foreach ($resultData["data"] as $iValue) {
                    if ($iValue["isActive"]) {
                        $status = "Active";
                    } else {
                        $status = "Inactive";
                    }
                    
                    $sheet->setCellValue('A' . $k, $iValue["name"]);
                    $sheet->setCellValue('B' . $k, $iValue["roleType"]);
                    $sheet->setCellValue('C' . $k, $iValue["level"]);
                    $sheet->setCellValue('D' . $k, $iValue["description"]);
                    $sheet->setCellValue('E' . $k, $status);
                    $k++;
                }
            }
            $fileName = "roleAndPermissionReport_" . time() . ".xlsx";
            ExcelGeneratorService::writerSave($writer, $fileName);
            exit;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Patient Excel Export
    public static function patientExcelExport($request, $id)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $writer = new Xlsx($spreadsheet);
            $timezone = "";
            if ($id) {
                $exportRequest = ExportReportRequest::where("udid", $id)->first()->toArray();
                if (!empty($exportRequest)) {

                    if (isset($request->timezone) && !empty($request->timezone)) {
                        $timezone = $request->timezone;
                    } else {
                        if (isset($exportRequest["customTimezone"])) {
                            $timezone = $exportRequest["customTimezone"];
                        }
                    }
                    $user = User::find($exportRequest["userId"])->toArray();
                    if (isset($user['roleId'])) {
                        $userStaff = User::with(['roles', 'staff', 'familyMember', 'patient'])->where("id", $exportRequest["userId"])->first();
                    } else {
                        $userStaff = "";
                    }
                }
            } else {
                $exportRequest = "";
                $user = "";
                $userStaff = "";
            }
            if (!empty($timezone)) {
                date_default_timezone_set($timezone);
            }
            
            $diffrence = 0;
            if ((!empty($request->input('fromDate')) && !empty($request->input('toDate')))) {
                $fromDateStr = Helper::date($request->input('fromDate'));
                $toDateStr = Helper::date($request->input('toDate'));
                $date1 = date_create($fromDateStr);
                $date2 = date_create($toDateStr);
                $diff = date_diff($date1, $date2);
                $diffrence = $diff->format("%a");
            }

            $resultData = "";
            $select = array('patients.nonCompliance', 'patients.weight', 'patients.genderId', 'patients.dob', 'patients.id', 'patients.udid', 'patients.firstName', 'patients.middleName', 'patients.lastName', 'patients.userId', 'patients.isActive', 'patients.isApp');
            $siteHead = array();
            $patient = Patient::select($select)->with('user', 'family', 'emergency', 'vitals', 'flags')
                ->leftJoin('users', 'users.id', '=', 'patients.userId')
                ->leftJoin('patientStaffs', 'patientStaffs.patientId', '=', 'patients.id')
                ->leftJoin('patientFamilyMembers', 'patientFamilyMembers.patientId', '=', 'patients.id')
                ->leftJoin('patientFlags', 'patientFlags.patientId', '=', 'patients.id')
                ->leftJoin('flags', 'flags.id', '=', 'patientFlags.flagId')
                ->leftJoin('patientProviders', 'patientProviders.patientId', '=', 'patients.id')
                // ->leftJoin('patientLocations', 'patientLocations.patientId', '=', 'patients.id')
                // ->leftJoin('patientGroups', 'patientGroups.patientId', '=', 'patients.id')
                ->whereNull('patients.deletedAt')
                ->whereNull('patientFlags.deletedAt');
            if(isset($user["id"])){
                $siteHead = Site::where(['siteHead' => $user["id"]])->first();
            }
            
            if (isset($user["roleId"]) && $user["roleId"] == 2) {
                $client = Staff::where(['userId' => $user["id"]])->get('clientId');
                $careTeam = CareTeam::whereIn('clientId', $client)->get('udid');
                $patient->whereIn('patientProviders.providerId', $careTeam);
                $patient->orWhere(function ($query) use($user) {
                    $query->where('patients.createdBy', $user["id"]);
                });
            } elseif (isset($user["roleId"]) && $user["roleId"] == 5 || isset($user["roleId"]) && $user["roleId"] == 7 || isset($user["roleId"]) && $user["roleId"] == 9) {
                if ($siteHead) {
                    $careTeam = CareTeam::where(['siteId' => $siteHead->id])->get('udid');
                } else {
                    $careTeam = CareTeamMember::where(['contactId' => $user["id"]])->get('careTeamId');
                }
                $patient->whereIn('patientProviders.providerId', $careTeam);
            } else {
                $patient;
            }
            if ($request->search) {
                if ($request->isActive) {
                    $patient->where([['patients.isActive', 1], [DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%"]])
                        ->orWhere([['patients.isActive', 1], [DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%"]])
                        ->orWhere([['patients.isActive', 1], [DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%"]])
                        ->orWhere([['patients.isActive', 1], [DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%"]]);
                } else {
                    if (isset($user["roleId"]) && $user["roleId"] == 3) {
                        $currentStaff = auth()->user()->staff->id;
                    } elseif (isset($user["roleId"]) && $user["roleId"] == 6) {
                        $currentStaff = auth()->user()->familyMember->id;
                    }

                    if (isset($user["roleId"]) && $user["roleId"] == 3 || isset($user["roleId"]) && $user["roleId"] == 6) {
                        $patient->where(function ($query) use ($currentStaff, $request) {
                            $query->where(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%");
                            $query->orWhere([['staffId', $currentStaff], [DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%"]]);
                            $query->orWhere([['staffId', $currentStaff], [DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%"]]);
                            $query->orWhere([['staffId', $currentStaff], [DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%"]]);
                        });
                    } else {
                        $patient->where(function ($query) use ($request) {
                            $query->where(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%");
                            $query->orWhere(DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%");
                            $query->orWhere(DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%");
                            $query->orWhere(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%");
                        });
                    }
                }
            }
            if ($request->isActive) {
                $patient->where('patients.isActive', 1);
            }
            if ($request->filter) {
                if ($request->filter === 'Active Patients') {
                    $patient->where('patients.isActive', 1);
                } elseif ($request->filter === 'Inactive Patients') {
                    $patient->where('patients.isActive', 0);
                } elseif ($request->filter === 'Total Patients') {
                    $patient->where('patients.isActive', '=', 1)
                        ->orWhere('patients.isActive', '=', 0);
                } elseif ($request->filter === 'New Patients') {
                    if ((!empty($request->input('fromDate')) && !empty($request->input('toDate')))) {
                        $patient->where([['patients.createdAt', '>=', $fromDateStr], ['patients.createdAt', '<=', $toDateStr]]);
                    } else {
                        $patient->where('patients.isActive', '=', 1)
                            ->orWhere('patients.isActive', '=', 0);
                    }
                } else {
                    $patient->where('flags.name', $request->filter);
                    $patient->whereNull('patients.deletedAt')->whereNull('patientFlags.deletedAt');
                }
            }
            if (!empty($fromDateStr) && !empty($toDateStr)) {

                if (
                    $request->filter === 'Escalation' || $request->filter === 'Critical' || $request->filter === 'Moderate' || $request->filter === 'WNL'
                    || $request->filter === 'Watchlist' || $request->filter === 'Trending' || $request->filter === 'Message' || $request->filter === 'Communication' || $request->filter === 'Work Status'
                ) {
                    if ($diffrence < 3) {
                        $patient->whereNull('patients.deletedAt')->whereNull('patientFlags.deletedAt');
                    } else {
                        $patient->where(function ($query) use ($fromDateStr) {
                            $query->where('patientFlags.createdAt', '>=', $fromDateStr)->orWhereNull('patientFlags.deletedAt');
                        });
                    }
                } else {
                    $patient->whereBetween('patients.createdAt', [$fromDateStr, $toDateStr]);
                }
            }
            if ($request->orderField === 'firstName' || $request->orderField === 'lastName' || $request->orderField === 'weight') {
                $patient->orderBy($request->orderField, $request->orderBy);
            } elseif ($request->orderField === 'fullName') {
                $patient->orderBy('lastName', $request->orderBy);
            } elseif ($request->orderField === 'compliance') {
                $patient->orderBy('nonCompliance', $request->orderBy);
            } elseif ($request->orderField === 'nonCompliance') {
                $patient->orderBy('nonCompliance', $request->orderBy);
            } elseif ($request->orderField === 'flagTmeStamp') {
                $patient->orderBy('patientFlags.createdAt', $request->orderBy);
            } elseif ($request->orderField === 'lastMessageSent') {
                $message = DB::select(
                    "CALL messagePriority()"
                );
                $messageData = array();
                foreach ($message as $value) {
                    array_push($messageData, $value->id);
                }
                $patient->leftJoin('communications', 'communications.referenceId', '=', 'patients.userId')
                    ->leftJoin('messages', 'messages.communicationId', '=', 'communications.id')
                    ->orderBy('messages.message', $request->orderBy)
                    ->where(function ($query) use ($messageData) {
                        $query->whereIn('messages.message', $messageData)
                            ->orWhereNull('messages.deletedAt')
                            ->orWhereNull('communications.deletedAt');
                    });
            } elseif ($request->orderField === 'bp') {
                $vitalField = DB::select(
                    "CALL vitalFieldId('" . 1 . "','" . '' . "')"
                );
                //    Print_r( $vitalField ); die('STOP');
                $array = array();
                foreach ($vitalField as $value) {
                    array_push($array, $value->id);
                }
                if ($vitalField) {
                    $patient->leftJoin('patientVitals', 'patientVitals.patientId', '=', 'patients.id')
                        ->orderBy('patientVitals.value', $request->orderBy)
                        ->where(function ($query) use ($array) {
                            $query->whereIn('patientVitals.id', $array)
                                ->where('patientVitals.deviceTypeId', '=', 99)
                                ->orWhereNull('patientVitals.deviceTypeId')
                                ->orWhereNull('patientVitals.id');
                        });
                }
            } elseif ($request->orderField === 'spo2') {
                $vitalField = DB::select(
                    "CALL vitalFieldId('" . 4 . "','" . ' ' . "')"
                );
                $array = array();
                foreach ($vitalField as $value) {
                    array_push($array, $value->id);
                }
                if ($vitalField) {
                    $patient->leftJoin('patientVitals', 'patientVitals.patientId', '=', 'patients.id')
                        ->orderBy('patientVitals.value', $request->orderBy)->where(function ($query) use ($array) {
                            $query->whereIn('patientVitals.id', $array)
                                ->where('patientVitals.deviceTypeId', '=', 100)
                                ->orWhereNull('patientVitals.deviceTypeId')
                                ->orWhereNull('patientVitals.id');
                        });
                }
            } elseif ($request->orderField === 'glucose') {
                $vitalField = DB::select(
                    "CALL vitalFieldId('" . '' . "','" . 101 . "')"
                );
                $array = array();
                foreach ($vitalField as $value) {
                    array_push($array, $value->id);
                }
                if ($vitalField) {
                    $patient->leftJoin('patientVitals', 'patientVitals.patientId', '=', 'patients.id')
                        ->orderBy('patientVitals.value', $request->orderBy)->where(function ($query) use ($array) {
                            $query->whereIn('patientVitals.id', $array)
                                ->where('patientVitals.deviceTypeId', '=', 101)
                                ->orWhereNull('patientVitals.deviceTypeId')
                                ->orWhereNull('patientVitals.id');
                        });
                }
            } elseif ($request->orderField === 'age') {
                if ($request->orderBy === 'ASC') {
                    $patient->orderBy('dob', 'DESC');
                } else {
                    $patient->orderBy('dob', 'ASC');
                }
            } elseif ($request->orderField === 'gender') {
                $patient->join('globalCodes', 'globalCodes.id', '=', 'patients.genderId')
                    ->orderBy('globalCodes.name', $request->orderBy);
            } else {
                $patient->orderBy('firstName', 'ASC');
            }

            $patient = $patient->groupBy('patients.id')->get();
            $resultData =  fractal()->collection($patient)->transformWith(new PatientTransformer(false))->toArray();
            
            $headingFrom = "A1"; // or any value
            $headingTo = "L1"; // or any value
            $sheet->setCellValue('A1', 'Patient Report')->mergeCells('A1:L1');
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
            $sheet->getStyle("A2:L2")->getFont()->setBold(true);
            $sheet->getColumnDimension('A')->setWidth(30, 'pt');
            $sheet->getColumnDimension('B')->setWidth(100, 'pt');
            $sheet->getColumnDimension('C')->setWidth(80, 'pt');
            $sheet->getColumnDimension('D')->setWidth(80, 'pt');
            $sheet->getColumnDimension('E')->setWidth(80, 'pt');
            $sheet->getColumnDimension('F')->setWidth(80, 'pt');
            $sheet->getColumnDimension('G')->setWidth(80, 'pt');
            $sheet->getColumnDimension('H')->setWidth(80, 'pt');
            $sheet->getColumnDimension('I')->setWidth(80, 'pt');
            $sheet->getColumnDimension('J')->setWidth(80, 'pt');
            $sheet->getColumnDimension('K')->setWidth(80, 'pt');
            $sheet->getColumnDimension('L')->setWidth(80, 'pt');
            $sheet->setCellValue('A2', 'Patient Status')
                ->setCellValue('B2', 'Reading Time')
                ->setCellValue('C2', 'Name')
                ->setCellValue('D2', 'BP(mmHg)')
                ->setCellValue('E2', 'BPM')
                ->setCellValue('F2', 'Spo2(%)')
                ->setCellValue('G2', 'Glucose (mg / dL)')
                ->setCellValue('H2', 'Weight (LBS)')
                ->setCellValue('I2', 'Compliant')
                ->setCellValue('J2', 'Last Message Sent')
                ->setCellValue('K2', 'Age')
                ->setCellValue('L2', 'Gender');
            $k = 3;
            $flagArr = array();
            if (!empty($resultData["data"])) {
                foreach ($resultData["data"] as $i => $iValue) {
                    $bp = "";
                    $bpm = "";
                    $Spo2 = "";
                    $glucose = "";
                    if (isset($iValue["patientVitals"]) && !empty($iValue["patientVitals"]["data"])) {
                        for ($j = 0, $jMax = count($iValue["patientVitals"]["data"]); $j < $jMax; $j++) {
                            if ($iValue["patientVitals"]['data'][$j]['vitalField'] === "Systolic") {
                                $bp .= $iValue["patientVitals"]['data'][$j]['value'] . "/";
                            } elseif ($iValue["patientVitals"]['data'][$j]['vitalField'] === "Diastolic") {
                                $bp .= $iValue["patientVitals"]['data'][$j]['value'] . "/";
                            }

                            if ($iValue["patientVitals"]['data'][$j]['vitalField'] === "BPM") {
                                $bpm = $resultData["data"][$i]["patientVitals"]['data'][$j]['value'];
                            }

                            if ($iValue["patientVitals"]['data'][$j]['vitalField'] === "SPO2") {
                                $Spo2 = $resultData["data"][$i]["patientVitals"]['data'][$j]['value'];
                            }

                            if ($iValue["patientVitals"]['data'][$j]['deviceType'] === "Glucose") {
                                $glucose = $iValue["patientVitals"]['data'][$j]['value'] . "/";
                            }
                        }
                    }
                    if (!empty($bp)) {
                        $bp = substr_replace($bp, "", -1);
                    }
                    if (!empty($glucose)) {
                        $glucose = substr_replace($glucose, "", -1);
                    }
                    if ($iValue["age"] == 0) {
                        $resultData["data"][$i]["age"] = 1;
                    }
                    if (isset($iValue["flagColor"]) && !empty($iValue["flagColor"])) {
                        $flagColor = $resultData["data"][$i]["flagColor"];
                        $flagColor = str_replace("#", "", $flagColor);
                        // $flagName = $flagArr["data"]["name"];
                        $styleArray = [
                            'font' => [
                                'bold' => true,
                            ],
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                            ],
                            'borders' => [
                                'top' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                ],
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'rotation' => 90,
                                'startColor' => [
                                    'argb' => $flagColor,
                                ],
                                'endColor' => [
                                    'argb' => $flagColor,
                                ],
                            ],
                        ];
                        $sheet->getStyle('A' . $k)->applyFromArray($styleArray);
                        // $sheet->getStyle('A' . $k)->getFill()->setFillType(Fill::FILL_GRADIENT_LINEAR)->getStartColor()->setARGB($flagColor);
                    } else {
                        $sheet->setCellValue('A' . $k, "");
                    }
                    if (!empty($iValue["flagTmeStamp"])) {
                        $readingTime = date('M d, Y', $iValue["flagTmeStamp"]);
                    } else {
                        $readingTime = "";
                    }

                    $sheet->setCellValue('B' . $k, $readingTime);
                    $sheet->setCellValue('C' . $k, $iValue["fullName"]);
                    $sheet->setCellValue('D' . $k, $bp);
                    $sheet->setCellValue('E' . $k, $bpm);
                    $sheet->setCellValue('F' . $k, $Spo2);
                    $sheet->setCellValue('G' . $k, $glucose);
                    $sheet->setCellValue('H' . $k, $iValue["weight"]);
                    $sheet->setCellValue('I' . $k, $iValue["nonCompliance"]);
                    $sheet->setCellValue('J' . $k, $iValue["lastMessageSent"]);
                    $sheet->setCellValue('K' . $k, $iValue["age"]);
                    $sheet->setCellValue('L' . $k, $iValue["genderName"]);
                    $k++;
                }
            }
            $fileName = "patient_" . time() . ".xlsx";
            ExcelGeneratorService::writerSave($writer, $fileName);
            exit;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    } 
    
    public static function patientExcelExportOld($request, $id)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $writer = new Xlsx($spreadsheet);
            $timezone = "";
            if ($id) {
                $exportRequest = ExportReportRequest::where("udid", $id)->first()->toArray();
                if (!empty($exportRequest)) {

                    if (isset($request->timezone) && !empty($request->timezone)) {
                        $timezone = $request->timezone;
                    } else {
                        if (isset($exportRequest["customTimezone"])) {
                            $timezone = $exportRequest["customTimezone"];
                        }
                    }
                    $user = User::find($exportRequest["userId"])->toArray();
                    if (isset($user['roleId'])) {
                        $userStaff = User::with(['roles', 'staff', 'familyMember', 'patient'])->where("id", $exportRequest["userId"])->first();
                    } else {
                        $userStaff = "";
                    }
                }
            } else {
                $exportRequest = "";
                $user = "";
                $userStaff = "";
            }
            if (!empty($timezone)) {
                date_default_timezone_set($timezone);
            }
            $resultData = "";
            if (isset($user['roleId']) && $user['roleId'] == 3) {
                if (Helper::haveAccessActionForExcelExport($userStaff->staff->udid, 490)) {
                    $resultData = Patient::orderBy('firstName', 'ASC');
                    if (isset($request->search)) {
                        $resultData->where(DB::raw("CONCAT(trim(`firstName`), ' ', trim(`lastName`))"), 'LIKE', "%" . $request->search . "%");
                        $resultData->orWhere(DB::raw("CONCAT(trim(`lastName`), ' ', trim(`firstName`))"), 'LIKE', "%" . $request->search . "%");
                    }
                    $resultData = $resultData->orderBy('lastName', 'ASC')->get();
                } else {
                    $resultData = Patient::whereHas('patientStaff', function ($query) use ($userStaff) {
                        $query->where('staffId', $userStaff->staff->id);
                    })->orderBy('firstName', 'ASC')->orderBy('lastName', 'ASC')->get();
                    if (isset($request->search)) {
                        $resultData = Patient::whereHas('patientStaff', function ($query) use ($request, $userStaff) {
                            $query->where('staffId', $userStaff->staff->id)->whereHas('patient', function ($q) use ($request) {
                                $q->where(DB::raw("CONCAT(trim(`firstName`), ' ', trim(`lastName`))"), 'LIKE', "%" . $request->search . "%")
                                    ->orWhere(DB::raw("CONCAT(trim(`lastName`), ' ', trim(`firstName`))"), 'LIKE', "%" . $request->search . "%");
                            });
                        });
                        $resultData = $resultData->orderBy('firstName', 'ASC')->orderBy('lastName', 'ASC')->get();
                    }
                }
                if (!empty($resultData)) {
                    $resultData = fractal()->collection($resultData)->transformWith(new PatientTransformer())->toArray();
                }
            } elseif (isset($user['roleId']) && $user['roleId'] == 6) {
                $resultData = Patient::whereHas('family', function ($query) use ($userStaff) {
                    $query->where('id', $userStaff->familyMember->id);
                })->orderBy('firstName', 'ASC')->orderBy('lastName', 'ASC')->get();

                if (isset($request->search)) {
                    $resultData = Patient::whereHas('family', function ($query) use ($request, $userStaff) {
                        $query->where('id', $userStaff->familyMember->id)->whereHas('patient', function ($q) use ($request) {
                            $q->where(DB::raw("CONCAT(trim(`firstName`), ' ', trim(`lastName`))"), 'LIKE', "%" . $request->search . "%")
                                ->orWhere(DB::raw("CONCAT(trim(`lastName`), ' ', trim(`firstName`))"), 'LIKE', "%" . $request->search . "%");
                        });
                    })->get();
                }
                if (!empty($resultData)) {
                    $resultData = fractal()->collection($resultData)->transformWith(new PatientTransformer())->toArray();
                }
            } elseif (isset($user['roleId']) && $user['roleId'] == 1) {
                if (isset($request->search)) {
                    $patient = Patient::where(DB::raw("CONCAT(trim(`firstName`), ' ', trim(`lastName`))"), 'LIKE', "%" . $request->search . "%")
                        ->orWhere(DB::raw("CONCAT(trim(`lastName`), ' ', trim(`firstName`))"), 'LIKE', "%" . $request->search . "%")
                        ->orderBy('firstName', 'ASC')->orderBy('lastName', 'ASC')->get();
                } else {
                    $patient = Patient::orderBy('firstName', 'ASC')->orderBy('lastName', 'ASC')->get();
                }
                $resultData = fractal()->collection($patient)->transformWith(new PatientTransformer())->toArray();
            } elseif (isset($user['roleId']) && $user['roleId'] == 4) {
                $patient = Patient::where('id', $userStaff->patient->id)->first();
                $resultData = fractal()->item($patient)->transformWith(new PatientTransformer())->toArray();
            }
            $headingFrom = "A1"; // or any value
            $headingTo = "L1"; // or any value
            $sheet->setCellValue('A1', 'Patient Report')->mergeCells('A1:L1');
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
            $sheet->getStyle("A2:L2")->getFont()->setBold(true);
            $sheet->getColumnDimension('A')->setWidth(30, 'pt');
            $sheet->getColumnDimension('B')->setWidth(100, 'pt');
            $sheet->getColumnDimension('C')->setWidth(80, 'pt');
            $sheet->getColumnDimension('D')->setWidth(80, 'pt');
            $sheet->getColumnDimension('E')->setWidth(80, 'pt');
            $sheet->getColumnDimension('F')->setWidth(80, 'pt');
            $sheet->getColumnDimension('G')->setWidth(80, 'pt');
            $sheet->getColumnDimension('H')->setWidth(80, 'pt');
            $sheet->getColumnDimension('I')->setWidth(80, 'pt');
            $sheet->getColumnDimension('J')->setWidth(80, 'pt');
            $sheet->getColumnDimension('K')->setWidth(80, 'pt');
            $sheet->getColumnDimension('L')->setWidth(80, 'pt');
            $sheet->setCellValue('A2', 'Patient Status')
                ->setCellValue('B2', 'Reading Time')
                ->setCellValue('C2', 'Name')
                ->setCellValue('D2', 'BP(mmHg)')
                ->setCellValue('E2', 'BPM')
                ->setCellValue('F2', 'Spo2(%)')
                ->setCellValue('G2', 'Glucose (mg / dL)')
                ->setCellValue('H2', 'Weight (LBS)')
                ->setCellValue('I2', 'Compliant')
                ->setCellValue('J2', 'Last Message Sent')
                ->setCellValue('K2', 'Age')
                ->setCellValue('L2', 'Gender');
            $k = 3;
            $flagArr = array();
            if (!empty($resultData["data"])) {
                foreach ($resultData["data"] as $i => $iValue) {
                    $bp = "";
                    $bpm = "";
                    $Spo2 = "";
                    $glucose = "";
                    if (isset($iValue["patientVitals"]) && !empty($iValue["patientVitals"]["data"])) {
                        for ($j = 0, $jMax = count($iValue["patientVitals"]["data"]); $j < $jMax; $j++) {
                            if ($iValue["patientVitals"]['data'][$j]['vitalField'] === "Systolic") {
                                $bp .= $iValue["patientVitals"]['data'][$j]['value'] . "/";
                            } elseif ($iValue["patientVitals"]['data'][$j]['vitalField'] === "Diastolic") {
                                $bp .= $iValue["patientVitals"]['data'][$j]['value'] . "/";
                            }

                            if ($iValue["patientVitals"]['data'][$j]['vitalField'] === "BPM") {
                                $bpm = $resultData["data"][$i]["patientVitals"]['data'][$j]['value'];
                            }

                            if ($iValue["patientVitals"]['data'][$j]['vitalField'] === "SPO2") {
                                $Spo2 = $resultData["data"][$i]["patientVitals"]['data'][$j]['value'];
                            }

                            if ($iValue["patientVitals"]['data'][$j]['deviceType'] === "Glucose") {
                                $glucose = $iValue["patientVitals"]['data'][$j]['value'] . "/";
                            }
                        }
                    }
                    if (!empty($bp)) {
                        $bp = substr_replace($bp, "", -1);
                    }
                    if (!empty($glucose)) {
                        $glucose = substr_replace($glucose, "", -1);
                    }
                    if ($iValue["age"] == 0) {
                        $resultData["data"][$i]["age"] = 1;
                    }
                    if (isset($iValue["flagColor"]) && !empty($iValue["flagColor"])) {
                        $flagColor = $resultData["data"][$i]["flagColor"];
                        $flagColor = str_replace("#", "", $flagColor);
                        // $flagName = $flagArr["data"]["name"];
                        $styleArray = [
                            'font' => [
                                'bold' => true,
                            ],
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                            ],
                            'borders' => [
                                'top' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                ],
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'rotation' => 90,
                                'startColor' => [
                                    'argb' => $flagColor,
                                ],
                                'endColor' => [
                                    'argb' => $flagColor,
                                ],
                            ],
                        ];
                        $sheet->getStyle('A' . $k)->applyFromArray($styleArray);
                        // $sheet->getStyle('A' . $k)->getFill()->setFillType(Fill::FILL_GRADIENT_LINEAR)->getStartColor()->setARGB($flagColor);
                    } else {
                        $sheet->setCellValue('A' . $k, "");
                    }
                    if (!empty($iValue["flagTmeStamp"])) {
                        $readingTime = date('M d, Y', $iValue["flagTmeStamp"]);
                    } else {
                        $readingTime = "";
                    }

                    $sheet->setCellValue('B' . $k, $readingTime);
                    $sheet->setCellValue('C' . $k, $iValue["fullName"]);
                    $sheet->setCellValue('D' . $k, $bp);
                    $sheet->setCellValue('E' . $k, $bpm);
                    $sheet->setCellValue('F' . $k, $Spo2);
                    $sheet->setCellValue('G' . $k, $glucose);
                    $sheet->setCellValue('H' . $k, $iValue["weight"]);
                    $sheet->setCellValue('I' . $k, $iValue["nonCompliance"]);
                    $sheet->setCellValue('J' . $k, $iValue["lastMessageSent"]);
                    $sheet->setCellValue('K' . $k, $iValue["age"]);
                    $sheet->setCellValue('L' . $k, $iValue["genderName"]);
                    $k++;
                }
            }
            $fileName = "patient_" . time() . ".xlsx";
            ExcelGeneratorService::writerSave($writer, $fileName);
            exit;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // CPT Billing ReportExcel Export
    public static function cptBillingReportExcelExport($request): void
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $writer = new Xlsx($spreadsheet);
            $data = CPTCodeCptCodeService::select('cptCodeServices.*')->with('cptCodeActivity', 'patient', 'service', 'cptCodeStatus', 'placesOfService', 'cptCodeServiceCondition')
                ->leftJoin('patients as p', 'p.id', '=', 'cptCodeServices.patientId')
                ->leftJoin('cptCodeActivities as c', 'c.id', '=', 'cptCodeServices.cptCodeActivityId')
                ->leftJoin('cptCodes as b', 'b.id', '=', 'c.cptCodeId')
                ->leftJoin('globalCodes as g1', 'g1.id', '=', 'cptCodeServices.status')
                ->leftJoin('globalCodes as g2', 'g2.id', '=', 'cptCodeServices.placeOfService');
            if ($request->fromDate && $request->toDate) {
                $fromDate = $request->fromDate . " 00:00:00";
                $toDate = $request->toDate . " 23:59:59";
                $data->whereBetween('cptCodeServices.createdAt', [$fromDate, $toDate]);
            }
            if ($request->orderField === 'patient') {
                $data->orderBy('p.lastName', $request->orderBy);
            } elseif ($request->orderField === 'billingDate') {
                $data->orderBy('cptCodeServices.createdAt', $request->orderBy);
            } elseif ($request->orderField === 'typeOfService') {
                $data->orderBy('c.name', $request->orderBy);
            } elseif ($request->orderField === 'cptCode') {
                $data->orderBy('b.name', $request->orderBy);
            } else {
                $data->orderBy('cptCodeServices.createdAt', 'ASC');
            }
            if ($request->search) {
                $data->where(function ($query) use ($request) {
                    $query->where('p.lastName', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('c.name', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('b.name', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('g1.name', 'LIKE', '%' . $request->search . '%');
                });
            }
            $data = $data->select('cptCodeServices.*')->get();
            $resultData = fractal()->collection($data)->transformWith(new CPTCodeServiceTransformer())->toArray();
            // echo "<pre>";
            // print_r($resultData);
            // die;
            $headingFrom = "A1"; // or any value
            $headingTo = "M1"; // or any value
            $sheet->setCellValue('A1', 'Global Code Report')->mergeCells('A1:M1');
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
            $sheet->getStyle("A2:M2")->getFont()->setBold(true);
            $sheet->getColumnDimension('A')->setWidth(80, 'pt');
            $sheet->getColumnDimension('B')->setWidth(80, 'pt');
            $sheet->getColumnDimension('C')->setWidth(80, 'pt');
            $sheet->getColumnDimension('D')->setWidth(80, 'pt');
            $sheet->getColumnDimension('E')->setWidth(80, 'pt');
            $sheet->getColumnDimension('F')->setWidth(80, 'pt');
            $sheet->getColumnDimension('G')->setWidth(80, 'pt');
            $sheet->getColumnDimension('H')->setWidth(80, 'pt');
            $sheet->getColumnDimension('I')->setWidth(80, 'pt');
            $sheet->getColumnDimension('J')->setWidth(80, 'pt');
            $sheet->getColumnDimension('K')->setWidth(80, 'pt');
            $sheet->getColumnDimension('L')->setWidth(80, 'pt');
            $sheet->getColumnDimension('M')->setWidth(80, 'pt');
            $sheet->setCellValue('A2', 'S.No')
                ->setCellValue('B2', 'Patient Name')
                ->setCellValue('C2', 'Place of Service')
                ->setCellValue('D2', 'ICD 10 Code')
                ->setCellValue('E2', 'Date of Service')
                ->setCellValue('F2', 'Type of Service')
                ->setCellValue('G2', 'CPT Code')
                ->setCellValue('H2', 'Unit')
                ->setCellValue('I2', 'Fee')
                ->setCellValue('J2', 'Total Fee')
                ->setCellValue('K2', 'Status')
                ->setCellValue('L2', 'Name Of Referral')
                ->setCellValue('M2', 'Referral Provider');
            $k = 3;
            if (!empty($resultData["data"])) {
                foreach ($resultData["data"] as $i => $iValue) {
                    $patientName = "";
                    if (isset($iValue["patient"])) {
                        if (isset($iValue["patient"]["fullName"])) {
                            $patientName = $resultData["data"][$i]["patient"]["fullName"];
                        }
                    }

                    $nameOfReferral = "";
                    if (isset($iValue["patient"])) {
                        if (isset($iValue["patient"]["patientReferral"]["firstName"])) {
                            $nameOfReferral = $iValue["patient"]["patientReferral"]["firstName"] . " " . $iValue["patient"]["patientReferral"]["lastName"];
                        }
                    }

                    $patientReferralProviderName = "";
                    if (isset($iValue["patient"])) {
                        if (isset($iValue["patient"]["patientReferralProviderName"])) {
                            $patientReferralProviderName = $resultData["data"][$i]["patient"]["patientReferralProviderName"];
                        }
                    }
                    if (isset($iValue["typeOfService"])) {
                        if (isset($iValue["typeOfService"]["name"])) {
                            $typeOfService = $resultData["data"][$i]["typeOfService"]["name"];
                        } else {
                            $typeOfService = "";
                        }

                        if (isset($iValue["device"]) && !empty($iValue["device"])) {
                            if (isset($iValue["device"][0]["deviceType"])) {
                                $deviceType = $resultData["data"][$i]["device"][0]["deviceType"];
                            } else {
                                $deviceType = "";
                            }
                        } elseif (isset($iValue["vital"]) && !empty($iValue["vital"])) {
                            if (isset($iValue["vital"][0]["deviceType"])) {
                                $deviceType = $resultData["data"][$i]["vital"][0]["deviceType"];
                            } else {
                                $deviceType = "";
                            }
                        } else {
                            $deviceType = "";
                        }
                        // $typeOfService = $typeOfService . " " . $deviceType;
                    } else {
                        $typeOfService = "";
                    }
                    if (isset($iValue["typeOfService"]["cptCode"]["billingAmout"])) {
                        $totalAmount = $iValue["typeOfService"]["cptCode"]["billingAmout"] * $iValue["numberOfUnit"];
                    } else {
                        $totalAmount = "";
                    }

                    if (isset($iValue["placeOfService"]["name"])) {
                        $placeOfServiceName = $resultData["data"][$i]["placeOfService"]["name"];
                    } else {
                        $placeOfServiceName = "";
                    }

                    if (isset($iValue["typeOfService"]["cptCode"]["name"])) {
                        $cptCodeName = $resultData["data"][$i]["typeOfService"]["cptCode"]["name"];
                    } else {
                        $cptCodeName = "";
                    }

                    if (isset($iValue["numberOfUnit"])) {
                        $numberOfUnit = $resultData["data"][$i]["numberOfUnit"];
                    } else {
                        $numberOfUnit = "";
                    }

                    if (isset($iValue["typeOfService"]["cptCode"]["billingAmout"])) {
                        $billingAmout = $resultData["data"][$i]["typeOfService"]["cptCode"]["billingAmout"];
                    } else {
                        $billingAmout = "";
                    }

                    if (isset($iValue["status"]["name"])) {
                        $statusName = $resultData["data"][$i]["status"]["name"];
                    } else {
                        $statusName = "";
                    }

                    if (isset($iValue["serviceId"])) {
                        $serviceId = $resultData["data"][$i]["serviceId"];
                    } else {
                        $serviceId = "";
                    }

                    if (isset($iValue["billingDate"])) {
                        $dateOfServices = date('M d, Y', $iValue["billingDate"]);
                    } else {
                        $dateOfServices = "";
                    }
                    $icdCode = "";
                    if (isset($iValue["condition"]) && count($iValue["condition"]) > 0) {
                        foreach ($iValue["condition"] as $icd) {
                            $icdCode .= $icd["code"] . ", ";
                        }
                        $icdCode = rtrim($icdCode, ', ');
                    }

                    $sheet->setCellValue('A' . $k, $serviceId);
                    $sheet->setCellValue('B' . $k, $patientName);
                    $sheet->setCellValue('C' . $k, $placeOfServiceName);
                    $sheet->setCellValue('D' . $k, $icdCode);
                    $sheet->setCellValue('E' . $k, $dateOfServices);
                    $sheet->setCellValue('F' . $k, $typeOfService);
                    $sheet->setCellValue('G' . $k, $cptCodeName);
                    $sheet->setCellValue('H' . $k, $numberOfUnit);
                    $sheet->setCellValue('I' . $k, $billingAmout);
                    $sheet->setCellValue('J' . $k, $totalAmount);
                    $sheet->setCellValue('K' . $k, $statusName);
                    $sheet->setCellValue('L' . $k, $nameOfReferral);
                    $sheet->setCellValue('M' . $k, $patientReferralProviderName);
                    $k++;
                }
            }
            $fileName = "billingReport_" . time() . ".xlsx";
            ExcelGeneratorService::writerSave($writer, $fileName);
            exit;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Global Code Excel Export
    public static function globalCodeExcelExport($request)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $writer = new Xlsx($spreadsheet);
            if (isset($request->search)) {
                $search = $request->search;
            } else {
                $search = "";
            }
            $resultData = DB::select('CALL globalCodeExcelExport("' . $search . '")');
            $headingFrom = "A1"; // or any value
            $headingTo = "D1"; // or any value
            $sheet->setCellValue('A1', 'Global Code Report')->mergeCells('A1:D1');
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
            $sheet->getStyle("A2:D2")->getFont()->setBold(true);
            $sheet->getColumnDimension('A')->setWidth(80, 'pt');
            $sheet->getColumnDimension('B')->setWidth(80, 'pt');
            $sheet->getColumnDimension('C')->setWidth(80, 'pt');
            $sheet->getColumnDimension('D')->setWidth(80, 'pt');
            $sheet->setCellValue('A2', 'Category')
                ->setCellValue('B2', 'Code Name')
                ->setCellValue('C2', 'Description')
                ->setCellValue('D2', 'Status');
            $k = 3;
            if (!empty($resultData)) {
                foreach ($resultData as $iValue) {
                    if ($iValue->isActive) {
                        $status = "Active";
                    } else {
                        $status = "Inactive";
                    }
                    if (empty($iValue->globalCodeCategoryName)) {
                        $category = "";
                    } else {
                        $category = $iValue->globalCodeCategoryName;
                    }
                    $sheet->setCellValue('A' . $k, $category);
                    $sheet->setCellValue('B' . $k, $iValue->name);
                    $sheet->setCellValue('C' . $k, $iValue->description);
                    $sheet->setCellValue('D' . $k, $status);
                    $k++;
                }
            }
            $fileName = "globalCodeReport_" . time() . ".xlsx";
            ExcelGeneratorService::writerSave($writer, $fileName);
            exit;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public static function timelogApprovalExcelExport($request, $id): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $writer = new Xlsx($spreadsheet);
        $post = $request->all();
        $timezone = "";
        if ($id) {
            $exportRequest = ExportReportRequest::where("udid", $id)->first()->toArray();
            if (!empty($exportRequest)) {
                if (isset($request->timezone) && !empty($request->timezone)) {
                    $timezone = $request->timezone;
                } else {
                    if (isset($exportRequest["customTimezone"])) {
                        $timezone = $exportRequest["customTimezone"];
                    }
                }
                $user = User::find($exportRequest["userId"])->toArray();
                if (isset($user['roleId']) && $user['roleId'] == 3) {
                    $userStaff = User::with(['roles', 'staff'])->where("id", $exportRequest["userId"])->first();
                } else {
                    $userStaff = "";
                }
            }
        } else {
            $exportRequest = "";
            $user = "";
            $userStaff = "";
        }

        if (!empty($timezone)) {
            date_default_timezone_set($timezone);
        }

        if (isset($post["fromDate"]) && !empty($post["fromDate"])) {
            $fromDate = $request->get("fromDate");
        } else {
            $fromDate = "";
        }
        if ($request->get("toDate")) {
            $toDate = $request->get("toDate");
        } else {
            $toDate = "";
        }
        $user = [];
        if (isset($post["userId"]) && !empty($post["userId"])) {
            $user = User::where("udid", $post["userId"])->first();
        }

        if (isset($post["fromDate"]) && !empty($post["fromDate"])) {
            $fromDate = $request->get("fromDate");
        } else {
            $fromDate = "";
        }
        if ($request->get("toDate")) {
            $toDate = $request->get("toDate");
        } else {
            $toDate = "";
        }
        $user = [];
        if (isset($post["userId"]) && !empty($post["userId"])) {
            $user = User::where("udid", $post["userId"])->first();
        }

        $data = TimeApproval::select('timeApprovals.*')
            ->leftJoin('patients', 'patients.id', '=', 'timeApprovals.patientId')
            ->leftJoin('globalCodes as g2', 'g2.id', '=', 'timeApprovals.typeId')
            ->leftJoin('globalCodes as g1', 'g1.id', '=', 'timeApprovals.statusId');

        if (isset($user->id)) {
            $data->where([['timeApprovals.createdBy', $user->id], ['statusId', '!=', 329]]);
        }

        if ($request->search) {
            $data->where(function ($query) use ($request) {
                $query->where(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%")
                    ->orWhere(DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%")
                    ->orWhere(DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%")
                    ->orWhere(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%")
                    ->orWhere('g1.name', 'LIKE', "%" . $request->search . "%")
                    ->orWhere('g2.name', 'LIKE', "%" . $request->search . "%");
            });
        }

        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = $fromDate . " 00:00:00";
            $toDate = $toDate . " 23:59:59";
            $data->whereBetween('timeApprovals.createdAt', [$fromDate, $toDate]);
        }

        if ($request->orderField === 'patient') {
            $data->orderBy('patients.firstName', $request->orderBy);
        } elseif ($request->orderField === 'time') {
            $data->orderBy($request->orderField, $request->orderBy);
        } elseif ($request->orderField === 'status') {
            $data->orderBy('g1.name', $request->orderBy);
        } elseif ($request->orderField === 'type') {
            $data->orderBy('g2.name', $request->orderBy);
        } elseif ($request->orderField === 'createdAt') {
            $data->orderBy('timeApprovals.createdAt', $request->orderBy);
        } else {
            $data->orderBy('timeApprovals.createdAt', 'DESC');
        }
        $data = $data->get();
        $resultData = fractal()->collection($data)->transformWith(new TimeApprovalTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();

        $headingFrom = "A1"; // or any value
        $headingTo = "E1"; // or any value
        $sheet->setCellValue('A1', 'Audit Timelog Approval Report')->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setSize(16);
        $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
        $sheet->getStyle("A2:E2")->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(80, 'pt');
        $sheet->getColumnDimension('B')->setWidth(80, 'pt');
        $sheet->getColumnDimension('C')->setWidth(80, 'pt');
        $sheet->getColumnDimension('D')->setWidth(80, 'pt');
        $sheet->getColumnDimension('E')->setWidth(80, 'pt');
        $sheet->setCellValue('A2', 'Patient')
            ->setCellValue('B2', 'Type')
            ->setCellValue('C2', 'Date')
            ->setCellValue('D2', 'Time(MM:SS)')
            ->setCellValue('E2', 'Status');
        $k = 3;
        if (!empty($resultData)) {
            foreach ($resultData as $i => $iValue) {
                $seconds = $resultData[$i]["time"];
                $s = $seconds % 60;
                $minutes = floor($seconds / 60);
                if ($minutes < 10) {
                    $minutes = "0" . $minutes;
                }
                if ($s < 10) {
                    $s = "0" . $s;
                }
                if (isset($iValue["patient"]["fullName"])) {
                    $fullname = $resultData[$i]["patient"]["fullName"];
                } else {
                    $fullname = "";
                }

                if (isset($iValue["type"])) {
                    $type = $resultData[$i]["type"];
                } else {
                    $type = "";
                }

                if (isset($iValue["status"])) {
                    $status = $resultData[$i]["status"];
                } else {
                    $status = "";
                }

                if (isset($iValue["createdAt"])) {
                    $createdAt = date("M d, Y", $iValue["createdAt"]);
                } else {
                    $createdAt = "";
                }
                $sheet->setCellValue('A' . $k, $fullname);
                $sheet->setCellValue('B' . $k, $type);
                $sheet->setCellValue('C' . $k, $createdAt);
                $sheet->setCellValue('D' . $k, $minutes . ":" . $s);
                $sheet->setCellValue('E' . $k, $status);
                $k++;
            }
            $sheet->setCellValue('A' . $k, $fullname);
            $sheet->setCellValue('B' . $k, $type);
            $sheet->setCellValue('C' . $k, $createdAt);
            $sheet->setCellValue('D' . $k, $minutes . ":" . $s);
            $sheet->setCellValue('E' . $k, $status);
            $k++;
        }
        $fileName = "auditTimeApprovalReport_" . time() . ".xlsx";
        ExcelGeneratorService::writerSave($writer, $fileName);
        exit;

    }

    public static function referralExcelExport($request, $id): void
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $writer = new Xlsx($spreadsheet);

            $timezone = "";
            if ($id) {
                $exportRequest = ExportReportRequest::where("udid", $id)->first()->toArray();
                if (!empty($exportRequest)) {
                    if (isset($request->timezone) && !empty($request->timezone)) {
                        $timezone = $request->timezone;
                    } else {
                        if (isset($exportRequest["customTimezone"])) {
                            $timezone = $exportRequest["customTimezone"];
                        }
                    }
                    $user = User::find($exportRequest["userId"])->toArray();
                    if (isset($user['roleId']) && $user['roleId'] == 3) {
                        $userStaff = User::with(['roles', 'staff'])->where("id", $exportRequest["userId"])->first();
                    } else {
                        $userStaff = "";
                    }
                }
            } else {
                $exportRequest = "";
                $user = "";
                $userStaff = "";
            }

            if (!empty($timezone)) {
                date_default_timezone_set($timezone);
            }

            if ((!empty($request->input('fromDate')) && !empty($request->input('toDate')))) {
                $fromDateStr = Helper::date($request->input('fromDate'));
                $toDateStr = Helper::date($request->input('toDate'));
            }
            $data = Referral::select("referrals.*", "patients.udid as patientUdid", "patients.firstName as patientFirstName", "patients.middleName as patientMiddleName", "patients.lastName as patientLastName")
                ->where(function ($query) use ($request) {
                    $query->where(DB::raw("CONCAT(trim(`referrals`.`firstName`), ' ', trim(`referrals`.`lastName`))"), 'LIKE', "%" . $request->search . "%");
                    $query->orwhere(DB::raw("CONCAT(trim(`referrals`.`lastName`), ' ', trim(`referrals`.`firstName`))"), 'LIKE', "%" . $request->search . "%");
                    $query->orwhere(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%");
                    $query->orwhere(DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%");
                });
            $data->join('patientReferrals', 'patientReferrals.referralId', '=', 'referrals.id')
                ->join('patients', 'patients.id', '=', 'patientReferrals.patientId')->whereNull('patients.deletedAt')->whereNull('patientReferrals.deletedAt');

            if ($request->filter) {
                $referal = Referral::where('udid', $request->filter)->first();
                if ($referal) {
                    $data->where('patientReferrals.referralId', $referal->id);
                }
                if ((!empty($request->input('fromDate')) && !empty($request->input('toDate')))) {
                    $data->where([['patientReferrals.createdAt', '>=', $fromDateStr], ['patientReferrals.createdAt', '<=', $toDateStr]]);
                }
            }
            if (!empty($fromDateStr) && !empty($toDateStr)) {
                $data->where([['patientReferrals.createdAt', '>=', $fromDateStr], ['patientReferrals.createdAt', '<=', $toDateStr]]);
            }
            if ($request->referral) {
                $data = Referral::where(DB::raw("CONCAT(trim(`firstName`), ' ', trim(`lastName`))"), 'LIKE', "%" . $request->search . "%")
                    ->orwhere(DB::raw("CONCAT(trim(`lastName`), ' ', trim(`firstName`))"), 'LIKE', "%" . $request->search . "%");
            }
            if ($request->orderField === 'name') {
                $data->orderBy(DB::raw("CONCAT(trim(`referrals`.`firstName`), ' ', trim(`referrals`.`lastName`))"), $request->orderBy);
            } elseif ($request->orderField === 'designation') {
                $data->Leftjoin('globalCodes', 'globalCodes.id', '=', 'referrals.designationId')
                    ->orderBy('globalCodes.name', $request->orderBy);
            } elseif ($request->orderField === 'email') {
                $data->orderBy('email', $request->orderBy);
            } elseif ($request->orderField === 'patientName') {
                $data->orderBy('patients.firstName', $request->orderBy);
            } else {
                $data->orderBy('firstName', 'ASC');
            }
            $data = $data->get();
            $resultData = fractal()->collection($data)->transformWith(new ReferralTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
            // echo "<pre>";
            // print_r($resultData);
            // die;
            $headingFrom = "A1"; // or any value
            $headingTo = "D1"; // or any value
            $sheet->setCellValue('A1', 'Referrals Report')->mergeCells('A1:D1');
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
            $sheet->getStyle("A2:D2")->getFont()->setBold(true);
            $sheet->getColumnDimension('A')->setWidth(80, 'pt');
            $sheet->getColumnDimension('B')->setWidth(80, 'pt');
            $sheet->getColumnDimension('C')->setWidth(80, 'pt');
            $sheet->getColumnDimension('D')->setWidth(80, 'pt');
            $sheet->setCellValue('A2', 'Name')
                ->setCellValue('B2', 'Phone Number')
                ->setCellValue('C2', 'Email')
                ->setCellValue('D2', 'Patient Name');
            $k = 3;
            if (!empty($resultData)) {
                foreach ($resultData as $iValue) {
                    $sheet->setCellValue('A' . $k, $iValue["name"]);
                    $sheet->setCellValue('B' . $k, $iValue["phoneNumber"]);
                    $sheet->setCellValue('C' . $k, $iValue["email"]);
                    $sheet->setCellValue('D' . $k, $iValue["patientName"]);
                    $k++;
                }
            }
            $fileName = "referralReport_" . time() . ".xlsx";
            ExcelGeneratorService::writerSave($writer, $fileName);
            exit;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public static function escalationExcelExport($request, $id): void
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $writer = new Xlsx($spreadsheet);
            $timezone = "";
            if ($id) {
                $exportRequest = ExportReportRequest::where("udid", $id)->first()->toArray();
                if (!empty($exportRequest)) {
                    if (isset($request->timezone) && !empty($request->timezone)) {
                        $timezone = $request->timezone;
                    } else {
                        if (isset($exportRequest["customTimezone"])) {
                            $timezone = $exportRequest["customTimezone"];
                        }
                    }
                    $user = User::find($exportRequest["userId"])->toArray();
                    if (isset($user['roleId']) && $user['roleId'] == 3) {
                        $userStaff = User::with(['roles', 'staff'])->where("id", $exportRequest["userId"])->first();
                    } else {
                        $userStaff = "";
                    }
                }
            } else {
                $exportRequest = "";
                $user = "";
                $userStaff = "";
            }

            if (!empty($timezone)) {
                date_default_timezone_set($timezone);
            }

            if ((!empty($request->input('fromDate')) && !empty($request->input('toDate')))) {
                $fromDateStr = Helper::date($request->input('fromDate'));
                $toDateStr = Helper::date($request->input('toDate'));
            }
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();

            $data = Escalation::select('escalations.*')->with('assign', 'patient', 'detail', 'escalationAction', 'escalationClose')
                ->leftJoin('patients', 'patients.id', '=', 'escalations.referenceId')
                ->leftJoin('globalCodes', 'globalCodes.id', '=', 'escalations.typeId')
                ->leftJoin('globalCodes as g1', 'g1.id', '=', 'escalations.statusId')
                ->leftJoin('escalationAssignTo', 'escalationAssignTo.escalationId', '=', 'escalations.escalationId')
                ->leftJoin('staffs', 'staffs.id', '=', 'escalationAssignTo.referenceId')
                ->leftJoin('providerLocations', 'providerLocations.id', '=', 'escalations.providerLocationId')
                ->leftJoin('escalationDetails', 'escalationDetails.escalationId', '=', 'escalations.escalationId')
                ->leftJoin('escalationCloses', 'escalationCloses.escalationId', '=', 'escalations.escalationId');
            if ($provider) {
                $data->where('escalations.providerId', $provider);
            }
            if ($providerLocation) {
                $data->where(function ($query) use ($providerLocation) {
                    $query->where('escalations.providerLocationId', $providerLocation)->orWhere('providerLocations.parent', $providerLocation);
                });
            }

            if ($request->referenceId) {
                $patient = Helper::tableName('App\Models\Patient\Patient', $request->referenceId);
                $data->where('escalations.referenceId', $patient);
            }
            if ($request->search) {
                $data->where(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%")
                    ->orWhere(DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%")
                    ->orWhere(DB::raw("CONCAT(trim(`staffs`.`firstName`), ' ', trim(`staffs`.`middleName`), ' ', trim(`staffs`.`lastName`))"), 'LIKE', "%" . $request->search . "%")
                    ->orWhere(DB::raw("CONCAT(trim(`staffs`.`lastName`), ' ', trim(`staffs`.`middleName`), ' ', trim(`staffs`.`firstName`))"), 'LIKE', "%" . $request->search . "%")
                    ->orWhere('globalCodes.name', 'LIKE', "%" . $request->search . "%")
                    ->orWhere('g1.name', 'LIKE', "%" . $request->search . "%");
            }
            if ($request->orderField === 'patientName') {
                $data->orderBy('patients.firstName', $request->orderBy);
            } elseif ($request->orderField === 'assignedTo') {
                $data->orderBy('staffs.firstName', $request->orderBy);
            } elseif ($request->orderField === 'escalationType') {
                $data->orderBy('globalCodes.name', $request->orderBy);
            } elseif ($request->orderField === 'status') {
                $data->orderBy('g1.name', $request->orderBy);
            } else {
                $data->orderBy('globalCodes.name', 'DESC');
            }
            if ($request->fromDate && $request->toDate) {
                $fromDate = Helper::date(strtotime($request->fromDate));
                $toDate = Helper::date(strtotime($request->toDate));
                $data->where('escalations.createdAt', '>=', $fromDate)->where('escalations.createdAt', '<=', $toDate)->where('escalations.entityType', $request->entityType);
            }
            if ($request->status) {
                $data->where('escalationCloses.status', $request->status);
            }
            $data = $data->groupBy('escalations.escalationId')->get();
            $resultData = fractal()->collection($data)->transformWith(new EscalationTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();

            $headingFrom = "A1"; // or any value
            $headingTo = "I1"; // or any value
            $sheet->setCellValue('A1', 'Escalation Report')->mergeCells('A1:I1');
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
            $sheet->getStyle("A2:I2")->getFont()->setBold(true);
            $sheet->getColumnDimension('A')->setWidth(80, 'pt');
            $sheet->getColumnDimension('B')->setWidth(80, 'pt');
            $sheet->getColumnDimension('C')->setWidth(80, 'pt');
            $sheet->getColumnDimension('E')->setWidth(80, 'pt');
            $sheet->getColumnDimension('F')->setWidth(80, 'pt');
            $sheet->getColumnDimension('G')->setWidth(80, 'pt');
            $sheet->getColumnDimension('H')->setWidth(80, 'pt');
            $sheet->getColumnDimension('I')->setWidth(80, 'pt');
            $sheet->setCellValue('A2', 'Patient')
                ->setCellValue('B2', 'Type')
                ->setCellValue('C2', 'Date')
                ->setCellValue('D2', 'Created By')
                ->setCellValue('E2', 'Send To')
                ->setCellValue('F2', 'Reason')
                ->setCellValue('G2', 'Status')
                ->setCellValue('H2', 'Action Taken')
                ->setCellValue('I2', 'Loop Closed');
            $k = 3;
            if (!empty($resultData)) {
                foreach ($resultData as $i => $iValue) {
                    $assignToName = "";
                    if (isset($iValue["escalationAssignTo"]) && !empty($iValue["escalationAssignTo"])) {
                        foreach ($iValue["escalationAssignTo"] as $s) {
                            if (isset($s["staff"]["firstName"])) {
                                $assignToName .= $s["staff"]["firstName"] . " " . $s["staff"]["lastName"] . ",";
                            }
                        }
                        $assignToName = rtrim($assignToName, ',');
                    }

                    $escalationAction = "";
                    if (isset($iValue["escalationAction"]) && !empty($iValue["escalationAction"])) {
                        foreach ($iValue["escalationAction"] as $a) {
                            if (isset($a["action"])) {
                                $escalationAction .= $a["action"] . ",";
                            }
                        }
                        $escalationAction = rtrim($escalationAction, ',');
                    }

                    if (isset($iValue["escalationClose"]["status"])) {
                        if ($iValue["escalationClose"]["status"] !== "Closed" && $iValue["status"] === "Responded") {
                            $escalationClose = "Open";
                        } else {
                            $escalationClose = $resultData[$i]["escalationClose"]["status"];
                        }
                    } else {
                        $escalationClose = "";
                    }


                    $createdAt = date("M d, Y", $iValue["createdAt"]);

                    if (isset($iValue["patient"]["fullName"])) {
                        $patientFullName = $resultData[$i]["patient"]["fullName"];
                    } else {
                        $patientFullName = "";
                    }

                    if (isset($iValue["assignedBy"]["fullName"])) {
                        $assignedFullName = $resultData[$i]["assignedBy"]["fullName"];
                    } else {
                        $assignedFullName = "";
                    }

                    $sheet->setCellValue('A' . $k, $patientFullName);
                    $sheet->setCellValue('B' . $k, $iValue["type"]);
                    $sheet->setCellValue('C' . $k, $createdAt);
                    $sheet->setCellValue('D' . $k, $assignedFullName);
                    $sheet->setCellValue('E' . $k, $assignToName);
                    $sheet->setCellValue('F' . $k, $iValue["reason"]);
                    $sheet->setCellValue('G' . $k, $iValue["status"]);
                    $sheet->setCellValue('H' . $k, $escalationAction);
                    $sheet->setCellValue('I' . $k, $escalationClose);
                    $k++;
                }
            }
            $fileName = "escalationReport_" . time() . ".xlsx";
            ExcelGeneratorService::writerSave($writer, $fileName);
            exit;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public static function escalationAuditExcelExport($request, $id)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $writer = new Xlsx($spreadsheet);

            $timezone = "";
            if ($id) {
                $exportRequest = ExportReportRequest::where("udid", $id)->first()->toArray();
                if (!empty($exportRequest)) {
                    if (isset($request->timezone) && !empty($request->timezone)) {
                        $timezone = $request->timezone;
                    } else {
                        if (isset($exportRequest["customTimezone"])) {
                            $timezone = $exportRequest["customTimezone"];
                        }
                    }
                    $user = User::find($exportRequest["userId"])->toArray();
                    if (isset($user['roleId']) && $user['roleId'] == 3) {
                        $userStaff = User::with(['roles', 'staff'])->where("id", $exportRequest["userId"])->first();
                    } else {
                        $userStaff = "";
                    }
                }
            } else {
                $exportRequest = "";
                $user = "";
                $userStaff = "";
            }

            if (!empty($timezone)) {
                date_default_timezone_set($timezone);
            }

            $data = Escalation::select('escalations.*')->with('assign', 'patient', 'detail', 'escalationAction', 'escalationClose', 'escalationAuditDescription')
                ->leftJoin('patients', 'patients.id', '=', 'escalations.referenceId')
                ->leftJoin('globalCodes', 'globalCodes.id', '=', 'escalations.typeId')
                ->leftJoin('globalCodes as g1', 'g1.id', '=', 'escalations.statusId')
                ->leftJoin('escalationAssignTo', 'escalationAssignTo.escalationId', '=', 'escalations.escalationId')
                ->leftJoin('staffs', 'staffs.id', '=', 'escalationAssignTo.referenceId')
                ->leftJoin('escalationDetails', 'escalationDetails.escalationId', '=', 'escalations.escalationId')
                ->leftJoin('escalationCloses', 'escalationCloses.escalationId', '=', 'escalations.escalationId')
                ->leftJoin('escalationActions', 'escalationActions.escalationId', '=', 'escalations.escalationId')
                ->leftJoin('globalCodes as g2', 'g2.id', '=', 'escalationActions.actionId');

            // $data->leftJoin('providers', 'providers.id', '=', 'escalations.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'escalations.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('escalations.providerLocationId', '=', 'providerLocations.id')->where('escalations.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('escalations.providerLocationId', '=', 'providerLocationStates.id')->where('escalations.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('escalations.providerLocationId', '=', 'providerLocationCities.id')->where('escalations.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('escalations.providerLocationId', '=', 'subLocations.id')->where('escalations.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('escalations.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['escalations.providerLocationId', $providerLocation], ['escalations.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['escalations.providerLocationId', $providerLocation], ['escalations.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['escalations.providerLocationId', $providerLocation], ['escalations.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['escalations.providerLocationId', $providerLocation], ['escalations.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['escalations.programId', $program], ['escalations.entityType', $entityType]]);
            // }

            if ($request->referenceId) {
                $patient = Helper::tableName('App\Models\Patient\Patient', $request->referenceId);
                $data->where('escalations.referenceId', $patient);
            }

            if ($request->search) {
                $data->where(function ($query) use ($request) {
                    $query->where(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%")->where('escalationCloses.status', 'Close')
                        ->orWhere(DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%")->where('escalationCloses.status', 'Close')
                        ->orWhere(DB::raw("CONCAT(trim(`staffs`.`firstName`), ' ', trim(`staffs`.`middleName`), ' ', trim(`staffs`.`lastName`))"), 'LIKE', "%" . $request->search . "%")->where('escalationCloses.status', 'Close')
                        ->orWhere(DB::raw("CONCAT(trim(`staffs`.`lastName`), ' ', trim(`staffs`.`middleName`), ' ', trim(`staffs`.`firstName`))"), 'LIKE', "%" . $request->search . "%")->where('escalationCloses.status', 'Close')
                        ->orWhere('globalCodes.name', 'LIKE', "%" . $request->search . "%")->where('escalationCloses.status', 'Close')
                        ->orWhere('g1.name', 'LIKE', "%" . $request->search . "%")->where('escalationCloses.status', 'Close');
                });
            }

            if ($request->orderField === 'patientName') {
                $data->orderBy('patients.firstName', $request->orderBy);
            } elseif ($request->orderField === 'assignedTo') {
                $data->orderBy('staffs.firstName', $request->orderBy);
            } elseif ($request->orderField === 'escalationType') {
                $data->orderBy('globalCodes.name', $request->orderBy);
            } elseif ($request->orderField === 'status') {
                $data->orderBy('g1.name', $request->orderBy);
            } elseif ($request->orderField === 'takenAction') {
                $data->orderBy('g2.name', $request->orderBy);
            } elseif ($request->orderField === 'createdAt') {
                $data->orderBy('escalations.createdAt', $request->orderBy);
            } else {
                $data->orderBy('globalCodes.name', 'DESC');
            }

            if ($request->fromDate && $request->toDate) {
                $fromDate = Helper::date($request->fromDate);
                $toDate = Helper::date($request->toDate);
                $data->where('escalations.createdAt', '>=', $fromDate)->where('escalations.createdAt', '<=', $toDate);
            }
            $data->where('escalationCloses.status', 'Close');
            $data = $data->groupBy('escalations.escalationId')->get();
            $resultData = fractal()->collection($data)->transformWith(new EscalationTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();

            $headingFrom = "A1"; // or any value
            $headingTo = "J1"; // or any value
            $sheet->setCellValue('A1', 'Audit Escalation Report')->mergeCells('A1:J1');
            $sheet->getStyle('A1')->getFont()->setSize(16);
            $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
            $sheet->getStyle("A2:J2")->getFont()->setBold(true);
            $sheet->getColumnDimension('A')->setWidth(80, 'pt');
            $sheet->getColumnDimension('B')->setWidth(80, 'pt');
            $sheet->getColumnDimension('C')->setWidth(80, 'pt');
            $sheet->getColumnDimension('E')->setWidth(80, 'pt');
            $sheet->getColumnDimension('F')->setWidth(80, 'pt');
            $sheet->getColumnDimension('G')->setWidth(80, 'pt');
            $sheet->getColumnDimension('H')->setWidth(80, 'pt');
            $sheet->getColumnDimension('I')->setWidth(80, 'pt');
            $sheet->getColumnDimension('J')->setWidth(80, 'pt');
            $sheet->setCellValue('A2', 'Patient')
                ->setCellValue('B2', 'Type')
                ->setCellValue('C2', 'Date')
                ->setCellValue('D2', 'Created By')
                ->setCellValue('E2', 'Send To')
                ->setCellValue('F2', 'Reason')
                ->setCellValue('G2', 'Status')
                ->setCellValue('H2', 'Action Taken')
                ->setCellValue('I2', 'Loop Closed')
                ->setCellValue('J2', 'Discription');
            $k = 3;
            if (!empty($resultData)) {
                foreach ($resultData as $i => $iValue) {
                    $assignToName = "";
                    if (isset($iValue["escalationAssignTo"]) && !empty($iValue["escalationAssignTo"])) {
                        foreach ($iValue["escalationAssignTo"] as $s) {
                            if (isset($s["staff"]["firstName"])) {
                                $assignToName .= $s["staff"]["firstName"] . " " . $s["staff"]["lastName"] . ",";
                            }
                        }
                        $assignToName = rtrim($assignToName, ',');
                    }

                    $escalationAction = "";
                    if (isset($iValue["escalationAction"]) && !empty($iValue["escalationAction"])) {
                        foreach ($iValue["escalationAction"] as $a) {
                            if (isset($a["action"])) {
                                $escalationAction .= $a["action"] . ",";
                            }
                        }
                        $escalationAction = rtrim($escalationAction, ',');
                    }

                    if (isset($iValue["escalationClose"]["status"])) {
                        if ($iValue["escalationClose"]["status"] !== "Closed" && $iValue["status"] === "Responded") {
                            $escalationClose = "Open";
                        } else {
                            $escalationClose = $resultData[$i]["escalationClose"]["status"];
                        }
                    } else {
                        $escalationClose = "";
                    }

                    if (isset($iValue["escalationAuditDescription"]["description"])) {
                        $description = $resultData[$i]["escalationAuditDescription"]["description"];
                        $description = str_replace(array("\r\n", "\n"), "", $description);
                    } else {
                        $description = "";
                    }


                    $createdAt = date("M d, Y", $iValue["createdAt"]);

                    $sheet->setCellValue('A' . $k, $iValue["patient"]["fullName"]);
                    $sheet->setCellValue('B' . $k, $iValue["type"]);
                    $sheet->setCellValue('C' . $k, $createdAt);
                    $sheet->setCellValue('D' . $k, $iValue["assignedBy"]["fullName"]);
                    $sheet->setCellValue('E' . $k, $assignToName);
                    $sheet->setCellValue('F' . $k, $iValue["reason"]);
                    $sheet->setCellValue('G' . $k, $iValue["status"]);
                    $sheet->setCellValue('H' . $k, $escalationAction);
                    $sheet->setCellValue('I' . $k, $escalationClose);
                    $sheet->setCellValue('J' . $k, $description);
                    $k++;
                }
            }
            $fileName = "escalationReport_" . time() . ".xlsx";
            ExcelGeneratorService::writerSave($writer, $fileName);
            exit;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public static function vitalExcelExport($request, $id)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $writer = new Xlsx($spreadsheet);

            $timezone = "";
            if ($id) {
                $exportRequest = ExportReportRequest::where("udid", $id)->first()->toArray();
                if (!empty($exportRequest)) {
                    if (isset($request->timezone) && !empty($request->timezone)) {
                        $timezone = $request->timezone;
                    } else {
                        if (isset($exportRequest["customTimezone"])) {
                            $timezone = $exportRequest["customTimezone"];
                        }
                    }
                    $user = User::find($exportRequest["userId"])->toArray();
                    if (isset($user['roleId']) && $user['roleId'] == 3) {
                        $userStaff = User::with(['roles', 'staff'])->where("id", $exportRequest["userId"])->first();
                    } else {
                        $userStaff = "";
                    }
                }
            } else {
                $exportRequest = "";
                $user = "";
                $userStaff = "";
            }

            if($request->patientId){
                $patient = Patient::where('udid', $request->patientId)->first();
                $type = '';
                $fromDate = '';
                $toDate = '';
                $deviceType = 99;
                if (!empty($request->toDate)) {
                    $toDate = date("Y-m-d H:i:s", $request->toDate);
                }
                if (!empty($request->fromDate)) {
                    $fromDate = date("Y-m-d H:i:s", $request->fromDate);
                }
                if (!empty($request->type)) {
                    $type = $request->type;
                }
                if (!empty($request->deviceType)) {
                    $deviceType = $request->deviceType;
                }

                if (isset($patient->id)) {
                    $patientIdx = $patient->id;
                } else {
                    return response()->json(['message' => "invalid Patient Id."], 403);
                }

                $data = DB::select(
                    'CALL getPatientVital("' . $patientIdx . '","' . $fromDate . '","' . $toDate . '","' . $type . '","' . $deviceType . '")',
                );

                $resultData =  fractal()->collection($data)->transformWith(new PatientVitalTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
                // 99 blood pressure, 100 Oximeter, 101 Glucose

                if (!empty($timezone)) {
                    date_default_timezone_set($timezone);
                }
                if(count($resultData) > 0){
                    $result = array();
                    foreach ($resultData as $res) {
                        $result[$res['takeTime']][] = $res;
                    }

                    if ($deviceType == 99) {
                        ExcelGeneratorService::getBloodPressureVital($sheet, $result);
                    } elseif ($deviceType == 100) {
                        ExcelGeneratorService::getBloodOxygenVital($sheet, $result);
                    } elseif ($deviceType == 101) {
                        ExcelGeneratorService::getBloodGlucoseVital($sheet, $result);
                    }
                }

                $fileName = "vitalReport_" . time() . ".xlsx";
                ExcelGeneratorService::writerSave($writer, $fileName);
                exit;
            }
            echo "Patient Id required";
            die;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public static function getBloodGlucoseVital($sheet, $result)
    {
        $headingFrom = "A1"; // or any value
        $headingTo = "H1"; // or any value
        $sheet->setCellValue('A1', 'Blood Glucose')->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setSize(16);
        $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
        $sheet->getStyle("A2:H2")->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(100, 'pt');
        $sheet->getColumnDimension('B')->setWidth(110, 'pt');
        $sheet->getColumnDimension('C')->setWidth(110, 'pt');
        $sheet->getColumnDimension('E')->setWidth(80, 'pt');
        $sheet->getColumnDimension('F')->setWidth(80, 'pt');
        $sheet->getColumnDimension('G')->setWidth(110, 'pt');
        $sheet->getColumnDimension('H')->setWidth(80, 'pt');
        $sheet->setCellValue('A2', 'Time & Date Vitals Taken')
            ->setCellValue('B2', 'Random Blood Sugar')
            ->setCellValue('C2', 'Fasting Blood Sugar')
            ->setCellValue('D2', 'Fasting')
            ->setCellValue('E2', 'Pre Meal')
            ->setCellValue('F2', 'Post Meal')
            ->setCellValue('G2', 'Before Sleep')
            ->setCellValue('H2', 'General');
        $k = 3;
        if (!empty($result)) {
            $takeTime = "";
            foreach($result as $key => $v){
                for($i = 0; $i < 8; $i++){
                    if($i == 0){
                        $takeTime = date("M d, Y h:i A", $result[$key][$i]["takeTime"]);
                        $sheet->setCellValue('A' . $k, $takeTime);
                    }

                    if (isset($result[$key][$i]["vitalField"]) && $result[$key][$i]["vitalField"] === "Random Blood Sugar") {
                        $sheet->setCellValue('B' . $k, $result[$key][$i]["value"]);
                    }

                    if (isset($result[$key][$i]["vitalField"]) && $result[$key][$i]["vitalField"] === "Fasting Blood Sugar") {
                        $sheet->setCellValue('C' . $k, $result[$key][$i]["value"]);
                    }

                    if (isset($result[$key][$i]["vitalField"]) && $result[$key][$i]["vitalField"] === "Fasting") {
                        $sheet->setCellValue('D' . $k, $result[$key][$i]["value"]);
                    }

                    if (isset($result[$key][$i]["vitalField"]) && $result[$key][$i]["vitalField"] === "Pre Meal") {
                        $sheet->setCellValue('E' . $k, $result[$key][$i]["value"]);
                    }

                    if (isset($result[$key][$i]["vitalField"]) && $result[$key][$i]["vitalField"] === "Post Meal") {
                        $sheet->setCellValue('F' . $k, $result[$key][$i]["value"]);
                    }

                    if (isset($result[$key][$i]["vitalField"]) && $result[$key][$i]["vitalField"] === "Before Sleep") {
                        $sheet->setCellValue('G' . $k, $result[$key][$i]["value"]);
                    }

                    if (isset($result[$key][$i]["vitalField"]) && $result[$key][$i]["vitalField"] === "General") {
                        $sheet->setCellValue('H' . $k, $result[$key][$i]["value"]);
                    }
                }
                $k++;
            }
        }
    }

    public static function getBloodOxygenVital($sheet, $result): void
    {
        $headingFrom = "A1"; // or any value
        $headingTo = "C1"; // or any value
        $sheet->setCellValue('A1', 'Blood Oxygen Saturation')->mergeCells('A1:C1');
        $sheet->getStyle('A1')->getFont()->setSize(16);
        $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
        $sheet->getStyle("A2:C2")->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(110, 'pt');
        $sheet->getColumnDimension('B')->setWidth(110, 'pt');
        $sheet->getColumnDimension('C')->setWidth(110, 'pt');
        $sheet->setCellValue('A2', 'Time & Date Vitals Taken')
            ->setCellValue('B2', 'SPO2')
            ->setCellValue('C2', 'BPM');
        $k = 3;
        if (!empty($result)) {
            foreach ($result as $key => $v) {
                for ($i = 0; $i < 8; $i++) {
                    if ($i == 0) {
                        $takeTime = date("M d, Y h:i A", $result[$key][$i]["takeTime"]);
                        $sheet->setCellValue('A' . $k, $takeTime);
                    }

                    if (isset($result[$key][$i]["vitalField"]) && $result[$key][$i]["vitalField"] === "SPO2") {
                        $sheet->setCellValue('B' . $k, $result[$key][$i]["value"]);
                    }

                    if (isset($result[$key][$i]["vitalField"]) && $result[$key][$i]["vitalField"] === "BPM") {
                        $sheet->setCellValue('C' . $k, $result[$key][$i]["value"]);
                    }
                }
                $k++;
            }
        }
    }

    public static function getBloodPressureVital($sheet, $result): void
    {
        $headingFrom = "A1"; // or any value
        $headingTo = "D1"; // or any value
        $sheet->setCellValue('A1', 'Blood Oxygen Saturation')->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setSize(16);
        $sheet->getStyle("$headingFrom:$headingTo")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("$headingFrom:$headingTo")->getFont()->setBold(true);
        $sheet->getStyle("A2:D2")->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(110, 'pt');
        $sheet->getColumnDimension('B')->setWidth(110, 'pt');
        $sheet->getColumnDimension('C')->setWidth(110, 'pt');
        $sheet->getColumnDimension('D')->setWidth(110, 'pt');
        $sheet->setCellValue('A2', 'Time & Date Vitals Taken')
            ->setCellValue('B2', 'Systolic')
            ->setCellValue('C2', 'Diastolic')
            ->setCellValue('D2', 'BPM');

        $k = 3;
        if (!empty($result)) {
            foreach ($result as $key => $v) {
                for ($i = 0; $i < 8; $i++) {
                    if ($i == 0) {
                        $takeTime = date("M d, Y h:i A", $result[$key][$i]["takeTime"]);
                        $sheet->setCellValue('A' . $k, $takeTime);
                    }

                    if (isset($result[$key][$i]["vitalField"]) && $result[$key][$i]["vitalField"] === "Systolic") {
                        $sheet->setCellValue('B' . $k, $result[$key][$i]["value"]);
                    }

                    if (isset($result[$key][$i]["vitalField"]) && $result[$key][$i]["vitalField"] === "Diastolic") {
                        $sheet->setCellValue('C' . $k, $result[$key][$i]["value"]);
                    }

                    if (isset($result[$key][$i]["vitalField"]) && $result[$key][$i]["vitalField"] === "BPM") {
                        $sheet->setCellValue('D' . $k, $result[$key][$i]["value"]);
                    }
                }
                $k++;
            }
        }
    }

    public static function writerSave($writer, $fileName): void
    {
        try {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
            $writer->save('php://output');
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
