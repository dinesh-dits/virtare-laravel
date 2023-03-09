<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\Device\DeviceModel;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory\Inventory;
use Illuminate\Support\Facades\Auth;
use App\Transformers\Device\DeviceModelTransformer;
use App\Transformers\Inventory\InventoryTransformer;
use App\Transformers\Inventory\InventoryListTransformer;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Transformers\Inventory\InventorySerialTransformer;

class InventoryService
{
    // Add Inventory
    public function store($request)
    {
        try {
            $provider = Helper::providerId();
            $isActive = $request->isActive;
            //  $input = $request->only(['deviceModelId', 'isActive']);
            $deviceModelId = DeviceModel::where(['deviceTypeId' => $request->deviceTypeId])->first();
            $otherData = [
                'udid' => Str::uuid()->toString(),
                'createdBy' => Auth::id(),
                'isActive' => $isActive == true ? 1 : 0,
                'providerId' => $provider,
                'macAddress' => trim($request->serialNumber),
                'manufactureId' => $request->manufactureId,
                //'serialNumber' => $request->serialNumber,
                'serialNumber' => '',
                'networkId' => $request->networkId,
                'deviceModelId' => $deviceModelId->id,
            ];
            //  $data = json_encode(array_merge($input, $otherData));
            $data = json_encode($otherData);
            DB::select(
                "CALL createInventories('" . $data . "')"
            );
            return response()->json(['message' => trans('messages.createdSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Inventory
    public function index($request)
    {
        try {

            $data = Inventory::select('inventories.*')->with('model', 'inventory')
                ->leftJoin('deviceModels', 'deviceModels.id', '=', 'inventories.deviceModelId')
                ->leftJoin('globalCodes as g1', 'g1.id', '=', 'deviceModels.deviceTypeId')
                ->leftJoin('globalCodes as g2', 'g2.id', '=', 'inventories.manufactureId')
                ->leftJoin('globalCodes as g3', 'g3.id', '=', 'inventories.networkId')
                ->leftJoin('patientInventories', 'patientInventories.inventoryId', '=', 'inventories.id')
                ->leftJoin('patients', 'patients.id', '=', 'patientInventories.patientId');

            /*      $data->leftJoin('providers', 'providers.id', '=', 'inventories.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            $data->leftJoin('programs', 'programs.id', '=', 'inventories.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            $data->leftJoin('providerLocations', function ($join) {
                $join->on('inventories.providerLocationId', '=', 'providerLocations.id')->where('inventories.entityType', '=', 'Country');
            })->whereNull('providerLocations.deletedAt');

            $data->leftJoin('providerLocationStates', function ($join) {
                $join->on('inventories.providerLocationId', '=', 'providerLocationStates.id')->where('inventories.entityType', '=', 'State');
            })->whereNull('providerLocationStates.deletedAt');

            $data->leftJoin('providerLocationCities', function ($join) {
                $join->on('inventories.providerLocationId', '=', 'providerLocationCities.id')->where('inventories.entityType', '=', 'City');
            })->whereNull('providerLocationCities.deletedAt');

            $data->leftJoin('subLocations', function ($join) {
                $join->on('inventories.providerLocationId', '=', 'subLocations.id')->where('inventories.entityType', '=', 'subLocation');
            })->whereNull('subLocations.deletedAt');
        */

            if ($request->deviceType) {
                $data->where('deviceModels.deviceTypeId', $request->deviceType);
            }
            if ($request->search) {
                $data->where(function ($query) use ($request) {
                    $query->where('g1.name', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('inventories.macAddress', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('deviceModels.modelName', 'LIKE', '%' . $request->search . '%')
                        ->orWhere(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%")
                        ->orWhere(DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%")
                        ->orWhere(DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%")
                        ->orWhere(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%");
                });
            }
            /* if (request()->header('providerId')) {
                $provider = Helper::providerId();
                $data->where('inventories.providerId', $provider);
            }
            if (request()->header('providerLocationId')) {
                $providerLocation = Helper::providerLocationId();
                if (request()->header('entityType') == 'Country') {
                    $data->where([['inventories.providerLocationId', $providerLocation], ['inventories.entityType', 'Country']]);
                }
                if (request()->header('entityType') == 'State') {
                    $data->where([['inventories.providerLocationId', $providerLocation], ['inventories.entityType', 'State']]);
                }
                if (request()->header('entityType') == 'City') {
                    $data->where([['inventories.providerLocationId', $providerLocation], ['providerPrograms.entityType', 'City']]);
                }
                if (request()->header('entityType') == 'subLocation') {
                    $data->where([['inventories.providerLocationId', $providerLocation], ['inventories.entityType', 'subLocation']]);
                }
            }
            if (request()->header('programId')) {
                $program = Helper::programId();
                $entityType = Helper::entityType();
                $data->where([['inventories.programId', $program], ['inventories.entityType', $entityType]]);
            }*/
            if (empty($request->active)) {
                $data->where('inventories.isActive', 1);
            }
            if ($request->isAvailable) {
                $data->where('inventories.isActive', 1)->where('inventories.isAvailable', 1);
            }
            if ($request->orderField == 'deviceType') {
                $data->orderBy('g1.name', $request->orderBy);
            } elseif ($request->orderField == 'modelNumber') {
                $data->orderBy('deviceModels.modelName', $request->orderBy);
            } elseif ($request->orderField == 'serialNumber') {
                $data->orderBy('inventories.serialNumber', $request->orderBy);
            } elseif ($request->orderField == 'macAddress') {
                $data->orderBy('inventories.macAddress', $request->orderBy);
            } else {
                $data->orderBy('inventories.id', 'DESC');
            }
            $data = $data->select('inventories.*')->groupBy('inventories.id')->paginate(env('PER_PAGE', 20));
            if (!empty($data)) {
                return fractal()->collection($data)->transformWith(new InventoryListTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
            }
            return response()->json(['message' => trans('messages.inventoryUnAvailable')], 404);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Inventory
    public function update($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $inventory = array();
            if (!empty($request->input('providerId'))) {
                $inventory['providerId'] = $request->input('providerId');
            }
            if (!empty($request->input('macAddress'))) {
                $inventory['macAddress'] = $request->input('macAddress');
            }
            if (!empty($request->input('manufactureId'))) {
                $inventory['manufactureId'] = $request->input('manufactureId');
            }
            if (!empty($request->input('networkId'))) {
                $inventory['networkId'] = $request->input('networkId');
            }
            if (!empty($request->input('serialNumber'))) {
                $inventory['serialNumber'] = $request->input('serialNumber');
            }
            if (!empty($request->input('deviceTypeId'))) {
                $deviceModelId = DeviceModel::where(['deviceTypeId' => $request->deviceTypeId])->first();
                $inventory['deviceModelId'] = $deviceModelId->id;
            }
            if (empty($request->input('isActive'))) {
                $inventory['isActive'] = 0;
            } else {
                $inventory['isActive'] = 1;
            }
            $inventory['updatedBy'] = Auth::id();
            $inventory['providerId'] = $provider;
            $inventory['providerLocationId'] = $providerLocation;
            if (!empty($inventory)) {
                $inventoryObj = new Inventory();
                $inventoryObj->storeData($id, $inventory);
                if ($request->inventoryStatus) {
                    if (isset($request->isActive)) {
                        $isActive = $request->isActive;
                    }
                    $input = ["isActive" => $isActive];
                    $inventoryObj->storeData($id, $input);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'inventories', 'tableId' => $id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($inventory), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                }
            }
            $message = ['message' => trans('messages.updatedSuccesfully')];
            $newData = Inventory::where('id', $id)->first();
            $data = fractal()->item($newData)->transformWith(new InventoryListTransformer())->toArray();
            return array_merge($message, $data);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Inventory
    public function destroy($id)
    {
        try {
            $idx = Auth::id();
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $input = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            Inventory::where('id', $id)->update($input);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientGoals', 'tableId' => $id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            DB::select('CALL deleteInventory(' . $idx . ',' . $id . ')');
            return response()->json(['message' => trans('messages.deletedSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Device Model
    public function getModels($request)
    {
        try {
            $deviceType = $request->deviceType;
            $data = DB::select('CALL deviceModelList("' . $deviceType . '")');
            return fractal()->collection($data)->transformWith(new DeviceModelTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Get Inventory By Id
    public function geInventoryById($id)
    {
        try {
            if ($id) {
                $data = array();

                $newData = Inventory::with('model', 'inventory')->select('inventories.*')->where('inventories.id', $id);

                // $newData->leftJoin('providers', 'providers.id', '=', 'inventories.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
                // $newData->leftJoin('programs', 'programs.id', '=', 'inventories.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

                // $newData->leftJoin('providerLocations', function ($join) {
                //     $join->on('inventories.providerLocationId', '=', 'providerLocations.id')->where('inventories.entityType', '=', 'Country');
                // })->whereNull('providerLocations.deletedAt');

                // $newData->leftJoin('providerLocationStates', function ($join) {
                //     $join->on('inventories.providerLocationId', '=', 'providerLocationStates.id')->where('inventories.entityType', '=', 'State');
                // })->whereNull('providerLocationStates.deletedAt');

                // $newData->leftJoin('providerLocationCities', function ($join) {
                //     $join->on('inventories.providerLocationId', '=', 'providerLocationCities.id')->where('inventories.entityType', '=', 'City');
                // })->whereNull('providerLocationCities.deletedAt');

                // $newData->leftJoin('subLocations', function ($join) {
                //     $join->on('inventories.providerLocationId', '=', 'subLocations.id')->where('inventories.entityType', '=', 'subLocation');
                // })->whereNull('subLocations.deletedAt');

                // if (request()->header('providerId')) {
                //     $provider = Helper::providerId();
                //     $newData->where('inventories.providerId', $provider);
                // }
                // if (request()->header('providerLocationId')) {
                //     $providerLocation = Helper::providerLocationId();
                //     if (request()->header('entityType') == 'Country') {
                //         $newData->where([['inventories.providerLocationId', $providerLocation], ['inventories.entityType', 'Country']]);
                //     }
                //     if (request()->header('entityType') == 'State') {
                //         $newData->where([['inventories.providerLocationId', $providerLocation], ['inventories.entityType', 'State']]);
                //     }
                //     if (request()->header('entityType') == 'City') {
                //         $newData->where([['inventories.providerLocationId', $providerLocation], ['providerPrograms.entityType', 'City']]);
                //     }
                //     if (request()->header('entityType') == 'subLocation') {
                //         $newData->where([['inventories.providerLocationId', $providerLocation], ['inventories.entityType', 'subLocation']]);
                //     }
                // }
                // if (request()->header('programId')) {
                //     $program = Helper::programId();
                //     $entityType = Helper::entityType();
                //     $newData->where([['inventories.programId', $program], ['inventories.entityType', $entityType]]);
                // }
                $newData = $newData->first();
                if (!empty($newData)) {
                    $data = fractal()->item($newData)->transformWith(new InventoryListTransformer())->toArray();
                }
                return $data;
            }
            return response()->json(['message' => "id is Required"], 500);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function requestForUpdate($request)
    {
        $data = [];
        if ($request->macAddress) {
            $data['macAddress'] = trim($request->macAddress);
        }
        if ($request->manufactureId) {
            $data['manufactureId'] = $request->manufactureId;
        }
        if ($request->serialNumber) {
            $data['serialNumber'] = $request->serialNumber;
        }
        if ($request->networkId) {
            $data['networkId'] = $request->networkId;
        }
        if ($request->deviceModelId) {
            $data['deviceModelId'] = $request->manufactureId;
        }
        return $data;
    }

    public function manufactureGet($request)
    {
        try {
            $data = Inventory::where(['manufactureId' => $request->manufactureId,'deviceId'=>$request->deviceId])->get();
            return fractal()->collection($data)->transformWith(new InventorySerialTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
