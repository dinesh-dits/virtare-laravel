<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Services\Api\TaskService;
use App\Http\Controllers\Controller;
use App\Services\Api\ExcelGeneratorService;
use App\Services\Api\ExportReportRequestService;

class TaskController extends Controller
{
    // Add Task
    public function addTask(request $request)
    {
        return (new TaskService)->addTask($request);
    }

    // List Task
    public function listTask(request $request)
    {
        return (new TaskService)->listTask($request);
    }

    // Task List Entity
    public function taskListEntity(request $request, $entity, $id)
    {
        return (new TaskService)->entityTaskList($request, $entity, $id);
    }

    // Task Priority
    public function priorityTask(request $request)
    {
        return (new TaskService)->priorityTask($request);
    }

    // Task Status
    public function statusTask(request $request)
    {
        return (new TaskService)->statusTask($request);
    }

    // Update Task
    public function updateTask(request $request, $id)
    {
        return (new TaskService)->updateTask($request, $id);
    }

    // Task By Id
    public function taskById(request $request, $id)
    {
        return (new TaskService)->taskById($request, $id);
    }

    // Delete Task
    public function deleteTask(request $request, $id)
    {
        return (new TaskService)->deleteTask($request, $id);
    }

    // Task Per Staff
    public function taskPerStaff(request $request)
    {
        return (new TaskService)->taskPerStaff($request);
    }

    // Task Per Category
    public function taskPerCategory(request $request)
    {
        return (new TaskService)->taskPerCategory($request);
    }

    // Task report
    public function taskReport(Request $request, $id)
    {
        if ($id) {
            $reportType = "task_report";
            $checkReport = ExportReportRequestService::checkReportRequest($id, $reportType);
            if ($checkReport) {
                ExcelGeneratorService::taskReportExport($request, $id);
            } else {
                return response()->json(['message' => "User not Access to download Report."], 403);
            }
        } else {
            return response()->json(['message' => "invalid URL."], 400);
        }
    }

    // Task Total With Time Duration
    public function taskTotalWithTimeDuration(request $request)
    {
        return (new TaskService)->taskTotalWithTimeDuration($request);
    }

    // Task Completed Rates
    public function taskCompletedRates(request $request)
    {
        return (new TaskService)->taskCompletedRates($request);
    }

    // Task Assigne List
    public function taskAssigneList(Request $request, $id)
    {
        return (new TaskService)->taskAssigneList($request, $id);
    }
}
