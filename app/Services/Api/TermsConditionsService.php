<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use App\Models\Policy\Policies;
use App\Transformers\TermsConditions\TermsConditionsTransformer;

class TermsConditionsService
{
    // List Terms and Conditions
    public function termsConditions($request)
    {
        try {
            $data = Policies::select('policies.*');

            // $data->leftJoin('providers', 'providers.id', '=', 'policies.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'policies.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('policies.providerLocationId', '=', 'providerLocations.id')->where('policies.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('policies.providerLocationId', '=', 'providerLocationStates.id')->where('policies.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('policies.providerLocationId', '=', 'providerLocationCities.id')->where('policies.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('policies.providerLocationId', '=', 'subLocations.id')->where('policies.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('policies.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['policies.providerLocationId', $providerLocation], ['policies.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['policies.providerLocationId', $providerLocation], ['policies.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['policies.providerLocationId', $providerLocation], ['policies.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['policies.providerLocationId', $providerLocation], ['policies.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['policies.programId', $program], ['policies.entityType', $entityType]]);
            // }
            $data->where('policies.language', app('translator')->getLocale());
            if ($request->filter) {
                $data->where('policies.key', $request->filter);
            }
            $data = $data->get();
            return fractal()->collection($data)->transformWith(new TermsConditionsTransformer)->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
