<?php

namespace App\Services\Api;

use App\Helper;
use App\Models\Tag\Tags;
use App\Transformers\Tag\TagsTransformer;
use Exception;

class TagService
{

    public function listTag($request, $id)
    {
        try {
            $data = Tags::select('tag.*');

            // $data->leftJoin('providers', 'providers.id', '=', 'tag.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'tag.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('tag.providerLocationId', '=', 'providerLocations.id')->where('tag.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('tag.providerLocationId', '=', 'providerLocationStates.id')->where('tag.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('tag.providerLocationId', '=', 'providerLocationCities.id')->where('tag.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('tag.providerLocationId', '=', 'subLocations.id')->where('tag.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('tag.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['tag.providerLocationId', $providerLocation], ['tag.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['tag.providerLocationId', $providerLocation], ['tag.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['tag.providerLocationId', $providerLocation], ['tag.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['tag.providerLocationId', $providerLocation], ['tag.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['tag.programId', $program], ['tag.entityType', $entityType]]);
            // }
            if ($id) {
                $data = $data->where("tag.tagId", $id)->first();
                return fractal()->collection($data)->transformWith(new TagsTransformer())->toArray();
            } else {
                if (isset($request->entityType) && $request->entityType) {
                    $data->where("entityType", $request->entityType);
                } else {
                    return false;
                }
                if (isset($request->search) && $request->search) {
                    $data->where("tag", 'LIKE', "%" . $request->search . "%");
                }
                $data = $data->groupBy('tag')->get();
                return fractal()->collection($data)->transformWith(new TagsTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
