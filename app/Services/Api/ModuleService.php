<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\Module\Module;
use Illuminate\Support\Facades\Auth;
use App\Transformers\Module\ModuleTransformer;

class ModuleService
{
    // Add Module
    public function addModule($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $module = [
                'name' => $request->name,
                'description' => $request->description,
                'createdBy' => Auth::id(),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'entityType' => $entityType,
            ];
            $data = Module::create($module);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'modules', 'tableId' => $data->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($module), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);
            return response()->json(['message' => trans('messages.createdSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Module
    public function getModuleList($request)
    {
        try {
            $module = Module::select('modules.*')->with('screens');

            // $module->leftJoin('providers', 'providers.id', '=', 'modules.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $module->leftJoin('programs', 'programs.id', '=', 'modules.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $module->leftJoin('providerLocations', function ($join) {
            //     $join->on('modules.providerLocationId', '=', 'providerLocations.id')->where('modules.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $module->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('modules.providerLocationId', '=', 'providerLocationStates.id')->where('modules.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $module->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('modules.providerLocationId', '=', 'providerLocationCities.id')->where('modules.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $module->leftJoin('subLocations', function ($join) {
            //     $join->on('modules.providerLocationId', '=', 'subLocations.id')->where('modules.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $module->where('modules.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $module->where([['modules.providerLocationId', $providerLocation], ['modules.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $module->where([['modules.providerLocationId', $providerLocation], ['modules.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $module->where([['modules.providerLocationId', $providerLocation], ['providerPrograms.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $module->where([['modules.providerLocationId', $providerLocation], ['modules.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $module->where([['modules.programId', $program], ['modules.entityType', $entityType]]);
            // }
            $module = $module->get();
            return fractal()->collection($module)->transformWith(new ModuleTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
