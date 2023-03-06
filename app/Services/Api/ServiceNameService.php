<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\CPTCode\Service;
use Illuminate\Support\Facades\Auth;
use App\Transformers\CPTCode\ServiceTransformer;

class ServiceNameService
{
    // List Service
    public function listService($request, $id)
    {
        try {
            $data = Service::select('services.*');

            // $data->leftJoin('providers', 'providers.id', '=', 'services.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'services.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('services.providerLocationId', '=', 'providerLocations.id')->where('services.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('services.providerLocationId', '=', 'providerLocationStates.id')->where('services.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('services.providerLocationId', '=', 'providerLocationCities.id')->where('services.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('services.providerLocationId', '=', 'subLocations.id')->where('services.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('services.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['services.providerLocationId', $providerLocation], ['services.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['services.providerLocationId', $providerLocation], ['services.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['services.providerLocationId', $providerLocation], ['services.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['services.providerLocationId', $providerLocation], ['services.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['services.programId', $program], ['services.entityType', $entityType]]);
            // }
            if (!empty($id)) {
                $data = $data->find($id);
                return fractal()->item($data)->transformWith(new ServiceTransformer())->toArray();
            } else {
                $data = $data->orderBy('services.createdAt', 'DESC')->get();
                return fractal()->collection($data)->transformWith(new ServiceTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Service
    public function createService($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $udid = Str::uuid()->toString();
            if ($request->input('serviceName')) {
                $serviceName = $request->input('serviceName');
            } else {
                return response()->json(['message' => "service name is required."], 500);
            }
            $isActive = 1;
            // DB::select('CALL createCPTCode("' . $udid . '","' . $serviceId . '","' . $providerId . '","' . $name . '","' . $billingAmout . '","' . $description . '","'.$durationId.'")');
            $input = [
                "udid" => $udid,
                "name" => $serviceName,
                "isActive" => $isActive,
                'providerId' => $provider,
                'providerLocationId' => $providerLocation
            ];
            $service = Service::insert($input);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'services', 'tableId' => $service->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            $serviceData = Service::where('udid', $udid)->first();
            $message = ['message' => trans('messages.createdSuccesfully')];
            $resp = fractal()->item($serviceData)->transformWith(new ServiceTransformer())->toArray();
            $endData = array_merge($message, $resp);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Service
    public function updateService($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            if ($request->input('serviceName')) {
                $serviceName = $request->input('serviceName');
            }
            $updatedBy = Auth::id();
            $isActive = 1;
            // DB::select('CALL updateCPTCode("'.$id.'","' . $serviceId . '","' . $providerId . '","' . $name . '","' . $billingAmout . '","' . $description . '","'.$durationId.'","'.$updatedBy.'","'.$isActive.'")');
            $input = [
                "name" => $serviceName,
                "isActive" => $isActive,
                "updatedBy" => $updatedBy,
                'providerId' => $provider,
                'providerLocationId' => $providerLocation
            ];
            Service::where("id", $id)->update($input);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'services', 'tableId' => $id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            $serviceData = Service::where('id', $id)->first();
            $message = ['message' => trans('messages.updatedSuccesfully')];
            $resp = fractal()->item($serviceData)->transformWith(new ServiceTransformer())->toArray();
            $endData = array_merge($message, $resp);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Service
    public function deleteService($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $serviceData = Service::where('udid', $id)->first();
            $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            Service::where('udid', $id)->update($input);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'services', 'tableId' => $serviceData->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            return response()->json(['message' => trans('messages.deletedSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
