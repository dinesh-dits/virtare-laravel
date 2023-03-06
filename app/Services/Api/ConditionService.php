<?php

namespace App\Services\Api;

use App\Helper;
use App\Models\Condition\Condition;
use App\Transformers\Condition\ConditionTransformer;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class ConditionService
{
    // List Condition
    public function conditionList($request, $id): array
    {
        try {
            $data = Condition::select('conditions.*');
            // $data->leftJoin('providers', 'providers.id', '=', 'conditions.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'conditions.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('conditions.providerLocationId', '=', 'providerLocations.id')->where('conditions.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('conditions.providerLocationId', '=', 'providerLocationStates.id')->where('conditions.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('conditions.providerLocationId', '=', 'providerLocationCities.id')->where('conditions.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('conditions.providerLocationId', '=', 'subLocations.id')->where('conditions.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('conditions.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['conditions.providerLocationId', $providerLocation], ['conditions.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['conditions.providerLocationId', $providerLocation], ['conditions.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['conditions.providerLocationId', $providerLocation], ['conditions.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['conditions.providerLocationId', $providerLocation], ['conditions.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['conditions.programId', $program], ['conditions.entityType', $entityType]]);
            // }
            if ($id) {
                $data->where('conditions.id', $id);
                $data = $data->first();
                return fractal()->item($data)->transformWith(new ConditionTransformer())->toArray();
            } else {
                $data->where('conditions.code', 'LIKE', "%" . $request->search . "%")->orWhere('conditions.description', 'LIKE', "%" . $request->search . "%");
                $data = $data->paginate(env('PER_PAGE', 20));
                return fractal()->collection($data)->transformWith(new ConditionTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
