<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\BugReport\BugReport;
use Illuminate\Support\Facades\Auth;
use App\Models\BugReport\BugReportDocument;
use App\Models\Screen\Screen;
use App\Transformers\BugReport\BugReportTransformer;
use App\Transformers\BugReport\ScreenTransformer;

class BugReportService
{

    // List Bug Report
    public function bugReportList($request, $bugReportId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $data = BugReport::select('bugReports.*')->with('bugDocument', 'bugScreen', 'bugCategory');

            // $data->leftJoin('providers', 'providers.id', '=', 'bugReports.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'bugReports.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('bugReports.providerLocationId', '=', 'providerLocations.id')->where('bugReports.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('bugReports.providerLocationId', '=', 'providerLocationStates.id')->where('bugReports.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('bugReports.providerLocationId', '=', 'providerLocationCities.id')->where('bugReports.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('bugReports.providerLocationId', '=', 'subLocations.id')->where('bugReports.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('bugReports.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['bugReports.providerLocationId', $providerLocation], ['bugReports.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['bugReports.providerLocationId', $providerLocation], ['bugReports.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['bugReports.providerLocationId', $providerLocation], ['bugReports.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['bugReports.providerLocationId', $providerLocation], ['bugReports.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['bugReports.programId', $program], ['bugReports.entityType', $entityType]]);
            // }
            if (!$bugReportId) {
                $data = $data->get();
                return fractal()->collection($data)->transformWith(new BugReportTransformer())->toArray();
            } else {
                $bugReport = BugReport::where('udid', $bugReportId)->first();
                $data = $data->where('bugReports.bugReportId', $bugReport->bugReportId)->first();
                return fractal()->item($data)->transformWith(new BugReportTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Bug Report
    public function createBugReport($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $data = [
                'udid' => Str::uuid()->toString(),
                'screenId' => $request->input('screenId'),
                'screenType' => $request->input('screenType'),
                'subjectTitle' => $request->input('subjectTitle'),
                'buildVersion' => $request->input('buildVersion'),
                'osVersion' => $request->input('osVersion'),
                'deviceName' => $request->input('deviceName'),
                'deviceId' => $request->input('deviceId'),
                'userLoginEmail' => $request->input('userLoginEmail'),
                'bugReportEmail' => $request->input('bugReportEmail'),
                'categoryId' => $request->input('categoryId'),
                'priority' => $request->input('priority'),
                'platform' => $request->input('platform'),
                'location' => $request->input('location'),
                'description' => $request->input('description'),
                'userId' => Auth::id(),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'entityType' => $entityType,
            ];
            $newData = BugReport::create($data);
            $bugReport = BugReport::where('udid', $newData->udid)->first();
            if (!empty($request->input('attachment'))) {
                $attachments = $request->input('attachment');
                foreach ($attachments as $attachment) {
                    $data = [
                        'udid' => Str::uuid()->toString(),
                        'bugReportId' => $bugReport->bugReportId,
                        'filePath' => $attachment,
                        'providerId' => $provider,
                        'providerLocationId' => $providerLocation,
                        'entityType' => $entityType,
                    ];
                    BugReportDocument::create($data);
                }
            }
            //66
            $findKeys = array($request->input('categoryId'), $request->input('priority'), $request->input('categoryId'));
            $globalNames = Helper::Globalnames($findKeys);
            $screen = Screen::where('id', $request->input('screenId'))->first();
            $description = $request->input('description');
            $description .= '@@App Version : ' . $request->input('buildVersion');
            $description .= '@@Device Name : ' . $request->input('deviceName');
            $description .= '@@Os Version : ' . $request->input('osVersion');
            $description .= '@@Device Id : ' . $request->input('deviceId');
            $description .= '@@Device Type : ' . $request->input('platform');
            $description .= '@@Location : ' . $request->input('location');
            $description .= '@@User Email : ' . $request->input('userLoginEmail');
            $description .= '@@Bug Reported By : ' . $request->input('bugReportEmail');
            if (isset($globalNames[$request->input('categoryId')])) {
                $description .= '@@Bug Category : ' . $globalNames[$request->input('categoryId')];
            }
            if (isset($screen->id)) {
                $description .= '@@Screen : ' . $screen->name;
            }

            $severity = '';
            if (isset($globalNames[$request->input('priority')])) {
                $severity = $globalNames[$request->input('priority')];
            }
            $reportType = '';
            if (isset($globalNames[$request->input('categoryId')])) {
                $reportType = $globalNames[$request->input('categoryId')];
            }

            Helper::createSubTaskonJira($request->input('subjectTitle'), $bugReport->bugReportId, $description, $severity, $request->input('screenType'), $reportType);
            return response()->json(['message' => trans('messages.createdSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Bug Report
    public function deleteBugReport($request, $bugReportId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $bugReport = BugReport::where('udid', $bugReportId)->first();
            $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'deletedAt' => Carbon::now(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            BugReport::where('bugReportId', $bugReport->bugReportId)->update($input);
            BugReportDocument::where('bugReportId', $bugReport->bugReportId)->update($input);
            return response()->json(['message' => trans('messages.deletedSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Screen listing
    public function screenList($request)
    {
        try {
            $type = $request->type;
            if ($type) {
                $data = Screen::where('type', $type)->get();
                return fractal()->collection($data)->transformWith(new ScreenTransformer())->toArray();
            }
            $data = Screen::all();
            return fractal()->collection($data)->transformWith(new ScreenTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
