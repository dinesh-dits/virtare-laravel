<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\Screen\Screen;
use Illuminate\Support\Facades\Auth;
use App\Transformers\Screen\ScreenTransformer;

class ScreenService
{
    // Add Screen
    public function addScreen($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $screen = [
                'name' => $request->name,
                'moduleId' => $request->moduleId,
                'createdBy' => Auth::id(),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation
            ];
            $data = Screen::create($screen);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'screens', 'tableId' => $data->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($screen), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            return response()->json(['message' => trans('messages.createdSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Screen
    public function getScreenList($request)
    {
        try {
            $data = Screen::select('screens.*');

            // $data->leftJoin('providers', 'providers.id', '=', 'screens.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'screens.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('screens.providerLocationId', '=', 'providerLocations.id')->where('screens.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('screens.providerLocationId', '=', 'providerLocationStates.id')->where('screens.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('screens.providerLocationId', '=', 'providerLocationCities.id')->where('screens.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('screens.providerLocationId', '=', 'subLocations.id')->where('screens.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('screens.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['screens.providerLocationId', $providerLocation], ['screens.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['screens.providerLocationId', $providerLocation], ['screens.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['screens.providerLocationId', $providerLocation], ['screens.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['screens.providerLocationId', $providerLocation], ['screens.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['screens.programId', $program], ['screens.entityType', $entityType]]);
            // }
            $data = $data->get();
            return fractal()->collection($data)->transformWith(new ScreenTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
