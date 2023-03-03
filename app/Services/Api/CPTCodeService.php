<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\CPTCode\CPTCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\CPTCode\CptCodeActivity;
use App\Models\Communication\CallRecord;
use App\Models\CPTCode\CallDurationBilling;
use App\Models\CPTCode\CptCodeServiceDetail;
use App\Transformers\CPTCode\CPTCodeTransformer;
use App\Models\CPTCode\CptCodeNextBillingServices;
use App\Transformers\CPTCode\CPTCodeServiceTransformer;
use App\Transformers\CPTCode\CptCodeActivityTransformer;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Models\CPTCode\CptCodeService as CPTCodeCptCodeService;
use App\Transformers\CPTCode\CPTCodeNextBillingDetailTransformer;

class CPTCodeService
{
    // List CPT Code
    public function listCPTCode($request, $id)
    {
        try {
            $data = CPTCode::select('cptCodes.*')->with('provider', 'service', 'duration');

            // $data->leftJoin('providers', 'providers.id', '=', 'cptCodes.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'cptCodes.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('cptCodes.providerLocationId', '=', 'providerLocations.id')->where('cptCodes.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('cptCodes.providerLocationId', '=', 'providerLocationStates.id')->where('cptCodes.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('cptCodes.providerLocationId', '=', 'providerLocationCities.id')->where('cptCodes.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('cptCodes.providerLocationId', '=', 'subLocations.id')->where('cptCodes.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('cptCodes.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['cptCodes.providerLocationId', $providerLocation], ['cptCodes.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['cptCodes.providerLocationId', $providerLocation], ['cptCodes.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['cptCodes.providerLocationId', $providerLocation], ['cptCodes.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['cptCodes.providerLocationId', $providerLocation], ['cptCodes.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['cptCodes.programId', $program], ['cptCodes.entityType', $entityType]]);
            // }
            if (!empty($id)) {
                $data = $data->where("cptCodes.udid", $id)->orderBy('cptCodes.createdAt', 'DESC')->first();
                return fractal()->item($data)->transformWith(new CPTCodeTransformer())->toArray();
            } else {
                // if ($request->all) {
                //     if ($request->active) {
                //         $data = CPTCode::with('provider', 'service', 'duration')->orderBy('createdAt', 'DESC')->get();
                //     } else {
                //         $data = CPTCode::where('isActive', 1)->with('provider', 'service', 'duration')->orderBy('createdAt', 'DESC')->get();
                //     }
                //     return fractal()->collection($data)->transformWith(new CPTCodeTransformer())->toArray();
                // } else {
                if ($request->active) {
                    $data->where('cptCodes.isActive', $request->active);
                }
                if ($request->filter) {
                    $data->where('cptCodes.name', $request->filter);
                }
                if ($request->search) {
                    $data->where('cptCodes.name', 'LIKE', '%' . $request->search . '%');
                    $data->orwhere('cptCodes.description', 'LIKE', '%' . $request->search . '%');
                }
                if ($request->orderBy && $request->orderField) {
                    $data->orderBy($request->orderField, $request->orderBy);
                }
                $data = $data->paginate(env('PER_PAGE', 20));
                return fractal()->collection($data)->transformWith(new CPTCodeTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
                // }
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add CPT Code
    public function createCPTCode($request)
    {
        try {
            $udid = $request->input('serviceId');
            $service = Helper::tableName('App\Models\CPTCode\Service', $udid);
            $udid = Str::uuid()->toString();
            $serviceId = $service;
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $name = $request->input('name');
            $billingAmout = $request->input('billingAmout');
            $description = $request->input('description');
            $durationId = $request->input('durationId');
            DB::select('CALL createCPTCode("' . $udid . '","' . $serviceId . '","' . $providerId . '","' . $providerLocation . '","' . $entityType . '","' . $name . '","' . $billingAmout . '","' . $description . '","' . $durationId . '")');
            return response()->json(['message' => trans('messages.createdSuccesfully')], 200);
            // $cptCodeData = CPTCode::where('udid', $udid)->first();
            // dd($cptCodeData);
            // $message = ['message' => trans('messages.createdSuccesfully')];
            // $resp =  fractal()->item($cptCodeData)->transformWith(new CPTCodeTransformer())->toArray();
            // $endData = array_merge($message, $resp);
            // return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update CPT Code
    public function updateCPTCode($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $CPTCode = array();
            if (!empty($request->input('serviceId'))) {
                $udid = $request->input('serviceId');
                $service = Helper::tableName('App\Models\CPTCode\Service', $udid);
                $CPTCode['serviceId'] = $service;
            }
            if (!empty($request->input('providerId'))) {
                $CPTCode['providerId'] = $request->input('providerId');
            }
            if (!empty($request->input('name'))) {
                $CPTCode['name'] = $request->input('name');
            }
            if (!empty($request->input('billingAmout'))) {
                $CPTCode['billingAmout'] = $request->input('billingAmout');
            }
            if (!empty($request->input('description'))) {
                $CPTCode['description'] = $request->input('description');
            }
            if (!empty($request->input('durationId'))) {
                $CPTCode['durationId'] = $request->input('durationId');
            }
            if (empty($request->input('isActive'))) {
                $CPTCode['isActive'] = 0;
            } else {
                $CPTCode['isActive'] = 1;
            }
            $CPTCode['updatedBy'] = Auth::id();
            $CPTCode['providerId'] = $provider;
            $CPTCode['providerLocationId'] = $providerLocation;
            if (!empty($CPTCode)) {
                CPTCode::where('udid', $id)->update($CPTCode);
                $cpt = Helper::tableName('App\Models\CPTCode\CPTCode', $id);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'cptCodes', 'tableId' => $cpt, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($CPTCode), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
            }
            $cptCodeData = CPTCode::where('udid', $id)->first();
            $message = ['message' => trans('messages.updatedSuccesfully')];
            $resp = fractal()->item($cptCodeData)->transformWith(new CPTCodeTransformer())->toArray();
            $endData = array_merge($message, $resp);
            return $endData;
            // $serviceId = $request->input('serviceId');
            // $providerId = $request->input('providerId');
            // $name = $request->input('name');
            // $billingAmout = $request->input('billingAmout');
            // $description = $request->input('description');
            // $durationId = $request->input('durationId');
            // $updatedBy = 1;
            // $isActive = 1;
            // DB::select('CALL updateCPTCode("' . $id . '","' . $serviceId . '","' . $providerId . '","' . $name . '","' . $billingAmout . '","' . $description . '","' . $durationId . '","' . $updatedBy . '","' . $isActive . '")');
            // $cptCodeData = CPTCode::where('id', $id)->first();
            // $message = ['message' => trans('messages.updatedSuccesfully')];
            // $resp =  fractal()->item($cptCodeData)->transformWith(new CPTCodeTransformer())->toArray();
            // $endData = array_merge($message, $resp);
            // return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update CPT Code Status
    public function updateCPTCodeStatus($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $isActive = $request->input('isActive') == true ? 1 : 0;
            $data = ["isActive" => $isActive, 'providerId' => $provider, 'updatedBy' => Auth::id(), 'providerLocationId' => $providerLocation];
            CPTCode::where("id", $id)->update($data);
            $cptCodeData = CPTCode::where('id', $id)->first();
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'cptCodes', 'tableId' => $cptCodeData->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($data), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);
            $message = ['message' => trans('messages.updatedSuccesfully')];
            $resp = fractal()->item($cptCodeData)->transformWith(new CPTCodeTransformer())->toArray();
            $endData = array_merge($message, $resp);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete CPT Code
    public function deleteCPTCode($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $CPTCode = CPTCode::where('udid', $id)->first();
            $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'updatedBy' => Auth::id()];
            CPTCode::where('udid', $id)->update($input);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'cptCodes', 'tableId' => $CPTCode->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);

            CPTCode::where('udid', $id)->delete();
            return response()->json(['message' => trans('messages.deletedSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List CPT Code Service
    public function cptCodeList($request)
    {
        try {
            $data = CPTCodeCptCodeService::select('cptCodeServices.*')->with('cptCodeActivity', 'patient', 'service', 'cptCodeStatus', 'placesOfService', 'cptCodeServiceCondition')
                ->leftJoin('patients as p', 'p.id', '=', 'cptCodeServices.patientId')
                ->leftJoin('cptCodeActivities as c', 'c.id', '=', 'cptCodeServices.cptCodeActivityId')
                ->leftJoin('cptCodes as b', 'b.id', '=', 'c.cptCodeId')
                ->leftJoin('globalCodes as g1', 'g1.id', '=', 'cptCodeServices.status')
                ->leftJoin('globalCodes as g2', 'g2.id', '=', 'cptCodeServices.placeOfService');

            // $data->leftJoin('providers', 'providers.id', '=', 'cptCodeServices.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'cptCodeServices.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('cptCodeServices.providerLocationId', '=', 'providerLocations.id')->where('cptCodeServices.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('cptCodeServices.providerLocationId', '=', 'providerLocationStates.id')->where('cptCodeServices.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('cptCodeServices.providerLocationId', '=', 'providerLocationCities.id')->where('cptCodeServices.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('cptCodeServices.providerLocationId', '=', 'subLocations.id')->where('cptCodeServices.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');


            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('cptCodeServices.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['cptCodeServices.providerLocationId', $providerLocation], ['cptCodeServices.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['cptCodeServices.providerLocationId', $providerLocation], ['cptCodeServices.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['cptCodeServices.providerLocationId', $providerLocation], ['cptCodeServices.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['cptCodeServices.providerLocationId', $providerLocation], ['cptCodeServices.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['cptCodeServices.programId', $program], ['cptCodeServices.entityType', $entityType]]);
            // }
            if ($request->patientId) {
                $data->where('p.udid', $request->patientId);
            }
            if ($request->search) {
                $data->where(function ($query) use ($request) {
                    $query->where('p.lastName', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('c.name', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('b.name', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('g1.name', 'LIKE', '%' . $request->search . '%');
                });
            }
            if ($request->filter) {
                $data->where(function ($query) use ($request) {
                    $query->where('c.name', $request->filter)
                        ->orWhere('g1.name', $request->filter);
                });
            }
            if ($request->fromDate && $request->toDate) {
                $fromDate = $request->fromDate . " 00:00:00";
                $toDate = $request->toDate . " 23:59:59";
                $data->whereBetween('cptCodeServices.createdAt', [$fromDate, $toDate]);
            }
            if ($request->orderField == 'patient') {
                $data->orderBy('p.lastName', $request->orderBy);
            } elseif ($request->orderField == 'billingDate') {
                $data->orderBy('cptCodeServices.createdAt', $request->orderBy);
            } elseif ($request->orderField == 'typeOfService') {
                $data->orderBy('c.name', $request->orderBy);
            } elseif ($request->orderField == 'cptCode') {
                $data->orderBy('b.name', $request->orderBy);
            } else {
                $data->orderBy('cptCodeServices.createdAt', 'ASC');
            }
            $data = $data->paginate(env('PER_PAGE', 20));
            return fractal()->collection($data)->transformWith(new CPTCodeServiceTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // CPT Code Service By Id
    public function cptCodeListDetail($request, $id)
    {
        try {
            $data = CPTCodeCptCodeService::select('cptCodeServices.*')->with('cptCodeActivity', 'patient', 'service', 'cptCodeStatus', 'placesOfService', 'cptCodeServiceCondition')->where('cptCodeServices.id', $id);

            // $data->leftJoin('providers', 'providers.id', '=', 'cptCodeServices.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'cptCodeServices.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('cptCodeServices.providerLocationId', '=', 'providerLocations.id')->where('cptCodeServices.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('cptCodeServices.providerLocationId', '=', 'providerLocationStates.id')->where('cptCodeServices.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('cptCodeServices.providerLocationId', '=', 'providerLocationCities.id')->where('cptCodeServices.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('cptCodeServices.providerLocationId', '=', 'subLocations.id')->where('cptCodeServices.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');


            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('cptCodeServices.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['cptCodeServices.providerLocationId', $providerLocation], ['cptCodeServices.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['cptCodeServices.providerLocationId', $providerLocation], ['cptCodeServices.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['cptCodeServices.providerLocationId', $providerLocation], ['cptCodeServices.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['cptCodeServices.providerLocationId', $providerLocation], ['cptCodeServices.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['cptCodeServices.programId', $program], ['cptCodeServices.entityType', $entityType]]);
            // }
            $data = $data->first();
            return fractal()->item($data)->transformWith(new CPTCodeServiceTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update CPT Code Service Update
    public function cptCodeStatusUpdate($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $CPTCodeIds = $request->CPTCodeId;
            if (!empty($CPTCodeIds && $request->status)) {
                foreach ($CPTCodeIds as $CPTCodeId) {
                    $data = [
                        'status' => $request->status,
                        'updatedBy' => Auth::id(),
                        'providerId' => $provider,
                        'providerLocationId' => $providerLocation
                    ];
                    CPTCodeCptCodeService::where('id', $CPTCodeId)->update($data);
                }
                return response()->json(['message' => trans('messages.updatedSuccesfully')]);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // CPT Code Next Billing Details
    public function getNextBillingDetail($request, $id)
    {
        try {
            $cptCode = CPTCodeCptCodeService::where("id", $id)->first();
            if (isset($cptCode->id)) {
                $data = CPTCodeCptCodeService::select('cptCodeServices.*')->with('getCptCodeActivity')
                    ->where("cptCodeServices.patientId", $cptCode->patientId)
                    ->orderBy("cptCodeActivityId", "DESC");

                // $data->leftJoin('providers', 'providers.id', '=', 'cptCodeServices.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
                // $data->leftJoin('programs', 'programs.id', '=', 'cptCodeServices.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');
                // $data->leftJoin('providerLocations', function ($join) {
                //     $join->on('cptCodeServices.providerLocationId', '=', 'providerLocations.id')->where('cptCodeServices.entityType', '=', 'Country');
                // })->whereNull('providerLocations.deletedAt');

                // $data->leftJoin('providerLocationStates', function ($join) {
                //     $join->on('cptCodeServices.providerLocationId', '=', 'providerLocationStates.id')->where('cptCodeServices.entityType', '=', 'State');
                // })->whereNull('providerLocationStates.deletedAt');

                // $data->leftJoin('providerLocationCities', function ($join) {
                //     $join->on('cptCodeServices.providerLocationId', '=', 'providerLocationCities.id')->where('cptCodeServices.entityType', '=', 'City');
                // })->whereNull('providerLocationCities.deletedAt');

                // $data->leftJoin('subLocations', function ($join) {
                //     $join->on('cptCodeServices.providerLocationId', '=', 'subLocations.id')->where('cptCodeServices.entityType', '=', 'subLocation');
                // })->whereNull('subLocations.deletedAt');

                // if (request()->header('providerId')) {
                //     $provider = Helper::providerId();
                //     $data->where('cptCodeServices.providerId', $provider);
                // }
                // if (request()->header('providerLocationId')) {
                //     $providerLocation = Helper::providerLocationId();
                //     if (request()->header('entityType') == 'Country') {
                //         $data->where([['cptCodeServices.providerLocationId', $providerLocation], ['cptCodeServices.entityType', 'Country']]);
                //     }
                //     if (request()->header('entityType') == 'State') {
                //         $data->where([['cptCodeServices.providerLocationId', $providerLocation], ['cptCodeServices.entityType', 'State']]);
                //     }
                //     if (request()->header('entityType') == 'City') {
                //         $data->where([['cptCodeServices.providerLocationId', $providerLocation], ['cptCodeServices.entityType', 'City']]);
                //     }
                //     if (request()->header('entityType') == 'subLocation') {
                //         $data->where([['cptCodeServices.providerLocationId', $providerLocation], ['cptCodeServices.entityType', 'subLocation']]);
                //     }
                // }
                // if (request()->header('programId')) {
                //     $program = Helper::programId();
                //     $entityType = Helper::entityType();
                //     $data->where([['cptCodeServices.programId', $program], ['cptCodeServices.entityType', $entityType]]);
                // }
                $data = $data->first();
                $data["cptCode"] = $cptCode;
                // if(!$data){
                //     $data =  CPTCodeCptCodeService::where("id",$id)->first();
                // }
                if (!empty($data)) {
                    return fractal()->item($data)->transformWith(new CPTCodeNextBillingDetailTransformer())->toArray();
                } else {
                    $data["data"] = [];
                    return $data;
                }
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add CPT Code Billing Serices
    public static function processNextBillingDetail($request, $id = "")
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $result = "";
            $result = CPTCodeCptCodeService::with('cptCodeActivity')
                ->select(DB::raw("MAX(id) as cptCodeServiceId, patientId "))
                ->where("isQueue", 0)
                ->groupBy("patientId")
                ->orderBy("id", "DESC")
                ->get();
            $ids = array();
            if ($result) {
                foreach ($result as $v) {
                    $ids[] = $v->cptCodeServiceId;
                }
            }
            $data = CPTCodeCptCodeService::with('cptCodeActivity')
                ->whereIn("id", $ids)
                ->orderBy("id", "DESC")
                ->get()->toArray();
            if (!empty($data)) {
                $insertArr = array();
                foreach ($data as $val) {
                    $lastDate = $val["createdAt"];
                    $nextDate = strtotime($val["createdAt"] . ' + 30 days');
                    $nextDate = Helper::date($nextDate);
                    $jsonData = json_encode($val);
                    $insertArr = array(
                        "referenceId" => $val["id"],
                        "entityType" => "cptCodeServices",
                        "functionName" => "processNextBillingDetail",
                        "controllerName" => "CPTCodeController",
                        "lastBillingAt" => $lastDate,
                        "nextBillingAt" => $nextDate,
                        "providerId" => $provider,
                        'providerLocationId' => $providerLocation
                        // "jsonData" => $jsonData,
                    );
                    CptCodeNextBillingServices::insert($insertArr);

                    // change queue status 0 to 1 for billed CPT code.
                    CPTCodeCptCodeService::where("patientId", $val["patientId"])
                        ->update(["isQueue" => 1, 'providerId' => $provider]);

                }
                return response()->json(['message' => trans('messages.createdSuccesfully')], 200);
            } else {
                return response()->json(['message' => "No CPT Billing Service Found."], 400);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add CPT Code Billing Serices Detail
    public static function insertNextBillingServiceDetail($request, $id = "")
    {
        try {
            $providerId = Helper::providerId();
            $cptBilling = array();
            $fromDate = Carbon::today();
            $cptBilling = CptCodeNextBillingServices::where("isQueue", 0)
                ->whereDate('nextBillingAt', $fromDate)
                ->get()->toArray();
            if (!empty($cptBilling)) {
                foreach ($cptBilling as $val) {
                    $cptService = CPTCodeCptCodeService::where("id", $val["referenceId"])->first();
                    if ((@$cptService->entity && $cptService->entity == 'vital') || $cptService->entity == 'device') {
                        $cptActivity = CptCodeActivity::with("cptCode")->where('id', 2)->first();
                        CPTCodeService::insertNextBillingServiceDetailForDevice($cptService, $val, $cptActivity, $providerId, $fromDate);
                    }
                }
                return response()->json(['message' => trans('messages.createdSuccesfully')], 200);
            } else {
                return response()->json(['message' => "No CPT Billing Service Found."], 400);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function insertNextBillingServiceDetailForDevice($cpt, $val, $cptActivity, $providerId, $fromDate)
    {
        try {
            $vitalData = array();
            if (isset($cpt->patientId)) {
                $vitalData = DB::select(
                    "CALL getVitalByMonth('" . $cpt->patientId . "','" . $fromDate . "')",
                );

                // 16 days of Compliance reading out of 30 days to be able to bill to insurance
                if (empty($vitalData) && count($vitalData) > 16) {
                    $cost = 0;
                    if (isset($cptActivity->cptCode->billingAmout)) {
                        $cost = $cptActivity->cptCode->billingAmout;
                    } else {
                        $cost = "84.48";
                    }
                    $cptData = [
                        'udid' => Str::uuid()->toString(), 'cptCodeActivityId' => 2, 'patientId' => $cpt->patientId, 'entity' => 'vital',
                        'referenceId' => $cpt->referenceId, 'units' => 1, 'cost' => $cost, 'providerId' => $providerId,
                        'status' => 1, 'placeOfService' => $cpt->placeOfService
                    ];
                    $cptInsertService = CPTCodeCptCodeService::create($cptData);
                    CptCodeNextBillingServices::where("id", $val["id"])->update(["isQueue", 1]);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'cptCodeServices', 'tableId' => $cptInsertService->id,
                        'value' => json_encode($cptData), 'type' => 'created', 'ip' => request()->ip(), 'providerId' => $providerId
                    ];
                    ChangeLog::create($changeLog);
                    if (isset($cptActivity->billingInterval)) {
                        $billingInterval = $cptActivity->billingInterval;
                    } else {
                        $billingInterval = 30;
                    }
                    $lastDate = $cptInsertService->createdAt;
                    $nextDate = strtotime($cptInsertService->createdAt . ' + ' . $billingInterval . ' days');
                    $nextDate = Helper::date($nextDate);
                    $insertArr = array(
                        "referenceId" => $cptInsertService->id,
                        "entityType" => "cptCodeServices",
                        "functionName" => "inventory",
                        "controllerName" => "PatientController",
                        "lastBillingAt" => $lastDate,
                        "nextBillingAt" => $nextDate,
                        'providerId' => $providerId
                    );
                    $cptBilling = CptCodeNextBillingServices::create($insertArr);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'cptCodeNextBillingServices', 'tableId' => $cptBilling->id,
                        'value' => json_encode($insertArr), 'type' => 'created', 'ip' => request()->ip(), 'providerId' => $providerId
                    ];
                    ChangeLog::create($changeLog);
                }
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function insertNextBillingServiceDetailForCall($cpt, $val, $cptActivity, $providerId, $fromDate)
    {
        try {
            $vitalData = array();
            if (isset($cpt->patientId)) {

                /**
                 * In order to bill this code minimum of 20 minutes of interactive communication after the first 20 minutes
                 * This CPT can be billed for maximum of 4 units in a month (2+2) with an interval of 15 days
                 */
                $callRecordingData = DB::select(
                    "CALL getCallRecordCountByHalfMonth('" . $cpt->patientId . "','" . $fromDate . "')",
                );

                if (isset($callRecordingData[0]->timeCall) && $callRecordingData[0]->timeCall > 20) {
                    $cpt = CPTCodeCptCodeService::where("id", $val["referenceId"])->first();
                    $cptData = [
                        'udid' => Str::uuid()->toString(), 'cptCodeActivityId' => 1, 'patientId' => $cpt->patientId, 'entity' => 'vital',
                        'referenceId' => $cpt->referenceId, 'units' => 1, 'cost' => $cpt->cost, 'providerId' => $providerId,
                        'status' => 1, 'placeOfService' => $cpt->placeOfService
                    ];

                    $cptInsertService = CPTCodeCptCodeService::create($cptData);
                    CptCodeNextBillingServices::where("id", $val["id"])->update(["isQueue", 1]);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'cptCodeServices', 'tableId' => $cptInsertService->id,
                        'value' => json_encode($cptData), 'type' => 'created', 'ip' => request()->ip(), 'providerId' => $providerId
                    ];
                    ChangeLog::create($changeLog);
                    if (isset($cptActivity->billingInterval)) {
                        $billingInterval = $cptActivity->billingInterval;
                    } else {
                        $billingInterval = 30;
                    }
                    $lastDate = $cptInsertService->createdAt;
                    $nextDate = strtotime($cptInsertService->createdAt . ' + ' . $billingInterval . ' days');
                    $nextDate = Helper::date($nextDate);
                    $insertArr = array(
                        "referenceId" => $cptInsertService->id,
                        "entityType" => "cptCodeServices",
                        "functionName" => "inventory",
                        "controllerName" => "PatientController",
                        "lastBillingAt" => $lastDate,
                        "nextBillingAt" => $nextDate,
                        'providerId' => $providerId
                    );
                    $cptBilling = CptCodeNextBillingServices::create($insertArr);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'cptCodeNextBillingServices', 'tableId' => $cptBilling->id,
                        'value' => json_encode($insertArr), 'type' => 'created', 'ip' => request()->ip(), 'providerId' => $providerId
                    ];
                    ChangeLog::create($changeLog);
                }
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function insertNextBillingServiceDetailForCallOLd($cptService, $val, $cptActivity, $providerId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $serviceId = CptCodeServiceDetail::where('cptCodeServicesId', $cptService->id)->selectRaw('group_concat(serviceId) as serviceId')->first();
            $serviceId = explode(",", $serviceId['serviceId']);

            $call = CallRecord::with("communicationCallRecord")
                ->select('callRecords.*', 'callRecordTimes.startTime', 'callRecordTimes.endTime', 'cptCodeServiceTimeSpands.timeSpent')
                ->whereIn('communicationCallRecordId', $serviceId)->with('staff')->Join('callRecordTimes', 'callRecordTimes.callRecordId', '=', 'callRecords.id')
                ->Join('cptCodeServiceTimeSpands', 'cptCodeServiceTimeSpands.refrenceId', '=', 'callRecordTimes.id')->withTrashed();
            if ($provider) {
                $call->where('providerId', $provider);
            }
            if ($providerLocation) {
                $call->where('providerLocationId', $providerLocation);
            }
            $call = $call->get();
            if ($call) {
                foreach ($call as $callRecord) {
                    $datetime_1 = $callRecord->startTime;
                    $datetime_2 = $callRecord->endTime;

                    $from_time = strtotime($datetime_1);
                    $to_time = strtotime($datetime_2);
                    $diff_minutes = round(abs($from_time - $to_time) / 60, 2);
                    $currentDate = Carbon::now();
                    $createdAt = $callRecord->createdAt;
                    $dayDIff = $this->dateDiffInDays($currentDate, $createdAt);
                    if ($diff_minutes > 20 && $dayDIff > 15) {
                        $cpt = CPTCodeCptCodeService::where("id", $val["referenceId"])->first();
                        $cptData = [
                            'udid' => Str::uuid()->toString(), 'cptCodeActivityId' => 1, 'patientId' => $cpt->patientId, 'entity' => 'vital',
                            'referenceId' => $cpt->referenceId, 'units' => 1, 'cost' => $cpt->cost, 'providerId' => $provider,
                            'status' => 1, 'placeOfService' => $cpt->placeOfService
                        ];

                        $cptInsertService = CPTCodeCptCodeService::create($cptData);
                        CptCodeNextBillingServices::where("id", $val["id"])->update(["isQueue", 1]);
                        $changeLog = [
                            'udid' => Str::uuid()->toString(), 'table' => 'cptCodeServices', 'tableId' => $cptInsertService->id, 'providerId' => $provider,
                            'value' => json_encode($cptData), 'type' => 'created', 'ip' => request()->ip(), 'providerId' => $providerId
                        ];
                        ChangeLog::create($changeLog);
                        if (isset($cptActivity->billingInterval)) {
                            $billingInterval = $cptActivity->billingInterval;
                        } else {
                            $billingInterval = 30;
                        }
                        $lastDate = $cptInsertService->createdAt;
                        $nextDate = strtotime($cptInsertService->createdAt . ' + ' . $billingInterval . ' days');
                        $nextDate = Helper::date($nextDate);
                        $insertArr = array(
                            "referenceId" => $cptInsertService->id,
                            "entityType" => "cptCodeServices",
                            "functionName" => "inventory",
                            "controllerName" => "PatientController",
                            "lastBillingAt" => $lastDate,
                            "nextBillingAt" => $nextDate,
                            'providerId' => $provider
                        );
                        $cptBilling = CptCodeNextBillingServices::create($insertArr);
                        $changeLog = [
                            'udid' => Str::uuid()->toString(), 'table' => 'cptCodeNextBillingServices', 'tableId' => $cptBilling->id, 'providerId' => $provider,
                            'value' => json_encode($insertArr), 'type' => 'created', 'ip' => request()->ip(), 'providerId' => $providerId
                        ];
                        ChangeLog::create($changeLog);
                    }
                }
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function dateDiffInDays($date1, $date2)
    {
        try {
            // Calculating the difference in timestamps
            $diff = strtotime($date2) - strtotime($date1);

            // 1 day = 24 hours
            // 24 * 60 * 60 = 86400 seconds
            return abs(round($diff / 86400));
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List CPT Code Activity
    public function cptCodeActivity($request)
    {
        try {
            $data = CptCodeActivity::select('cptCodeActivities.*')->with('cptCode')
                ->leftJoin('cptCodes', 'cptCodes.id', '=', 'cptCodeActivities.cptCodeId');
            if ($request->search) {
                $data->where(function ($query) use ($request) {
                    $query->where('cptCodes.name', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('cptCodeActivities.name', 'LIKE', '%' . $request->search . '%');
                });
            }

         /*   $data->leftJoin('providers', 'providers.id', '=', 'cptCodeActivities.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            $data->leftJoin('programs', 'programs.id', '=', 'cptCodeActivities.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            $data->leftJoin('providerLocations', function ($join) {
                $join->on('cptCodeActivities.providerLocationId', '=', 'providerLocations.id')->where('cptCodeActivities.entityType', '=', 'Country');
            })->whereNull('providerLocations.deletedAt');

            $data->leftJoin('providerLocationStates', function ($join) {
                $join->on('cptCodeActivities.providerLocationId', '=', 'providerLocationStates.id')->where('cptCodeActivities.entityType', '=', 'State');
            })->whereNull('providerLocationStates.deletedAt');

            $data->leftJoin('providerLocationCities', function ($join) {
                $join->on('cptCodeActivities.providerLocationId', '=', 'providerLocationCities.id')->where('cptCodeActivities.entityType', '=', 'City');
            })->whereNull('providerLocationCities.deletedAt');

            $data->leftJoin('subLocations', function ($join) {
                $join->on('cptCodeActivities.providerLocationId', '=', 'subLocations.id')->where('cptCodeActivities.entityType', '=', 'subLocation');
            })->whereNull('subLocations.deletedAt');

            if (request()->header('providerId')) {
                $provider = Helper::providerId();
                $data->where('cptCodeActivities.providerId', $provider);
            }
            if (request()->header('providerLocationId')) {
                $providerLocation = Helper::providerLocationId();
                if (request()->header('entityType') == 'Country') {
                    $data->where([['cptCodeActivities.providerLocationId', $providerLocation], ['cptCodeActivities.entityType', 'Country']]);
                }
                if (request()->header('entityType') == 'State') {
                    $data->where([['cptCodeActivities.providerLocationId', $providerLocation], ['cptCodeActivities.entityType', 'State']]);
                }
                if (request()->header('entityType') == 'City') {
                    $data->where([['cptCodeActivities.providerLocationId', $providerLocation], ['cptCodeActivities.entityType', 'City']]);
                }
                if (request()->header('entityType') == 'subLocation') {
                    $data->where([['cptCodeActivities.providerLocationId', $providerLocation], ['cptCodeActivities.entityType', 'subLocation']]);
                }
            }
            if (request()->header('programId')) {
                $program = Helper::programId();
                $entityType = Helper::entityType();
                $data->where([['cptCodeActivities.programId', $program], ['cptCodeActivities.entityType', $entityType]]);
            }*/
            if ($request->orderField == 'name') {
                $data->orderBy('cptCodeActivities.name', $request->orderBy);
            } elseif ($request->orderField == 'billingIntervalType') {
                $data->orderBy('cptCodeActivities.billingIntervalType', $request->orderBy);
            } elseif ($request->orderField == 'cptCode') {
                $data->orderBy('cptCodes.name', $request->orderBy);
            } elseif ($request->orderField == 'billingAmout') {
                $data->orderBy('cptCodes.billingAmout', $request->orderBy);
            } else {
                $data->orderBy('cptCodeActivities.priority', 'ASC');
            }
            $data = $data->select('cptCodeActivities.*')->paginate(env('PER_PAGE', 20));
            return fractal()->collection($data)->transformWith(new CptCodeActivityTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function cptNextBillingForCall(){
        $now = date('Y-m-d');
        $fromDate = date('Y-m-d', strtotime('-15 days'));
        $patientIdx = "";
        $unit = 0;
        $cptActivityId = 0;
        $cptBillingNextObj = CptCodeNextBillingServices::where("entityType","patient")
                        ->where("nextBillingAt",'LIKE',"%".$now."%")
                        ->where("isActive",1)
                        ->where("isQueue",0)
                        ->get();
        if(!empty($cptBillingNextObj) && count($cptBillingNextObj->toArray()) > 0){
            foreach ($cptBillingNextObj as $cptBillingNext) {
                if(isset($cptBillingNext->referenceId)){
                    $callDuObj = CallDurationBilling::where("patientId",$cptBillingNext->referenceId)
                    ->orderBy("id","DESC")->first();

                    if(!isset($callDuObj->id)){
                        if($cptBillingNext->referenceId){
                            $patientIdx = $cptBillingNext->referenceId;
                            $cptCodeId = $cptBillingNext->cptCodeId;
                            $providerId = $cptBillingNext->providerId;
                            echo $now." to ".$fromDate,' / '.$patientIdx;
                            $callRecordingData = DB::select(
                                "CALL getCallRecordCountByHalfMonth('" . $patientIdx . "','" . $fromDate . "')",
                            );
                            $duration = 0;
                            $remaining = 0;
                            $insertCallDurationObj  =  [];
                            if(count($callRecordingData) > 0) {
                                foreach($callRecordingData as $callRecording){
                                    if(isset($callRecording->timeCall) && $callRecording->timeCall >= 20){
                                        $cptActivityId = 3;
                                        $unit = 1;
                                        $charge = 20;
                                        $duration = $callRecording->timeCall;
                                        $remaining = $callRecording->timeCall - $charge;
                                    }else{
                                        $cptActivityId = 3;
                                        $unit = 0;
                                        $charge = 0;
                                        $duration = $callRecording->timeCall;
                                        $remaining = $callRecording->timeCall;
                                    }
                                    $insertCallDurationObj = [
                                        "udid" => Str::uuid()->toString(),
                                        "duration" => $duration,
                                        "remaining" => $remaining,
                                        "charge" => $charge,
                                        "patientId" => $callRecording->patientId,
                                    ];
                                    if(count($insertCallDurationObj) > 0){
                                        $this->generateNextBilling($cptActivityId,$unit,$charge,$patientIdx,$providerId,$cptCodeId,$insertCallDurationObj,$cptBillingNext);
                                    }
                                }
                            }else{
                                die("No call record found on getCallRecordCountByHalfMonth function.");
                            }
                        }
                    }else{
                        $totalTime = 0;
                        if(isset($callDuObj->id)){
                            if($cptBillingNext->referenceId){
                                $patientIdx = $cptBillingNext->referenceId;
                                $cptCodeId = $cptBillingNext->cptCodeId;
                                $providerId = $cptBillingNext->providerId;
                                echo $now." to ".$fromDate,' / '.$patientIdx;
                                $callRecordingData = DB::select(
                                    "CALL getCallRecordCountByHalfMonth('" . $patientIdx . "','" . $fromDate . "')",
                                );
                                $duration = 0;
                                $remaining = 0;
                                $insertCallDurationObj  =  [];
                                foreach($callRecordingData as $callRecording){
                                    $totalTime = $callRecording->timeCall + $callDuObj->remaining;
                                    if($totalTime >= 20 && $totalTime < 40){
                                        $cptActivityId = 3;
                                        $unit = 1;
                                        $charge = 20;
                                        $duration = $totalTime;
                                        $remaining = $duration - $charge;
                                    }elseif($totalTime >= 40){
                                        $cptActivityId = 4;
                                        $unit = 2;
                                        $charge = 40;
                                        $duration = $totalTime;
                                        $remaining = $totalTime - $charge;
                                    }else{
                                        if($totalTime >= 20){
                                            $cptActivityId = 3;
                                            $unit = 1;
                                            $charge = 20;
                                            $duration = $totalTime;
                                            $remaining = $totalTime -$charge;
                                        }else{
                                            $cptActivityId = 3;
                                            $unit = 0;
                                            $charge = 0;
                                            $duration = $totalTime;
                                            $remaining = $totalTime;
                                        }
                                    }
                                    $insertCallDurationObj = [
                                        "udid" => Str::uuid()->toString(),
                                        "duration" => $duration,
                                        "remaining" => $remaining,
                                        "charge" => $charge,
                                        "patientId" => $callRecording->patientId,
                                    ];
                                    if(count($insertCallDurationObj) > 0){
                                        $this->generateNextBilling($cptActivityId,$unit,$charge,$patientIdx,$providerId,$cptCodeId,$insertCallDurationObj,$cptBillingNext);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }else{
            die("Today no billing in queue");
        }
    }

    public function cptCodeServiceInsert($data) {
        $insertCptObj = [
            'udid' => Str::uuid()->toString(),
            'cptCodeActivityId' => $data["cptCodeActivityId"],
            'patientId' => $data["patientId"],
            'entity' => 'call',
            'referenceId' => 99,
            'units' => $data["unit"],
            'cost' => $data["billingAmout"],
            'status' => 299,
            'placeOfService' => 295,
            'createdBy' => Auth::id(),
            'providerId' => $data["providerId"]
        ];

        $cpt = CPTCodeCptCodeService::create($insertCptObj);
        return $cpt;
    }

    public function generateNextBilling($cptActivityId,$unit,$charge,$patientIdx,$providerId,$cptCodeId,$insertCallDurationObj,$cptBillingNext){
        CallDurationBilling::create($insertCallDurationObj);
        $cpt = CPTCode::where('id', $cptCodeId)->first();
        if($charge > 0){
            // insert record in cpt billing report
            $insertCptObj = [
                'cptCodeActivityId' => $cptActivityId,
                'unit' => $unit,
                'patientId' => $patientIdx,
                'billingAmout' => $cpt->billingAmout,
                'providerId' => $providerId
            ];
            $cptData = $this->cptCodeServiceInsert($insertCptObj);
            if(isset($cptData->id)){
                // Getting Cpt code 99458 for Additional 20mint after first 20mints.
                $cptCode = CPTCode::where("name",99458)->first();
                //Generate Nextbilling Date
                if(isset($cptCode->id)){
                    $now = date('Y-m-d H:i:s');
                    $nextDate = strtotime($now. ' + 15 days');
                    $nextDate = Helper::date($nextDate);
                    $insertArr = array(
                        "cptCodeId" => $cptCode->id,
                        "referenceId" => $patientIdx,
                        "entityType" => "patient",
                        "functionName" => "cptNextBillingForCall",
                        "controllerName" => "CPTCodeController",
                        "lastBillingAt" => '',
                        "nextBillingAt" => $nextDate,
                        'providerId' => $providerId
                    );
                    $cptBilling = CptCodeNextBillingServices::create($insertArr);
                }
            }
        }else{
            $now = date('Y-m-d H:i:s');
            $nextDate = strtotime($now. ' + 15 days');
            $nextDate = Helper::date($nextDate);

            $insertArr = array(
                "cptCodeId" => $cptCodeId,
                "referenceId" => $patientIdx,
                "entityType" => "patient",
                "functionName" => "cptNextBillingForCall",
                "controllerName" => "CPTCodeController",
                "lastBillingAt" => '',
                "nextBillingAt" => $nextDate,
                'providerId' => $providerId
            );
            $cptBilling = CptCodeNextBillingServices::create($insertArr);
        }

        CptCodeNextBillingServices::where("id",$cptBillingNext->id)->update(["isQueue"=>1]);
    }
}
