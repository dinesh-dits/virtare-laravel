<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use Illuminate\Support\Facades\DB;
use App\Models\Patient\PatientStaff;
use Illuminate\Support\Facades\Auth;
use App\Transformers\Search\SearchTransformer;
use App\Transformers\Patient\PatientStaffTransformer;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class PatientStaffService
{
    // Assign Staff to Patient
    public function assignStaffToPatient($request, $id, $patientStaffId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            if ($request->input('staff')) {
                $staff = Helper::entity('staff', $request->input('staff'));
            }
            if (!$patientStaffId) {
                $patient = Helper::entity('patient', $id);
                $staffExists = PatientStaff::where([['patientId', $patient], ['isCareTeam', $request->input('type')], ['staffId', $staff]])->exists();
                if ($staffExists) {
                    if ($request->input('isPrimary') == 1) {
                        $isPrimaryData = ['isPrimary' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                        PatientStaff::where([['patientId', $patient], ['isCareTeam', $request->input('type')]])->update($isPrimaryData);
                        $patientstaff = PatientStaff::where([['patientId', $patient], ['isCareTeam', $request->input('type')]])->first();
                        if ($patientstaff) {
                            $changeLog = [
                                'udid' => Str::uuid()->toString(), 'table' => 'patientStaffs', 'tableId' => $patientstaff->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                                'value' => json_encode($isPrimaryData), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                            ];
                            ChangeLog::create($changeLog);
                        }
                    }
                    $isPrimary = $request->input('isPrimary');
                    $input = ['staffId' => $staff, 'updatedBy' => Auth::id(), 'isPrimary' => $isPrimary, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                    PatientStaff::where([['patientId', $patient], ['isCareTeam', $request->input('type')], ['staffId', $staff]])->update($input);
                    $patientstaffData = PatientStaff::where([['patientId', $patient], ['isCareTeam', $request->input('type')], ['staffId', $staff]])->first();
                    if ($patientstaffData) {
                        $changeLog = [
                            'udid' => Str::uuid()->toString(), 'table' => 'patientStaffs', 'tableId' => $patientstaffData->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                            'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                        ];
                        ChangeLog::create($changeLog);
                    }
                } else {
                    if ($request->input('isPrimary') == 1) {
                        $isPrimaryData = ['isPrimary' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                        PatientStaff::where([['patientId', $patient], ['isCareTeam', $request->input('type')]])->update($isPrimaryData);
                        $patientstaffData = PatientStaff::where([['patientId', $patient], ['isCareTeam', $request->input('type')]])->first();
                        if ($patientstaffData) {
                            $changeLog = [
                                'udid' => Str::uuid()->toString(), 'table' => 'patientStaffs', 'tableId' => $patientstaffData->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                                'value' => json_encode($isPrimaryData), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                            ];
                            ChangeLog::create($changeLog);
                        }
                    }
                    $input = [
                        'udid' => Str::uuid()->toString(), 'patientId' => $patient, 'staffId' => $staff, 'createdBy' => Auth::id(), 'isPrimary' => $request->input('isPrimary'),
                        'isCareTeam' => $request->input('type'), 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                    ];
                    $data = PatientStaff::create($input);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patientStaffs', 'tableId' => $data->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                    ];
                    ChangeLog::create($changeLog);
                }
                return response()->json(['message' => trans('messages.createdSuccesfully')], 200);
            } else {
                $patient = Helper::entity('patient', $id);
                if ($request->input('isPrimary') == 1) {
                    $isPrimaryData = ['isPrimary' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                    PatientStaff::where([['patientId', $patient], ['isCareTeam', $request->input('type')]])->update($isPrimaryData);
                    $data = PatientStaff::where([['patientId', $patient], ['isCareTeam', $request->input('type')]])->first();
                    if ($data) {
                        $changeLog = [
                            'udid' => Str::uuid()->toString(), 'table' => 'patientStaffs', 'tableId' => $data->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                            'value' => json_encode($isPrimaryData), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                        ];
                        ChangeLog::create($changeLog);
                    }
                }
                $isPrimary = $request->input('isPrimary');
                $patientStaff = [];
                if (!empty($staff)) {
                    $patientStaff['staffId'] = $staff;
                }
                if (isset($isPrimary)) {
                    $patientStaff['isPrimary'] = $request->isPrimary;
                }
                $patientStaff['updatedBy'] = Auth::id();
                $patientStaff['providerId'] = $provider;
                $patientStaff['providerLocationId'] = $providerLocation;
                PatientStaff::where('udid', $patientStaffId)->update($patientStaff);
                $data = Helper::tableName('App\Models\Patient\PatientStaff', $patientStaffId);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientStaffs', 'tableId' => $data, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($patientStaff), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                ];
                ChangeLog::create($changeLog);
                return response()->json(['message' => trans('messages.updatedSuccesfully')], 200);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Assign Staff to Patient
    public function getAssignStaffToPatient($request, $id, $patientStaffId)
    {
        try {
            $data = PatientStaff::select('patientStaffs.*')->with('patient', 'staff');

            // $data->leftJoin('providers', 'providers.id', '=', 'patientStaffs.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'patientStaffs.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('patientStaffs.providerLocationId', '=', 'providerLocations.id')->where('patientStaffs.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('patientStaffs.providerLocationId', '=', 'providerLocationStates.id')->where('patientStaffs.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('patientStaffs.providerLocationId', '=', 'providerLocationCities.id')->where('patientStaffs.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('patientStaffs.providerLocationId', '=', 'subLocations.id')->where('patientStaffs.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('patientStaffs.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['patientStaffs.providerLocationId', $providerLocation], ['patientStaffs.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['patientStaffs.providerLocationId', $providerLocation], ['patientStaffs.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['patientStaffs.providerLocationId', $providerLocation], ['patientStaffs.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['patientStaffs.providerLocationId', $providerLocation], ['patientStaffs.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['patientStaffs.programId', $program], ['patientStaffs.entityType', $entityType]]);
            // }
            if ($patientStaffId) {
                $data = $data->where('patientStaffs.udid', $patientStaffId)->wherehas('staff')->first();
                return fractal()->item($data)->transformWith(new PatientStaffTransformer())->toArray();
            } else {
                $patient = Helper::entity('patient', $id);
                $data->where('patientId', $patient)
                    ->leftJoin('patients', 'patients.id', '=', 'patientStaffs.patientId')
                    ->leftJoin('staffs', 'staffs.id', '=', 'patientStaffs.staffId')
                    ->leftJoin('globalCodes', 'globalCodes.id', '=', 'staffs.typeId');
                if ($request->isPrimary == 1) {
                    $data->where('patientStaffs.isPrimary', 1);
                }
                if (isset($request->type)) {
                    if ($request->type == 'Specialist') {
                        $data->where('globalCodes.name', $request->type);
                    } elseif ($request->type == 'Staff') {
                        $data->where([['globalCodes.name', $request->type], ['patientStaffs.isCareTeam', 1]]);
                    } else {
                        $data->where('patientStaffs.isCareTeam', $request->type);
                    }
                }
                $data = $data->select('patientStaffs.*')->groupBy('patientStaffs.id')->orderBy('patientStaffs.createdAt', 'DESC')->paginate(env('PER_PAGE', 20));
                return fractal()->collection($data)->transformWith(new PatientStaffTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Assign Staff to Patient
    public function deleteAssignStaffToPatient($request, $id, $patientStaffId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            PatientStaff::where('udid', $patientStaffId)->update($input);
            $data = Helper::entity('patientStaff', $patientStaffId);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientStaffs', 'tableId' => $data, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            PatientStaff::where('udid', $patientStaffId)->delete();
            return response()->json(['message' => trans('messages.deletedSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Global Search
    public function search($request)
    {
        try {
            $data = DB::select(
                'CALL search("' . $request->search . '")',
            );
            return fractal()->collection($data)->transformWith(new SearchTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
