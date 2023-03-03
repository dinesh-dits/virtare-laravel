<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use App\Models\BitrixField\BitrixField;
use App\Transformers\BitrixField\BitrixFieldTransformer;

class BitrixService
{
    // List Bitrix Field
    public function bitrixFiledGet($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $data = BitrixField::select('bitrixFields.*');

            //$data->leftJoin('providers', 'providers.id', '=', 'bitrixFields.providerId')->whereNull('providers.deletedAt');

            // $data->leftJoin('providers', 'providers.id', '=', 'bitrixFields.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'bitrixFields.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('bitrixFields.providerLocationId', '=', 'providerLocations.id')->where('bitrixFields.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('bitrixFields.providerLocationId', '=', 'providerLocationStates.id')->where('bitrixFields.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('bitrixFields.providerLocationId', '=', 'providerLocationCities.id')->where('bitrixFields.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('bitrixFields.providerLocationId', '=', 'subLocations.id')->where('bitrixFields.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('bitrixFields.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['bitrixFields.providerLocationId', $providerLocation], ['bitrixFields.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['bitrixFields.providerLocationId', $providerLocation], ['bitrixFields.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['bitrixFields.providerLocationId', $providerLocation], ['bitrixFields.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['bitrixFields.providerLocationId', $providerLocation], ['bitrixFields.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['bitrixFields.programId', $program], ['bitrixFields.entityType', $entityType]]);
            // }
            if ($id) {
                $data = $data->where("bitrixFields.id", $id)->where("bitrixFields.isDelete", "0")->first();
                if (!empty($data)) {
                    return fractal()->item($data)->transformWith(new BitrixFieldTransformer())->toArray();
                } else {
                    return response()->json(['message' => "Record not found."], 500);
                }
            } else {
                $data = $data->where("bitrixFields.isDelete", "0")->get();
                return fractal()->collection($data)->transformWith(new BitrixFieldTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Bitrix Field
    public function bitrixFieldCreate($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            if ($request->input('bitrixId')) {
                $bitrixId = $request->input('bitrixId');
            } else {
                return response()->json(['message' => "bitrixId is required."], 500);
            }
            if ($request->input('patientId')) {
                $patientId = $request->input('patientId');
            } else {
                return response()->json(['message' => "patientId is required."], 500);
            }
            $lastid = BitrixField::insertGetId([
                "bitrixId" => $bitrixId,
                "patientId" => $patientId,
                "isActive" => 1,
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'entityType' => $entityType,
            ]);
            if ($lastid) {
                $serviceData = BitrixField::where('id', $lastid)->first();
                $message = ['message' => trans('messages.createdSuccesfully')];
                $resp = fractal()->item($serviceData)->transformWith(new BitrixFieldTransformer())->toArray();
                $endData = array_merge($message, $resp);
                return $endData;
            } else {
                return response()->json(['message' => "bitrixField not inserted,Something wroung."], 500);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Bitrix Field
    public function bitrixFieldUpdate($request, $id)
    {
        try {
            if ($request->input('bitrixId')) {
                $bitrixData["bitrixId"] = $request->input('bitrixId');
            }
            if ($request->input('patientId')) {
                $bitrixData["patientId"] = $request->input('patientId');
            }
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $bitrixData["updatedBy"] = 1;
            $bitrixData["isActive"] = 1;
            $bitrixData["providerId"] = $provider;
            $bitrixData["providerLocationId"] = $providerLocation;
            BitrixField::where("id", $id)->update($bitrixData);
            $bitrixFieldData = BitrixField::where('id', $id)->first();
            if (!empty($bitrixFieldData)) {
                $message = ['message' => trans('messages.updatedSuccesfully')];
                $resp = fractal()->item($bitrixFieldData)->transformWith(new BitrixFieldTransformer())->toArray();
                $endData = array_merge($message, $resp);
                return $endData;
            } else {
                return response()->json(['message' => "Record not found."], 500);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Bitrix Field
    public function bitrixFieldDelete($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $input = [
                'deletedBy' => 1,
                'isActive' => 0,
                'isDelete' => 1,
                'providerId' => $provider,
                'providerLocationId' => $providerLocation
            ];
            $bitrixFieldData = BitrixField::where('id', $id)->first();
            if (!empty($bitrixFieldData)) {
                BitrixField::where('id', $id)->update($input);
                return response()->json(['message' => trans('messages.deletedSuccesfully')], 200);
            } else {
                return response()->json(['message' => "Record not found."], 500);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
