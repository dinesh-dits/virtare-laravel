<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use App\Models\Flag\Flag;
use App\Transformers\Flag\FlagTransformer;

class FlagService
{
    // List Flag
    public function flagList($request)
    {
        try {
            $data = Flag::select('flags.*')->with('typeId')
                ->leftJoin('globalCodes as g2', 'g2.id', '=', 'flags.type');


            // $data->leftJoin('providers', 'providers.id', '=', 'flags.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'flags.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('flags.providerLocationId', '=', 'providerLocations.id')->where('flags.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('flags.providerLocationId', '=', 'providerLocationStates.id')->where('flags.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('flags.providerLocationId', '=', 'providerLocationCities.id')->where('flags.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('flags.providerLocationId', '=', 'subLocations.id')->where('flags.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('flags.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['flags.providerLocationId', $providerLocation], ['flags.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['flags.providerLocationId', $providerLocation], ['flags.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['flags.providerLocationId', $providerLocation], ['flags.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['flags.providerLocationId', $providerLocation], ['flags.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['flags.programId', $program], ['flags.entityType', $entityType]]);
            // }


            if ($request->type == 'patient') {
                $data->whereIn('g2.name',['Patient','Both']);
            } else {
                $data->whereIn('g2.name', ['Other','Both']);
            }
            if ($request->category) {
                // Category = 1 (Critical/Moderate/WNL)
                // Category = 2 (Escalation/Watchlist/Communication/Trending)
                $data->where('flags.category', $request->category);
            }
            if ($request->orderField == 'priority') {
                $data->orderBy('flags.priority', 'ASC');
            } else {
                $data->orderBy('flags.id', 'ASC');
            }
            
            $data = $data->get();
            return fractal()->collection($data)->transformWith(new FlagTransformer)->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
