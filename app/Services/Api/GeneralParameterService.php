<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\GeneralParameter\GeneralParameter;
use App\Models\GeneralParameter\GeneralParameterGroup;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Transformers\GeneralParameter\GeneralParameterTransformer;
use App\Transformers\GeneralParameter\GeneralParameterGroupTransformer;

class GeneralParameterService
{
    // Add General Parameter
    public function generalParameterAdd($request, $id)
    {
        DB::beginTransaction();
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            if (!$id) {
                $group = [
                    'name' => $request->input('generalParameterGroup'), 'deviceTypeId' => $request->input('deviceTypeId'), 'entityType' => $entityType,
                    'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation
                ];
                $groupData = GeneralParameterGroup::create($group);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'generalParameterGroups', 'tableId' => $groupData->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($group), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                ];
                ChangeLog::create($changeLog);
                $parameter = $request->input('parameter');
                foreach ($parameter as $value) {
                    $input = [
                        'generalParameterGroupId' => $groupData->id, 'vitalFieldId' => $value['type'], 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'highLimit' => $value['highLimit'], 'lowLimit' => $value['lowLimit'], 'createdBy' => 1, 'udid' => Str::uuid()->toString(), 'entityType' => $entityType
                    ];
                    $general = GeneralParameter::create($input);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'generalParameters', 'tableId' => $general->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                    ];
                    ChangeLog::create($changeLog);
                }
                $data = GeneralParameterGroup::where('id', $groupData->id)->with('generalParameter')->first();
                $userdata = fractal()->item($data)->transformWith(new GeneralParameterGroupTransformer())->toArray();
                $message = ['message' => trans('messages.createdSuccesfully')];
            } else {
                $group = ['name' => $request->input('generalParameterGroup'), 'updatedBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                $groupData = GeneralParameterGroup::where('udid', $id)->update($group);
                // $groupInput = Helper::entity('udid', $id);
                // $changeLog = [
                //     'udid' => Str::uuid()->toString(), 'table' => 'generalParameterGroups', 'tableId' => $groupInput,
                //     'value' => json_encode($group), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                // ];
                // ChangeLog::create($changeLog);
                $genralParameter = GeneralParameterGroup::where('udid', $id)->first();
                $parameter = $request->input('parameter');
                foreach ($parameter as $value) {
                    if (!empty($value['parameterId'])) {
                        $input = ['highLimit' => $value['highLimit'], 'lowLimit' => $value['lowLimit'], 'updatedBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                        GeneralParameter::where('udid', $value['parameterId'])->update($input);
                        // $general = Helper::entity('udid', $value['parameterId']);
                        // $changeLog = [
                        //     'udid' => Str::uuid()->toString(), 'table' => 'generalParameters', 'tableId' => $general,
                        //     'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                        // ];
                        // ChangeLog::create($changeLog);
                    } else {
                        $input = [
                            'generalParameterGroupId' => $genralParameter['id'], 'vitalFieldId' => $value['type'], 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                            'highLimit' => $value['highLimit'], 'lowLimit' => $value['lowLimit'], 'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString()
                        ];
                        $generalData = GeneralParameter::create($input);
                        $changeLog = [
                            'udid' => Str::uuid()->toString(), 'table' => 'generalParameters', 'tableId' => $generalData->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                            'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                        ];
                        ChangeLog::create($changeLog);
                    }
                }
                $data = GeneralParameterGroup::where('udid', $id)->with('generalParameter')->first();
                $userdata = fractal()->item($data)->transformWith(new GeneralParameterGroupTransformer())->toArray();
                $message = ['message' => trans('messages.updatedSuccesfully')];
            }
            DB::commit();
            $endData = array_merge($message, $userdata);
            return $endData;
        } catch (Exception $e) {
            DB::rollback();
            throw new \RuntimeException($e);
        }
    }

    // List General Parameter Group
    public function generalParameterGroupList($request, $id)
    {
        try {
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
            if (!$id) {
                if ($request->all) {
                    $data = $data->where('generalParameterGroups.name', 'LIKE', '%' . $request->search . '%')->with('generalParameter')->orderBy('generalParameterGroups.createdAt', 'DESC')->get();
                    return fractal()->collection($data)->transformWith(new GeneralParameterGroupTransformer())->toArray();
                } else {
                    $data->where('generalParameterGroups.name', 'LIKE', '%' . $request->search . '%');
                    // if (request()->header('providerId')) {
                    //     $provider = Helper::providerId();
                    //     $data->where('generalParameterGroups.providerId', $provider);
                    // }
                    // if (request()->header('providerLocationId')) {
                    //     $providerLocation = Helper::providerLocationId();
                    //     if (request()->header('entityType') == 'Country') {
                    //         $data->where([['generalParameterGroups.providerLocationId', $providerLocation], ['generalParameterGroups.entityType', 'Country']]);
                    //     }
                    //     if (request()->header('entityType') == 'State') {
                    //         $data->where([['generalParameterGroups.providerLocationId', $providerLocation], ['generalParameterGroups.entityType', 'State']]);
                    //     }
                    //     if (request()->header('entityType') == 'City') {
                    //         $data->where([['generalParameterGroups.providerLocationId', $providerLocation], ['generalParameterGroups.entityType', 'City']]);
                    //     }
                    //     if (request()->header('entityType') == 'subLocation') {
                    //         $data->where([['generalParameterGroups.providerLocationId', $providerLocation], ['generalParameterGroups.entityType', 'subLocation']]);
                    //     }
                    // }
                    // if (request()->header('programId')) {
                    //     $program = Helper::programId();
                    //     $entityType = Helper::entityType();
                    //     $data->where([['generalParameterGroups.programId', $program], ['generalParameterGroups.entityType', $entityType]]);
                    // }
                    if ($request->orderField == 'deviceType') {
                        $data->join('globalCodes as deviceType', 'deviceType.id', '=', 'generalParameterGroups.deviceTypeId')->orderBy('deviceType.name', $request->orderBy);
                    } elseif ($request->orderField == 'vitalFieldName') {
                        $data->join('generalParameters', 'generalParameters.generalParameterGroupId', '=', 'generalParameterGroups.id')
                            ->join('vitalFields', 'vitalFields.id', '=', 'generalParameters.vitalFieldId')
                            ->orderBy('vitalFields.name', $request->orderBy);
                    } elseif ($request->orderField == 'generalParameterGroup') {
                        $data->orderBy('generalParameterGroups.name', $request->orderBy);
                    } else {
                        $data->orderBy('generalParameterGroups.name', 'ASC');
                    }
                    $data = $data->select('generalParameterGroups.*')->paginate(env('PER_PAGE', 20));
                    return fractal()->collection($data)->transformWith(new GeneralParameterGroupTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
                }
            } else {
                $data->where('generalParameterGroups.udid', $id)->with('generalParameter');
                $data = $data->first();
                return fractal()->item($data)->transformWith(new GeneralParameterGroupTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List General Parameter
    public function generalParameterList($request, $id)
    {
        try {
            $data = GeneralParameter::select('generalParameters.*')->with('generalParameterGroup');

            // $data->leftJoin('providers', 'providers.id', '=', 'generalParameters.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'generalParameters.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('generalParameters.providerLocationId', '=', 'providerLocations.id')->where('generalParameters.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('generalParameters.providerLocationId', '=', 'providerLocationStates.id')->where('generalParameters.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('generalParameters.providerLocationId', '=', 'providerLocationCities.id')->where('generalParameters.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('generalParameters.providerLocationId', '=', 'subLocations.id')->where('generalParameters.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('generalParameters.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['generalParameters.providerLocationId', $providerLocation], ['generalParameters.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['generalParameters.providerLocationId', $providerLocation], ['generalParameters.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['generalParameters.providerLocationId', $providerLocation], ['generalParameters.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['generalParameters.providerLocationId', $providerLocation], ['generalParameters.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['generalParameters.programId', $program], ['generalParameters.entityType', $entityType]]);
            // }
            if (!$id) {
                $data = $data->orderBy('generalParameters.createdAt', 'DESC')->get();
                return fractal()->collection($data)->transformWith(new GeneralParameterTransformer())->toArray();
            } else {
                $data = $data->where('generalParameters.udid', $id)->first();
                return fractal()->item($data)->transformWith(new GeneralParameterTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete General Parameter Group
    public function generalParameterGroupDelete($request, $id)
    {
        DB::beginTransaction();
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $input = [
                'deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation
            ];
            GeneralParameterGroup::where('udid', $id)->update($input);
            GeneralParameter::where('generalParameterGroupId', $id)->update($input);
            GeneralParameterGroup::where('udid', $id)->delete();
            GeneralParameter::where('generalParameterGroupId', $id)->delete();
            DB::commit();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            DB::rollback();
            throw new \RuntimeException($e);
        }
    }

    // Delete General Parameter
    public function generalParameterDelete($request, $id)
    {
        DB::beginTransaction();
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $input = [
                'updatedBy' => Auth::id(), 'highLimit' => '', 'lowLimit' => '', 'providerId' => $provider, 'providerLocationId' => $providerLocation
            ];
            GeneralParameter::where('udid', $id)->update($input);
            $generalData = Helper::entity('generalParameter', $id);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'generalParameters', 'tableId' => $generalData,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation
            ];
            ChangeLog::create($changeLog);
            DB::commit();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            DB::rollback();
            throw new \RuntimeException($e);
        }
    }
}
