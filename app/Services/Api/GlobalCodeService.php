<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\GlobalCode\GlobalCode;
use App\Models\GlobalCode\GlobalCodeCategory;
use App\Transformers\GlobalCode\GlobalCodeTransformer;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Transformers\GlobalCode\GlobalCodeCategoryTransformer;
use App\Transformers\GlobalCode\GlobalStartEndDateTransformer;

class GlobalCodeService
{
    // List Global Code Category
    public function globalCodeCategoryList($request, $id)
    {
        try {
            $data = GlobalCodeCategory::select('globalCodeCategories.*')->with('globalCode');

            // $data->leftJoin('providers', 'providers.id', '=', 'globalCodeCategories.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'globalCodeCategories.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //      $join->on('globalCodeCategories.providerLocationId', '=', 'providerLocations.id')->where('globalCodeCategories.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //      $join->on('globalCodeCategories.providerLocationId', '=', 'providerLocationStates.id')->where('globalCodeCategories.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //      $join->on('globalCodeCategories.providerLocationId', '=', 'providerLocationCities.id')->where('globalCodeCategories.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //      $join->on('globalCodeCategories.providerLocationId', '=', 'subLocations.id')->where('globalCodeCategories.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //      $provider = Helper::providerId();
            //      $data->where('globalCodeCategories.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //      $providerLocation = Helper::providerLocationId();
            //      if (request()->header('entityType') == 'Country') {
            //           $data->where([['globalCodeCategories.providerLocationId', $providerLocation], ['globalCodeCategories.entityType', 'Country']]);
            //      }
            //      if (request()->header('entityType') == 'State') {
            //           $data->where([['globalCodeCategories.providerLocationId', $providerLocation], ['globalCodeCategories.entityType', 'State']]);
            //      }
            //      if (request()->header('entityType') == 'City') {
            //           $data->where([['globalCodeCategories.providerLocationId', $providerLocation], ['globalCodeCategories.entityType', 'City']]);
            //      }
            //      if (request()->header('entityType') == 'subLocation') {
            //           $data->where([['globalCodeCategories.providerLocationId', $providerLocation], ['globalCodeCategories.entityType', 'subLocation']]);
            //      }
            // }
            // if (request()->header('programId')) {
            //      $program = Helper::programId();
            //      $entityType = Helper::entityType();
            //      $data->where([['globalCodeCategories.programId', $program], ['globalCodeCategories.entityType', $entityType]]);
            // }

            if (!$id) {
                if ($request->all) {
                    $data->where('globalCodeCategories.name', 'LIKE', '%' . $request->search . '%')->orWhereHas('globalCode', function ($query) use ($request) {
                        $query->where('name', 'LIKE', '%' . $request->search . '%')->orWhere('description', 'LIKE', '%' . $request->search . '%');
                    })->with('globalCode', function ($query) {
                        $query->orderBy('name', 'ASC');
                    })->orderBy('globalCodeCategories.name', 'ASC');
                    $data = $data->get();
                    return fractal()->collection($data)->transformWith(new GlobalCodeCategoryTransformer())->toArray();
                } else {
                    $data->where('globalCodeCategories.name', 'LIKE', '%' . $request->search . '%')->orWhereHas('globalCode', function ($query) use ($request) {
                        $query->where('name', 'LIKE', '%' . $request->search . '%')->orWhere('description', 'LIKE', '%' . $request->search . '%');
                    });
                    if ($request->orderField == 'globalCodeCategory') {
                        $data->orderBy('globalCodeCategories.name', $request->orderBy);
                    } elseif ($request->orderField == 'name') {
                        $data->join('globalCodes as g1', 'g1.globalCodeCategoryId', '=', 'globalCodeCategories.id')
                            ->orderBy('g1.name', $request->orderBy);
                    } elseif ($request->orderField == 'description') {
                        $data->join('globalCodes as g2', 'g2.globalCodeCategoryId', '=', 'globalCodeCategories.id')
                            ->orderBy('g2.description', $request->orderBy);
                    } elseif ($request->orderField == 'priority') {
                        $data->join('globalCodes as g3', 'g3.globalCodeCategoryId', '=', 'globalCodeCategories.id')
                            ->orderBy('g3.priority', 'ASC');
                    } else {
                        $data->orderBy('globalCodeCategories.name', 'ASC');
                    }
                    $data = $data->paginate(env('PER_PAGE', 20));
                    return fractal()->collection($data)->transformWith(new GlobalCodeCategoryTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
                }
            } else {
                $global = GlobalCodeCategory::where('id', $id)->first();
                return fractal()->item($global)->transformWith(new GlobalCodeCategoryTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Global Code
    public function globalCodeList($request, $id): array
    {
        try {
            $global = GlobalCode::select('globalCodes.*')->with('globalCodeCategory');

            // $global->leftJoin('providers', 'providers.id', '=', 'globalCodes.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $global->leftJoin('programs', 'programs.id', '=', 'globalCodes.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $global->leftJoin('providerLocations', function ($join) {
            //      $join->on('globalCodes.providerLocationId', '=', 'providerLocations.id')->where('globalCodes.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $global->leftJoin('providerLocationStates', function ($join) {
            //      $join->on('globalCodes.providerLocationId', '=', 'providerLocationStates.id')->where('globalCodes.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $global->leftJoin('providerLocationCities', function ($join) {
            //      $join->on('globalCodes.providerLocationId', '=', 'providerLocationCities.id')->where('globalCodes.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $global->leftJoin('subLocations', function ($join) {
            //      $join->on('globalCodes.providerLocationId', '=', 'subLocations.id')->where('globalCodes.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //      $provider = Helper::providerId();
            //      $data->where('globalCodes.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //      $providerLocation = Helper::providerLocationId();
            //      if (request()->header('entityType') == 'Country') {
            //           $data->where([['globalCodes.providerLocationId', $providerLocation], ['globalCodes.entityType', 'Country']]);
            //      }
            //      if (request()->header('entityType') == 'State') {
            //           $data->where([['globalCodes.providerLocationId', $providerLocation], ['globalCodes.entityType', 'State']]);
            //      }
            //      if (request()->header('entityType') == 'City') {
            //           $data->where([['globalCodes.providerLocationId', $providerLocation], ['globalCodes.entityType', 'City']]);
            //      }
            //      if (request()->header('entityType') == 'subLocation') {
            //           $data->where([['globalCodes.providerLocationId', $providerLocation], ['globalCodes.entityType', 'subLocation']]);
            //      }
            // }
            if (!$id) {
                if ($request->all) {
                    $global->orderBy('globalCodes.name', 'ASC')->get();
                    return fractal()->collection($global)->transformWith(new GlobalCodeTransformer())->toArray();
                } else {
                    $global = GlobalCode::join('globalCodeCategories as g1', 'globalCodes.globalCodeCategoryId', '=', 'g1.id');
                    if ($request->active) {
                        $global->where('globalCodes.predefined', '0');
                    }
                    if ($request->search) {
                        $global->where('globalCodes.name', 'LIKE', '%' . $request->search . '%');
                        //   ->orWhere('g1.name','LIKE', '%' . $request->search . '%');
                    }
                    if ($request->globalCodeCategoryId) {
                        $global->where('globalCodes.globalCodeCategoryId', $request->globalCodeCategoryId);
                        if ($request->orderField == 'name') {
                            $global->orderBy('globalCodes.name', $request->orderBy);
                        } elseif ($request->orderField == 'globalCodeCategory') {
                            $global->orderBy('g1.name', $request->orderBy);
                        } elseif ($request->orderField == 'description') {
                            $global->orderBy('globalCodes.description', $request->orderBy);
                        } elseif ($request->orderField == 'priority') {
                            $global->orderBy('globalCodes.priority', 'ASC');
                        } else {
                            $global->orderBy('globalCodes.name', 'ASC');
                        }
                        $global = $global->select('globalCodes.*')->get();
                        return fractal()->collection($global)->transformWith(new GlobalCodeTransformer())->toArray();
                    }

                    if ($request->search) {
                        $global->orWhere('g1.name', 'LIKE', '%' . $request->search . '%');
                    }

                    if ($request->orderField == 'name') {
                        $global->orderBy('globalCodes.name', $request->orderBy);
                    } elseif ($request->orderField == 'globalCodeCategory') {
                        $global->orderBy('g1.name', $request->orderBy);
                    } elseif ($request->orderField == 'description') {
                        $global->orderBy('globalCodes.description', $request->orderBy);
                    } elseif ($request->orderField == 'priority') {
                        $global->orderBy('globalCodes.priority', 'ASC');
                    } else {
                        $global->orderBy('globalCodes.name', 'ASC');
                    }
                    $global = $global->paginate(env('PER_PAGE', 20));
                    return fractal()->collection($global)->transformWith(new GlobalCodeTransformer())->paginateWith(new IlluminatePaginatorAdapter($global))->toArray();
                }
            } else {
                $global = $global->where('globalCodes.id', $id)->first();
                return fractal()->item($global)->transformWith(new GlobalCodeTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Global Code
    public function globalCodeCreate($request): array
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $global = GlobalCode::where('globalCodeCategoryId', $request->input('globalCodeCategory'))->where('name', $request->input('name'))->first();
            $input = [
                'globalCodeCategoryId' => $request->input('globalCodeCategory'), 'createdBy' => Auth::id(), 'entityType' => $entityType,
                'udid' => Str::uuid()->toString(), 'isActive' => $request->input('isActive'), 'name' => $request->input('name'),
                'description' => $request->input('description'), 'providerId' => $provider, 'providerLocationId' => $providerLocation
            ];
            if (!$global) {
                $global = GlobalCode::create($input);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'globalCodes', 'tableId' => $global->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                ];
                ChangeLog::create($changeLog);
            } else {
                GlobalCode::find($global->id)->update($input);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'globalCodes', 'tableId' => $global->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                ];
                ChangeLog::create($changeLog);
            }
            $data = GlobalCode::where('id', $global->id)->with('globalCodeCategory')->first();
            $category = GlobalCodeCategory::where('id', $data->globalCodeCategoryId);
            $userdata = fractal()->item($data)->transformWith(new GlobalCodeTransformer())->toArray();
            $message = ['message' => trans('messages.createdSuccesfully')];
            $endData = array_merge($message, $userdata);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Global Code
    public function globalCodeUpdate($request, $id): array
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $globalCode = array();
            if (!empty($request->globalcodecategory)) {
                $globalCode['globalCodeCategoryId'] = $request->globalcodecategory;
            }
            if (!empty($request->name)) {
                $globalCode['name'] = $request->name;
            }
            if (!empty($request->description)) {
                $globalCode['description'] = $request->description;
            }
            if (isset($request->isActive)) {
                $globalCode['isActive'] = $request->isActive;
            }
            $globalCode['updatedBy'] = Auth::id();
            $globalCode['providerId'] = $provider;
            $globalCode['providerLocationId'] = $providerLocation;
            $global = GlobalCode::where('globalCodeCategoryId', $request->input('globalCodeCategory'))->where('name', $request->input('name'))->first();
            if (!$global) {
                $global = GlobalCode::find($id)->update($globalCode);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'globalCodes', 'tableId' => $id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($globalCode), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
            } else {
                GlobalCode::find($global->id)->update($globalCode);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'globalCodes', 'tableId' => $global->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($globalCode), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
            }
            $data = GlobalCode::where('id', $id)->with('globalCodeCategory')->first();
            $userdata = fractal()->item($data)->transformWith(new GlobalCodeTransformer())->toArray();
            $message = ['message' => trans('messages.updatedSuccesfully')];
            $endData = array_merge($message, $userdata);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Global Code
    public function globalCodeDelete($request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $data = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            GlobalCode::find($id)->update($data);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'globalCodes', 'tableId' => $id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($data), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            GlobalCode::find($id)->delete();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Get Global Code Start End Date
    public function getGlobalStartEndDate($request)
    {
        try {
            $timelineId = '';
            if (!empty($request->timelineId)) {
                $timelineId = $request->timelineId;
            }
            $data = DB::select('CALL getGlobalStartEndDate("' . $timelineId . '")');
            if (isset($request->timelineId) && isset($data[0])) {
                return fractal()->item($data[0])->transformWith(new GlobalStartEndDateTransformer())->toArray();
            } else {
                return fractal()->collection($data)->transformWith(new GlobalStartEndDateTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
