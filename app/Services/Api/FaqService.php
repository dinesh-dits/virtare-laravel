<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use App\Models\Faq\Faq;
use App\Transformers\Faq\FaqTransformer;

class FaqService
{
    // List FAQ
    public function list($request)
    {
        try {
            $data = Faq::select('faqs.*')->where('language', app('translator')->getLocale());

            // $data->leftJoin('providers', 'providers.id', '=', 'faqs.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'faqs.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');
            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('faqs.providerLocationId', '=', 'providerLocations.id')->where('faqs.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('faqs.providerLocationId', '=', 'providerLocationStates.id')->where('faqs.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('faqs.providerLocationId', '=', 'providerLocationCities.id')->where('faqs.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('faqs.providerLocationId', '=', 'subLocations.id')->where('faqs.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('faqs.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['faqs.providerLocationId', $providerLocation], ['faqs.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['faqs.providerLocationId', $providerLocation], ['faqs.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['faqs.providerLocationId', $providerLocation], ['faqs.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['faqs.providerLocationId', $providerLocation], ['faqs.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['faqs.programId', $program], ['faqs.entityType', $entityType]]);
            // }
            $data = $data->get();
            return fractal()->collection($data)->transformWith(new FaqTransformer)->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
