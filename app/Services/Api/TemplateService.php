<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\Template\Template;
use Illuminate\Support\Facades\Auth;
use App\Transformers\Template\TemplateTransformer;

class TemplateService
{

    // List Template
    public function listTemplate()
    {
        try {
            $data = Template::select('templates.*');

            // $data->leftJoin('providers', 'providers.id', '=', 'templates.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'templates.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('templates.providerLocationId', '=', 'providerLocations.id')->where('templates.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('templates.providerLocationId', '=', 'providerLocationStates.id')->where('templates.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('templates.providerLocationId', '=', 'providerLocationCities.id')->where('templates.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('templates.providerLocationId', '=', 'subLocations.id')->where('templates.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('templates.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['templates.providerLocationId', $providerLocation], ['templates.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['templates.providerLocationId', $providerLocation], ['templates.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['templates.providerLocationId', $providerLocation], ['templates.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['templates.providerLocationId', $providerLocation], ['templates.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['templates.programId', $program], ['templates.entityType', $entityType]]);
            // }
            $data = $data->get();
            return fractal()->collection($data)->transformWith(new TemplateTransformer())->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Template
    public function createTemplate($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $template = [
                'udid' => Str::uuid()->toString(),
                'name' => $request->input('name'),
                'dataType' => $request->input('dataType'),
                'templateType' => $request->input('templateType'),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation
            ];
            $newtemplate = Template::create($template);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'templates', 'tableId' => $newtemplate->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($template), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            $newtemplate = Template::where('udid', $newtemplate->udid)->first();
            $message = ['message' => trans('messages.createdSuccesfully')];
            $resp = fractal()->item($newtemplate)->transformWith(new TemplateTransformer())->toArray();
            $endData = array_merge($message, $resp);
            return $endData;
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Template
    public function updateTemplate($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $template = [
                'name' => $request->input('name'),
                'dataType' => $request->input('dataType'),
                'templateType' => $request->input('templateType'),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation
            ];
            $newtemplate = Template::find($id)->update($template);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'templates', 'tableId' => $id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($template), 'type' => 'update', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            $newtemplate = Template::where('id', $id)->first();
            $message = ['message' => trans('messages.updatedSuccesfully')];
            $resp = fractal()->item($newtemplate)->transformWith(new TemplateTransformer())->toArray();
            $endData = array_merge($message, $resp);
            return $endData;
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Template
    public function deleteTemplate($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $data = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            Template::find($id)->update($data);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'templates', 'tableId' => $id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($data), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            Template::find($id)->delete();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
