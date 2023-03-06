<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use App\Models\NonCompliance\NonCompliance;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Transformers\NonCompliance\NonComplianceTransformer;

class NonComplianceService
{
    // List Non Compliance
    public function nonComplianceList($request, $id)
    {
        try {
            $patientId = Helper::tableName('App\Models\Patient\Patient', $id);
            $data = NonCompliance::select('nonCompliances.*')->where('nonCompliances.patientId', $patientId);
            // $data->leftJoin('providers', 'providers.id', '=', 'nonCompliances.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'nonCompliances.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('nonCompliances.providerLocationId', '=', 'providerLocations.id')->where('nonCompliances.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('nonCompliances.providerLocationId', '=', 'providerLocationStates.id')->where('nonCompliances.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('nonCompliances.providerLocationId', '=', 'providerLocationCities.id')->where('nonCompliances.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('nonCompliances.providerLocationId', '=', 'subLocations.id')->where('nonCompliances.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');
            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('nonCompliances.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['nonCompliances.providerLocationId', $providerLocation], ['nonCompliances.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['nonCompliances.providerLocationId', $providerLocation], ['nonCompliances.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['nonCompliances.providerLocationId', $providerLocation], ['providerPrograms.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['nonCompliances.providerLocationId', $providerLocation], ['nonCompliances.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['nonCompliances.programId', $program], ['nonCompliances.entityType', $entityType]]);
            // }
            $data = $data->paginate(env('PER_PAGE', 20));
            return fractal()->collection($data)->transformWith(new NonComplianceTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
