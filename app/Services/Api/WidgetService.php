<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use Illuminate\Support\Carbon;
use App\Models\Widget\WidgetAccess;
use Illuminate\Support\Facades\Auth;
use App\Models\AccessRole\AccessRole;
use App\Models\WidgetModule\WidgetModule;
use App\Models\Dashboard\DashboardWidgetByRole;
use App\Models\Widget\Widget;
use App\Transformers\Widget\WidgetAccessTransformer;
use App\Transformers\Widget\WidgetModuleTransformer;
use App\Transformers\Widget\WidgetUpdateTransformer;
use App\Transformers\Widget\AssignedWidgetTransformer;
use App\Transformers\Widget\DashboardWidgetTransformer;
use App\Models\GlobalCode\GlobalCode;
use App\Models\UserRole\UserRole;

class WidgetService
{
    // List Widget
    public function getWidget()
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $data = WidgetModule::select('widgetModules.*');

            // $data->leftJoin('providers', 'providers.id', '=', 'widgetModules.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'widgetModules.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('widgetModules.providerLocationId', '=', 'providerLocations.id')->where('widgetModules.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('widgetModules.providerLocationId', '=', 'providerLocationStates.id')->where('widgetModules.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('widgetModules.providerLocationId', '=', 'providerLocationCities.id')->where('widgetModules.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('widgetModules.providerLocationId', '=', 'subLocations.id')->where('widgetModules.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('widgetModules.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['widgetModules.providerLocationId', $providerLocation], ['widgetModules.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['widgetModules.providerLocationId', $providerLocation], ['widgetModules.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['widgetModules.providerLocationId', $providerLocation], ['widgetModules.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['widgetModules.providerLocationId', $providerLocation], ['widgetModules.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['widgetModules.programId', $program], ['widgetModules.entityType', $entityType]]);
            // }
            $data = $data->with('widgets')->get();
            return fractal()->collection($data)->transformWith(new WidgetModuleTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Assigne Widget
    public function assignWidget($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $input = [
                'widgetId' => $request->widgetId,
                'widgetPath' => $request->widgetPath,
                'roleId' => $request->roleId,
                'canNotViewModifyOrDelete' => $request->canNotViewModifyOrDelete,
                'createdBy' => Auth::id(),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation
            ];
            $data = DashboardWidgetByRole::create($input);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'dashboardWidgetByRoles', 'tableId' => $data->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            return response()->json(['message' => trans('messages.createdSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Assigned Widgets
    public function getAssignedWidget()
    {
        try {
            $data = DashboardWidgetByRole::select('dashboardWidgetByRoles.*');

            // $data->leftJoin('providers', 'providers.id', '=', 'dashboardWidgetByRoles.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'dashboardWidgetByRoles.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('dashboardWidgetByRoles.providerLocationId', '=', 'providerLocations.id')->where('dashboardWidgetByRoles.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('dashboardWidgetByRoles.providerLocationId', '=', 'providerLocationStates.id')->where('dashboardWidgetByRoles.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('dashboardWidgetByRoles.providerLocationId', '=', 'providerLocationCities.id')->where('dashboardWidgetByRoles.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('dashboardWidgetByRoles.providerLocationId', '=', 'subLocations.id')->where('dashboardWidgetByRoles.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('dashboardWidgetByRoles.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['dashboardWidgetByRoles.providerLocationId', $providerLocation], ['dashboardWidgetByRoles.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['dashboardWidgetByRoles.providerLocationId', $providerLocation], ['dashboardWidgetByRoles.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['dashboardWidgetByRoles.providerLocationId', $providerLocation], ['dashboardWidgetByRoles.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['dashboardWidgetByRoles.providerLocationId', $providerLocation], ['dashboardWidgetByRoles.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['dashboardWidgetByRoles.programId', $program], ['dashboardWidgetByRoles.entityType', $entityType]]);
            // }
            $data = $data->with('widget', 'widgetType', 'role')->where('dashboardWidgetByRoles.canNotViewModifyOrDelete', 0)->get();
            return fractal()->collection($data)->transformWith(new AssignedWidgetTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Widget
    public function updateWidget($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $input = [$request->all(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            $data = DashboardWidgetByRole::findOrFail($id);
            $data->update($input);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'dashboardWidgetByRoles', 'tableId' => $id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($request->all()), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            return fractal()->item($data)->transformWith(new WidgetUpdateTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Widget Access
    public function listWidgetAccess($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $data = WidgetAccess::select('widgetAccesses.*');

            // $data->leftJoin('providers', 'providers.id', '=', 'widgetAccesses.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'widgetAccesses.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('widgetAccesses.providerLocationId', '=', 'providerLocations.id')->where('widgetAccesses.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('widgetAccesses.providerLocationId', '=', 'providerLocationStates.id')->where('widgetAccesses.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('widgetAccesses.providerLocationId', '=', 'providerLocationCities.id')->where('widgetAccesses.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('widgetAccesses.providerLocationId', '=', 'subLocations.id')->where('widgetAccesses.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('widgetAccesses.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['widgetAccesses.providerLocationId', $providerLocation], ['widgetAccesses.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['widgetAccesses.providerLocationId', $providerLocation], ['widgetAccesses.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['widgetAccesses.providerLocationId', $providerLocation], ['widgetAccesses.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['widgetAccesses.providerLocationId', $providerLocation], ['widgetAccesses.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['widgetAccesses.programId', $program], ['widgetAccesses.entityType', $entityType]]);
            // }
            $role = AccessRole::where('udid', $id)->first();
            $data = $data->where('widgetAccesses.accessRoleId', $role->id)->with('widget')->get();
            return fractal()->collection($data)->transformWith(new WidgetAccessTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Widget Access
    public function createWidgetAccess($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $role = AccessRole::where('udid', $id)->first();
            $widgetAss = WidgetAccess::where('accessRoleId', $role->id)->first();
            $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'deletedAt' => Carbon::now(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            WidgetAccess::where('accessRoleId', $role->id)->update($input);
            $widget = $request->widgets;
            foreach ($widget as $widgetId) {
                $widgets = [
                    'udid' => Str::uuid()->toString(),
                    'accessRoleId' => $role->id,
                    'widgetId' => $widgetId,
                    'providerId' => $provider,
                    'providerLocationId' => $providerLocation
                ];
                $widgetData = WidgetAccess::create($widgets);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'widgetAccesses', 'tableId' => $widgetData->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($widgets), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
            }
            if (is_null($widgetAss)) {
                return response()->json(['message' => trans('messages.createdSuccesfully')]);
            } else {
                return response()->json(['message' => trans('messages.updatedSuccesfully')]);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Widget Access
    public function deleteWidgetAccess($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            WidgetAccess::where('udid', $id)->update($input);
            $widget = Helper::entity('widgetAccess', $id);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'widgetAccesses', 'tableId' => $widget, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            WidgetAccess::where('udid', $id)->delete();
            return response()->json(['message' => trans('messages.deletedSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    //Add Dashboard Widgets
    public function addDashboardWidget($request, $id)
    {
        try {
            $userId = Auth::id();
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $data = Widget::where('id', $id)->first();
            if (!empty($data)) {
                $input = array();
                if (!empty($request->widgetName)) {
                    $input['widgetName'] = $request->widgetName;
                }
                if (!empty($request->title)) {
                    $input['title'] = $request->title;
                }
                if (!empty($request->widgetModuleId)) {
                    $input['widgetModuleId'] = $request->widgetModuleId;
                }
                if (!empty($request->type)) {
                    $input['type'] = $request->type;
                }
                if (!empty($request->widgetType)) {
                    $input['widgetType'] = $request->widgetType;
                }
                if (!empty($request->endPoint)) {
                    $input['endPoint'] = $request->endPoint;
                }
                if (!empty($provider)) {
                    $input['providerId'] = $provider;
                }
                if (!empty($providerLocation)) {
                    $input['providerlocationId'] = $providerLocation;
                }
                if (!empty($input)) {
                    Widget::where('id', $id)->update($input);
                }
            } else {
                $input = [
                    'udid' => Str::uuid()->toString(),
                    'widgetName' => $request->widgetName,
                    'title' => $request->title,
                    'widgetModuleId' => $request->widgetModuleId,
                    'type' => $request->type,
                    'widgetType' => $request->widgetType,
                    'endPoint' => $request->endPoint,
                    'providerId' => $provider,
                    'providerlocationId' => $providerLocation,
                    'createdBy' => Auth::id()
                ];
                Widget::create($input);
            }

            return response()->json(['message' => trans('messages.createdSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Dashboard Widgets Listing
    public function dashboardWidgetList()
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $staffId = auth()->user()->staff->id;
            $userRole = UserRole::where('staffId', $staffId)->get();
            $accessRoleId = array();
            $response = array();
            if ($userRole->count() > 0) {
                foreach ($userRole as $key => $role) {
                    $accessRoleId[$key] = $role->accessRoleId;
                }
            }
            $data = WidgetModule::select('widgetModules.*');

            // $data->leftJoin('providers', 'providers.id', '=', 'widgetModules.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'widgetModules.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('widgetModules.providerLocationId', '=', 'providerLocations.id')->where('widgetModules.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('widgetModules.providerLocationId', '=', 'providerLocationStates.id')->where('widgetModules.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('widgetModules.providerLocationId', '=', 'providerLocationCities.id')->where('widgetModules.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('widgetModules.providerLocationId', '=', 'subLocations.id')->where('widgetModules.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('widgetModules.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['widgetModules.providerLocationId', $providerLocation], ['widgetModules.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['widgetModules.providerLocationId', $providerLocation], ['widgetModules.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['widgetModules.providerLocationId', $providerLocation], ['widgetModules.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['widgetModules.providerLocationId', $providerLocation], ['widgetModules.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['widgetModules.programId', $program], ['widgetModules.entityType', $entityType]]);
            // }
            $moduleArray = array();
            $assignedArray = array();
            $dataModule = WidgetModule::all();
            foreach ($dataModule as $key => $module):
                $moduleArray[$module->id]['id'] = $module->id;
                $moduleArray[$module->id]['name'] = $module->name;
                $moduleArray[$module->id]['description'] = $module->description;
            endforeach;
            //$data = WidgetModule::with('dashboardWidgets')->get();
            //$data = WidgetModule::with('widgetAccess')->get(); // Pivot Association

            $dataAssigned = WidgetAccess::with('widget')->whereIn('accessRoleId', $accessRoleId)->get();
            $GlobalCodesArray = array();
            $globalCodes = GlobalCode::where('globalCodeCategoryId', 86)->get();
            foreach ($globalCodes as $key => $code):
                $GlobalCodesArray[$code->id] = $code->name;
            endforeach;
            foreach ($dataAssigned as $key => $assigned) {
                $assignedArray[$assigned->widget->widgetModuleId][$key] = $assigned->widget;
            }
            if (count($moduleArray) > 0) {
                $index = 0;
                foreach ($moduleArray as $key => $dataRecord):
                    if (isset($assignedArray[$dataRecord['id']])) {
                        $response[$index]['id'] = $dataRecord['id'];
                        $response[$index]['name'] = $dataRecord['name'];
                        $response[$index]['description'] = $dataRecord['description'];
                        $response[$index]['widget'] = array();
                        ///   if(isset( $assignedArray[$dataRecord['id']])){
                        $count = 0;
                        foreach ($assignedArray[$dataRecord['id']] as $key1 => $dashboardWidgets):

                            if (array_search($dashboardWidgets->id, array_column($response[$index]['widget'], 'id')) === FALSE) {


                                $response[$index]['widget'][$count]['id'] = $dashboardWidgets->id;
                                $response[$index]['widget'][$count]['udid'] = $dashboardWidgets->udid;
                                $response[$index]['widget'][$count]['widgetName'] = $dashboardWidgets->widgetName;
                                $response[$index]['widget'][$count]['title'] = $dashboardWidgets->title;
                                $response[$index]['widget'][$count]['type'] = isset($GlobalCodesArray[$dashboardWidgets->type]) ? $GlobalCodesArray[$dashboardWidgets->type] : '';
                                $response[$index]['widget'][$count]['widgetType'] = $dashboardWidgets->widgetType;
                                $count++;
                            }
                        endforeach;
                    }
                    $index++;
                endforeach;
            }
            return response()->json(['data' => $response], 200);
            // return fractal()->collection($data)->transformWith(new DashboardWidgetTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
