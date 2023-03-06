<?php

namespace App\Services\Api;

use App\Helper;
use App\Models\Patient\Patient;
use App\Models\Patient\PatientFamilyMember;
use App\Transformers\Patient\PatientTransformer;
use Exception;

class FamilyMemberService
{
    // List Patient
    public function listPatient($request, $id)
    {
        try {
            if ($id) {
                $patient = Helper::entity('patient', $id);
                $notAccess = Helper::haveAccess($patient);
                if (!$notAccess) {
                    $data = PatientFamilyMember::select('patientFamilyMembers.*')->whereHas('patients')->where([['patientId', $patient], ['userId', auth()->user()->id]]);

                    $data->leftJoin('providers', 'providers.id', '=', 'patientFamilyMembers.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
                    $data->leftJoin('programs', 'programs.id', '=', 'patientFamilyMembers.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

                    $data->leftJoin('providerLocations', function ($join) {
                        $join->on('patientFamilyMembers.providerLocationId', '=', 'providerLocations.id')->where('patientFamilyMembers.entityType', '=', 'Country');
                    })->whereNull('providerLocations.deletedAt');

                    $data->leftJoin('providerLocationStates', function ($join) {
                        $join->on('patientFamilyMembers.providerLocationId', '=', 'providerLocationStates.id')->where('patientFamilyMembers.entityType', '=', 'State');
                    })->whereNull('providerLocationStates.deletedAt');

                    $data->leftJoin('providerLocationCities', function ($join) {
                        $join->on('patientFamilyMembers.providerLocationId', '=', 'providerLocationCities.id')->where('patientFamilyMembers.entityType', '=', 'City');
                    })->whereNull('providerLocationCities.deletedAt');

                    $data->leftJoin('subLocations', function ($join) {
                        $join->on('patientFamilyMembers.providerLocationId', '=', 'subLocations.id')->where('patientFamilyMembers.entityType', '=', 'subLocation');
                    })->whereNull('subLocations.deletedAt');

                    if (request()->header('providerId')) {
                        $provider = Helper::providerId();
                        $data->where('patientFamilyMembers.providerId', $provider);
                    }
                    if (request()->header('providerLocationId')) {
                        $providerLocation = Helper::providerLocationId();
                        if (request()->header('entityType') == 'Country') {
                            $data->where([['patientFamilyMembers.providerLocationId', $providerLocation], ['patientFamilyMembers.entityType', 'Country']]);
                        }
                        if (request()->header('entityType') == 'State') {
                            $data->where([['patientFamilyMembers.providerLocationId', $providerLocation], ['patientFamilyMembers.entityType', 'State']]);
                        }
                        if (request()->header('entityType') == 'City') {
                            $data->where([['patientFamilyMembers.providerLocationId', $providerLocation], ['patientFamilyMembers.entityType', 'City']]);
                        }
                        if (request()->header('entityType') == 'subLocation') {
                            $data->where([['patientFamilyMembers.providerLocationId', $providerLocation], ['patientFamilyMembers.entityType', 'subLocation']]);
                        }
                    }
                    if (request()->header('programId')) {
                        $program = Helper::programId();
                        $entityType = Helper::entityType();
                        $data->where([['patientFamilyMembers.programId', $program], ['patientFamilyMembers.entityType', $entityType]]);
                    }
                    $data = $data->first();
                    return fractal()->item($data->patients)->transformWith(new PatientTransformer(true))->toArray();
                } else {
                    return $notAccess;
                }
            } elseif (!$id) {
                $data = Patient::select('patients.*')->whereHas('family', function ($query) {
                    $query->where('userId', auth()->user()->id);
                });

                // $data->leftJoin('providers', 'providers.id', '=', 'patients.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
                // $data->leftJoin('programs', 'programs.id', '=', 'patients.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');
                // $data->leftJoin('providerLocations', function ($join) {
                //     $join->on('patients.providerLocationId', '=', 'providerLocations.id')->where('patients.entityType', '=', 'Country');
                // })->whereNull('providerLocations.deletedAt');

                // $data->leftJoin('providerLocationStates', function ($join) {
                //     $join->on('patients.providerLocationId', '=', 'providerLocationStates.id')->where('patients.entityType', '=', 'State');
                // })->whereNull('providerLocationStates.deletedAt');

                // $data->leftJoin('providerLocationCities', function ($join) {
                //     $join->on('patients.providerLocationId', '=', 'providerLocationCities.id')->where('patients.entityType', '=', 'City');
                // })->whereNull('providerLocationCities.deletedAt');

                // $data->leftJoin('subLocations', function ($join) {
                //     $join->on('patients.providerLocationId', '=', 'subLocations.id')->where('patients.entityType', '=', 'subLocation');
                // })->whereNull('subLocations.deletedAt');

                // if (request()->header('providerId')) {
                //     $provider = Helper::providerId();
                //     $data->where('patients.providerId', $provider);
                // }
                // if (request()->header('providerLocationId')) {
                //     $providerLocation = Helper::providerLocationId();
                //     if (request()->header('entityType') == 'Country') {
                //         $data->where([['patients.providerLocationId', $providerLocation], ['patients.entityType', 'Country']]);
                //     }
                //     if (request()->header('entityType') == 'State') {
                //         $data->where([['patients.providerLocationId', $providerLocation], ['patients.entityType', 'State']]);
                //     }
                //     if (request()->header('entityType') == 'City') {
                //         $data->where([['patients.providerLocationId', $providerLocation], ['patients.entityType', 'City']]);
                //     }
                //     if (request()->header('entityType') == 'subLocation') {
                //         $data->where([['patients.providerLocationId', $providerLocation], ['patients.entityType', 'subLocation']]);
                //     }
                // }
                $data = $data->orderBy('firstName', 'ASC')->get();
                return fractal()->collection($data)->transformWith(new PatientTransformer(true))->toArray();
            } else {
                return response()->json(['message' => trans('messages.unauthenticated')], 401);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
