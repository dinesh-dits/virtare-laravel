<?php

namespace App\Services\Api;

use App\Helper;
use App\Models\Vital\VitalTypeField;
use App\Transformers\Vital\VitalTypeFieldTransformer;

class VitalService
{

    // List Vital Field
    public function VitalTypeFieldList($request, $id)
    {
        try {
            $data = VitalTypeField::select('vitalTypeFields.*');

            // $data->leftJoin('providers', 'providers.id', '=', 'vitalTypeFields.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'vitalTypeFields.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('vitalTypeFields.providerLocationId', '=', 'providerLocations.id')->where('vitalTypeFields.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('vitalTypeFields.providerLocationId', '=', 'providerLocationStates.id')->where('vitalTypeFields.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('vitalTypeFields.providerLocationId', '=', 'providerLocationCities.id')->where('vitalTypeFields.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('vitalTypeFields.providerLocationId', '=', 'subLocations.id')->where('vitalTypeFields.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('vitalTypeFields.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['vitalTypeFields.providerLocationId', $providerLocation], ['vitalTypeFields.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['vitalTypeFields.providerLocationId', $providerLocation], ['vitalTypeFields.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['vitalTypeFields.providerLocationId', $providerLocation], ['vitalTypeFields.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['vitalTypeFields.providerLocationId', $providerLocation], ['vitalTypeFields.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['vitalTypeFields.programId', $program], ['vitalTypeFields.entityType', $entityType]]);
            // }
            if ($id) {
                $data = $data->where('vitalTypeFields.vitalTypeId', $id)->get();
            } else {
                $data = $data->get();
            }
            return fractal()->collection($data)->transformWith(new VitalTypeFieldTransformer())->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
