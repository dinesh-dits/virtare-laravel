<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\Program\Program;
use Illuminate\Support\Facades\Auth;
use App\Transformers\Program\ProgramTransformer;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class ProgramService
{
    // List Program
    public function programList($request, $id)
    {
        try {
            $data = Program::select('programs.*')->with('type');

            // $data->leftJoin('providers', 'providers.id', '=', 'programs.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'programs.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('programs.providerLocationId', '=', 'providerLocations.id')->where('programs.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('programs.providerLocationId', '=', 'providerLocationStates.id')->where('programs.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('programs.providerLocationId', '=', 'providerLocationCities.id')->where('programs.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('programs.providerLocationId', '=', 'subLocations.id')->where('programs.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('programs.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['programs.providerLocationId', $providerLocation], ['programs.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['programs.providerLocationId', $providerLocation], ['programs.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['programs.providerLocationId', $providerLocation], ['programs.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['programs.providerLocationId', $providerLocation], ['programs.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['programs.programId', $program], ['programs.entityType', $entityType]]);
            // }
            if (!$id) {
                if ($request->all) {
                    $data = $data->where('programs.isActive', 1)->with('type')->get();
                    return fractal()->collection($data)->transformWith(new ProgramTransformer())->toArray();
                } else {
                    if ($request->search) {
                        $data->where('programs.name', 'LIKE', '%' . $request->search . '%')->orWhere('programs.description', 'LIKE', '%' . $request->search . '%');
                    }
                    if ($request->active) {
                        $data;
                    } else {
                        $data->where('programs.isActive', 1)->with('type');
                    }
                    if ($request->orderBy && $request->orderField) {
                        $data->orderBy($request->orderField, $request->orderBy);
                    } else {
                        $data->orderBy('programs.name', 'ASC');
                    }
                    $data = $data->paginate(env('PER_PAGE', 20));
                    return fractal()->collection($data)->transformWith(new ProgramTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
                }
            } else {
                $data = $data->where('programs.udid', $id)->get();
                return fractal()->collection($data)->transformWith(new ProgramTransformer())->toArray();
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Program
    public function createProgram($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $program = [
                'udid' => Str::random(10),
                'typeId' => $request->input('typeId'),
                'description' => $request->input('description'),
                'name' => $request->input('name'),
                'isActive' => $request->input('isActive'),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
            ];
            $newData = Program::create($program);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'programs', 'tableId' => $newData->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($program), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            $staffData = Program::where('id', $newData->id)->first();
            $message = ["message" => "created Successfully"];
            $resp = fractal()->item($staffData)->transformWith(new ProgramTransformer())->toArray();
            $endData = array_merge($message, $resp);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Program
    public function updateProgram($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $program = array();
            if (!empty($request->input('typeId'))) {
                $program['typeId'] = $request->input('typeId');
            }
            if (!empty($request->input('description'))) {
                $program['description'] = $request->input('description');
            }
            if (!empty($request->input('name'))) {
                $program['name'] = $request->input('name');
            }
            if (empty($request->input('isActive'))) {
                $program['isActive'] = 0;
            } else {
                $program['isActive'] = 1;
            }
            $program['updatedBy'] = Auth::id();
            $program['providerId'] = $provider;
            $program['providerLocationId'] = $providerLocation;


            if (!empty($program)) {
                Program::where('udid', $id)->update($program);
                $programData = Helper::entity('program', $id);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'programs', 'tableId' => $programData,
                    'value' => json_encode($program), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
            }
            $newData = Program::where('udid', $id)->first();
            $message = ["message" => "updated Successfully"];
            $resp = fractal()->item($newData)->transformWith(new ProgramTransformer())->toArray();
            $endData = array_merge($message, $resp);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Program
    public function deleteProgram($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $input = ['deletedBy' => 1, 'isActive' => 0, 'isDelete' => 1, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            Program::where('udid', $id)->update($input);
            $programData = Helper::entity('program', $id);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'programs', 'tableId' => $programData, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            Program::where('udid', $id)->delete();
            return response()->json(['message' => "Deleted Successfully"]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
