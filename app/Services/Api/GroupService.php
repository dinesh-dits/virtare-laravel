<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Carbon\Carbon;
use App\Models\Group\Group;
use App\Models\Staff\Staff;
use Illuminate\Support\Str;
use App\Models\Program\Program;
use App\Models\Group\StaffGroup;
use App\Models\Group\GroupWidget;
use App\Models\Group\GroupProgram;
use Illuminate\Support\Facades\DB;
use App\Models\Group\GroupProvider;
use App\Models\Patient\PatientGroup;
use Illuminate\Support\Facades\Auth;
use App\Models\Group\GroupPermission;
use App\Models\Group\GroupComposition;
use App\Models\Provider\ProviderProgram;
use App\Transformers\Group\GroupTransformer;
use App\Transformers\Group\StaffGroupTransformer;
use App\Transformers\Group\GroupWidgetTransformer;
use App\Transformers\Group\GroupProgramTransformer;
use App\Transformers\Group\GroupProviderTransformer;
use App\Transformers\Group\GroupPermissionTransformer;
use App\Transformers\Program\ProgramProviderTransformer;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Transformers\Group\GroupCompositionDetailTransformer;

class GroupService
{

    // List Group
    public function groupList($request, $id)
    {
        try {
            $data = Group::select('groups.*')->with('staff', 'location', 'state', 'city');

            // $data->leftJoin('providers', 'providers.id', '=', 'groups.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'groups.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('groups.providerLocationId', '=', 'providerLocations.id')->where('groups.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt')->leftJoin('globalCodes as g1', 'g1.id', '=', 'providerLocations.countryId');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('groups.providerLocationId', '=', 'providerLocationStates.id')->where('groups.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt')->leftJoin('globalCodes as g2', 'g2.id', '=', 'providerLocationStates.stateId');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('groups.providerLocationId', '=', 'providerLocationCities.id')->where('groups.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('groups.providerLocationId', '=', 'subLocations.id')->where('groups.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('groups.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['groups.providerLocationId', $providerLocation], ['groups.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['groups.providerLocationId', $providerLocation], ['groups.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['groups.providerLocationId', $providerLocation], ['groups.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['groups.providerLocationId', $providerLocation], ['groups.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['groups.programId', $program], ['groups.entityType', $entityType]]);
            // }

            if ($request->orderField == 'group') {
                $data->orderBy('groups.group', $request->orderBy);
            } elseif ($request->orderField == 'providerName') {
                $data->orderBy('providers.name', $request->orderBy);
            } else {
                $data->orderBy('groups.createdAt', 'DESC');
            }
            if ($request->search) {
                $data->where('groups.group', 'LIKE', '%' . $request->search . '%')->orWhere('providers.name', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('g1.name', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('g2.name', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('providerLocationCities.city', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('subLocations.subLocationName', 'LIKE', '%' . $request->search . '%');
            }
            if (!$id) {
                $data = $data->paginate(env('PER_PAGE', 20));
                return fractal()->collection($data)->transformWith(new GroupTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
            } else {
                $data = $data->where('groups.udid', $id)->first();
                return fractal()->item($data)->transformWith(new GroupTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Group
    public function createGroup($request)
    {
        try {
            // $provider = Helper::providerId();
            // $providerLocation = Helper::providerLocationId();
            $group = [
                'udid' => Str::uuid()->toString(),
                'group' => $request->input('group'),
                'isActive' => $request->input('isActive'),
                'providerLocationId' => $request->input('providerLocation'),
                'entityType' => $request->input('entityType'),
                'providerId' => $request->input('providerId'),
                'createdBy' => Auth::id(),
            ];
            $data = Group::create($group);
            $addGroup = Group::where('udid', $data->udid)->first();
            $resp = fractal()->item($addGroup)->transformWith(new GroupTransformer())->toArray();
            $message = ["message" => trans('messages.addedSuccesfully')];
            $endData = array_merge($message, $resp);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Group
    public function updateGroup($request, $id)
    {
        try {
            $group = array();
            if (!empty($request->input('group'))) {
                $group['group'] = $request->input('group');
            }
            if (empty($request->input('isActive'))) {
                $group['isActive'] = 0;
            } else {
                $group['isActive'] = 1;
            }
            $group['updatedBy'] = Auth::id();
            if (!empty($group)) {
                Group::where('udid', $id)->update($group);
            }
            return response()->json(['message' => trans('messages.updatedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Group
    public function deleteGroup($request, $id)
    {
        try {
            $group = Group::where('udid', $id)->first();
            $data = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0];
            $tables = [
                Group::where('udid', $id),
                GroupComposition::where('groupId', $group->groupId),
                StaffGroup::where('groupId', $group->groupId),
                GroupProgram::where('groupId', $group->groupId),
                GroupProvider::where('groupId', $group->groupId),
                GroupPermission::where('groupId', $group->groupId),
                PatientGroup::where('groupId', $group->groupId),
            ];
            foreach ($tables as $table) {
                $table->update($data);
                $table->delete();
            }
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Staff Group List
    public function staffGroupList($request, $id)
    {
        try {
            $group = Group::where('udid', $id)->first();
            $data = StaffGroup::select('staffGroups.*')->with('staff')->where('staffGroups.groupId', $group->groupId);

            // $data->leftJoin('providers', 'providers.id', '=', 'staffGroups.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'staffGroups.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('staffGroups.providerLocationId', '=', 'providerLocations.id')->where('staffGroups.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('staffGroups.providerLocationId', '=', 'providerLocationStates.id')->where('staffGroups.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('staffGroups.providerLocationId', '=', 'providerLocationCities.id')->where('staffGroups.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('staffGroups.providerLocationId', '=', 'subLocations.id')->where('staffGroups.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('staffGroups.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['staffGroups.providerLocationId', $providerLocation], ['staffGroups.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['staffGroups.providerLocationId', $providerLocation], ['staffGroups.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['staffGroups.providerLocationId', $providerLocation], ['staffGroups.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['staffGroups.providerLocationId', $providerLocation], ['staffGroups.entityType', 'subLocation']]);
            //     }
            // }
            $data = $data->get();
            return fractal()->collection($data)->transformWith(new StaffGroupTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Staff Group
    public function createStaffGroup($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $group = Group::where('udid', $id)->first();
            $staff = $request->staff;
            $leadLVN = Staff::join('globalCodes', 'globalCodes.id', '=', 'staffs.designationId')->whereIn('staffs.udid', $staff)->where('globalCodes.name', 'LVN - Lead')->count();
            $lvn = Staff::join('globalCodes', 'globalCodes.id', '=', 'staffs.designationId')->whereIn('staffs.udid', $staff)->where('globalCodes.name', 'LVN')->count();
            $leadMA = Staff::join('globalCodes', 'globalCodes.id', '=', 'staffs.designationId')->whereIn('staffs.udid', $staff)->where('globalCodes.name', 'MA - Lead')->count();
            $ma = Staff::join('globalCodes', 'globalCodes.id', '=', 'staffs.designationId')->whereIn('staffs.udid', $staff)->where('globalCodes.name', 'MA')->count();
            $MALiaison = Staff::join('globalCodes', 'globalCodes.id', '=', 'staffs.designationId')->whereIn('staffs.udid', $staff)->where('globalCodes.name', 'MA Liaison')->count();
            $CNALiaison = Staff::join('globalCodes', 'globalCodes.id', '=', 'staffs.designationId')->whereIn('staffs.udid', $staff)->where('globalCodes.name', 'CNA Liaison')->count();

            $comLeadLVN = GroupComposition::join('globalCodes', 'globalCodes.id', '=', 'groupCompositions.designationId')->where([['groupId', $group->groupId], ['globalCodes.name', 'LVN - Lead']])->first();
            $comLVN = GroupComposition::join('globalCodes', 'globalCodes.id', '=', 'groupCompositions.designationId')->where([['groupId', $group->groupId], ['globalCodes.name', 'LVN']])->first();
            $comLeadMA = GroupComposition::join('globalCodes', 'globalCodes.id', '=', 'groupCompositions.designationId')->where([['groupId', $group->groupId], ['globalCodes.name', 'MA - Lead']])->first();
            $comMA = GroupComposition::join('globalCodes', 'globalCodes.id', '=', 'groupCompositions.designationId')->where([['groupId', $group->groupId], ['globalCodes.name', 'MA']])->first();
            $comMALiaison = GroupComposition::join('globalCodes', 'globalCodes.id', '=', 'groupCompositions.designationId')->where([['groupId', $group->groupId], ['globalCodes.name', 'MA Liaison']])->first();
            $comCNALiaison = GroupComposition::join('globalCodes', 'globalCodes.id', '=', 'groupCompositions.designationId')->where([['groupId', $group->groupId], ['globalCodes.name', 'CNA Liaison']])->first();

            if ($comLeadLVN) {
                $comLeadLVNCount = $comLeadLVN->count;
            } else {
                $comLeadLVNCount = 0;
            }
            if ($comLVN) {
                $comLVNCount = $comLVN->count;
            } else {
                $comLVNCount = 0;
            }
            if ($comLeadMA) {
                $comLeadMACount = $comLeadMA->count;
            } else {
                $comLeadMACount = 0;
            }
            if ($comMA) {
                $comMACount = $comMA->count;
            } else {
                $comMACount = 0;
            }
            if ($comMALiaison) {
                $comMALiaisonCount = $comMALiaison->count;
            } else {
                $comMALiaisonCount = 0;
            }
            if ($comCNALiaison) {
                $comCNALiaisonCount = $comCNALiaison->count;
            } else {
                $comCNALiaisonCount = 0;
            }
            if ($leadLVN >= $comLeadLVNCount && $lvn >= $comLVNCount && $leadMA >= $comLeadMACount && $ma >= $comMACount && $MALiaison >= $comMALiaisonCount && $CNALiaison >= $comCNALiaisonCount) {
                foreach ($staff as $staffId) {
                    $udid = Str::uuid()->toString();
                    $groupId = $group->groupId;
                    $staffId = Helper::entity('staff', $staffId);
                    $createdBy = Auth::id();
                    DB::select('CALL createStaffGroup("' . $udid . '","' . $provider . '","' . $providerLocation . '","' . $staffId . '","' . $groupId . '","' . $createdBy . '")');
                }
                return response()->json(['message' => trans('messages.createdSuccesfully')]);
            } else {
                return response()->json(['staff' => array(trans('messages.groupComposition'))], 422);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Staff Group
    public function deleteStaffGroup($request, $id, $staffGroupId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $group = Group::where('udid', $id)->first();
            $staffGroup = StaffGroup::where('udid', $staffGroupId)->first();
            $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'deletedAt' => Carbon::now(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            StaffGroup::where([['groupId', $group->groupId], ['staffGroupId', $staffGroup->staffGroupId]])->update($input);
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Group Program
    public function groupProgramList($request, $id)
    {
        try {
//            $data = "";
            $group = Group::where('udid', $id)->first();
            if (empty($group)) {
                $group = Group::get();
                if (!empty($group)) {
                    $group = $group[0];
                }
            }
            $data = DB::select('CALL groupProgramList("' . $group->groupId . '")');
//            if (!empty($data)) {
//                $data = $data;
//            }
//            $data = DB::select('CALL groupProgramList("' . $group->groupId . '")');
//            if (!empty($data)) {
//                $data = $data;
//            }
            return fractal()->collection($data)->transformWith(new GroupProgramTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Group Program
    public function creategroupProgram($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $group = Group::where('udid', $id)->first();
            $programs = $request->program;
            foreach ($programs as $programId) {
                $udid = Str::uuid()->toString();
                $groupId = $group->groupId;
                $programId = Helper::entity('program', $programId);
                $createdBy = Auth::id();
                DB::select('CALL createGroupProgram("' . $udid . '","' . $provider . '","' . $providerLocation . '","' . $groupId . '","' . $programId . '","' . $createdBy . '")');
            }
            return response()->json(['message' => trans('messages.createdSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Group Program
    public function deleteGroupProgram($request, $id, $groupProgramId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $group = Group::where('udid', $id)->first();
            $groupProgram = GroupProgram::where('udid', $groupProgramId)->first();
            $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'deletedAt' => Carbon::now(), 'providerId' => $provider, 'provderLocationId' => $providerLocation];
            GroupProgram::where([['groupId', $group->groupId], ['groupProgramId', $groupProgram->groupProgramId]])->update($input);
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Group Provider
    public function groupProviderList($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $group = Group::where('udid', $id)->first();
            $data = DB::select('CALL groupProviderList("' . $group->groupId . '","' . $provider . '","' . $providerLocation . '")');
            return fractal()->collection($data)->transformWith(new GroupProviderTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Group Provider
    public function createGroupProvider($request, $id)
    {
        try {
            $group = Group::where('udid', $id)->first();
            $providers = $request->provider;
            foreach ($providers as $providerId) {
                $udid = Str::uuid()->toString();
                $groupId = $group->groupId;
                $providerId = Helper::entity('provider', $providerId);
                $createdBy = Auth::id();
                DB::select('CALL createGroupProvider("' . $udid . '","' . $groupId . '","' . $providerId . '","' . $createdBy . '")');
            }
            return response()->json(['message' => trans('messages.createdSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Group Provider
    public function deleteGroupProvider($request, $id, $groupProviderId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $group = Group::where('udid', $id)->first();
            $groupProvider = GroupProvider::where('udid', $groupProviderId)->first();
            $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'deletedAt' => Carbon::now(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            GroupProvider::where([['groupId', $group->groupId], ['groupProviderId', $groupProvider->groupProviderId]])->update($input);
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Group Permission
    public function groupPermissionList($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $group = Group::where('udid', $id)->first();
            $data = DB::select('CALL groupPermissionList("' . $group->groupId . '","' . $provider . '","' . $providerLocation . '")');
            return fractal()->collection($data)->transformWith(new GroupPermissionTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Group Permission
    public function createGroupPermission($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $group = Group::where('udid', $id)->first();
            $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'deletedAt' => Carbon::now()];
            GroupPermission::where('groupId', $group->groupId)->update($input);
            $actions = $request->action;
            foreach ($actions as $actionId) {
                $udid = Str::uuid()->toString();
                $groupId = $group->groupId;
//                $actionId = $actionId;
                $createdBy = Auth::id();
                DB::select('CALL createGroupPermission("' . $udid . '","' . $provider . '","' . $providerLocation . '","' . $groupId . '","' . $actionId . '","' . $createdBy . '")');
            }
            return response()->json(['message' => trans('messages.createdSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Program Provider
    public function programProviderList($request, $id)
    {
        try {
            $program = Program::where('udid', $id)->first();
            $data = ProviderProgram::select('providerPrograms.*')->join('providers', 'providers.id', '=', 'providerPrograms.providerId')
                ->where('providerPrograms.programId', $program->id);

            // $data->leftJoin('providers', 'providers.id', '=', 'providerPrograms.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'providerPrograms.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('providerPrograms.providerLocationId', '=', 'providerLocations.id')->where('providerPrograms.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('providerPrograms.providerLocationId', '=', 'providerLocationStates.id')->where('providerPrograms.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('providerPrograms.providerLocationId', '=', 'providerLocationCities.id')->where('providerPrograms.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('providerPrograms.providerLocationId', '=', 'subLocations.id')->where('providerPrograms.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('providerPrograms.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['providerPrograms.providerLocationId', $providerLocation], ['providerPrograms.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['providerPrograms.providerLocationId', $providerLocation], ['providerPrograms.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['providerPrograms.providerLocationId', $providerLocation], ['providerPrograms.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['providerPrograms.providerLocationId', $providerLocation], ['providerPrograms.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['providerPrograms.programId', $program], ['providerPrograms.entityType', $entityType]]);
            // }
            $data = $data->get();
            return fractal()->collection($data)->transformWith(new ProgramProviderTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Group Composition
    public function groupCompositionAdd($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $group = Group::where('udid', $id)->first();
            $input = ['updatedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1];
            GroupComposition::where('groupId', $group->groupId)->delete();
            foreach ($request->composition as $value) {
                $compositionData = GroupComposition::where([['groupId', $group->groupId], ['designationId', $value['designation']]])->first();
                if (!$compositionData) {
                    $input = [
                        "udid" => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'groupId' => $group->groupId, 'designationId' => $value['designation'],
                        'count' => $value['count'], 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                    ];
                    GroupComposition::create($input);
                } else {
                    return response()->json(['message' => array(trans('messages.commposition'))]);
                }
            }
            if ($request->input('patientCount')) {
                Group::where('udid', $id)->update(['patientCount' => $request->input('patientCount')]);
            }
            $data = GroupComposition::where('groupId', $group->groupId)->get();
            $resp = fractal()->collection($data)->transformWith(new GroupCompositionDetailTransformer())->toArray();
            $message = ["message" => trans('messages.createdSuccesfully')];
            $endData = array_merge($message, $resp);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Group Composition
    public function groupCompositionList($request, $id)
    {
        try {
            $group = Group::where('udid', $id)->first();
            $data = GroupComposition::select('groupCompositions.*')->where('groupId', $group->groupId)->select('groupCompositions.*');

            // $data->leftJoin('providers', 'providers.id', '=', 'groupCompositions.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'groupCompositions.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('groupCompositions.providerLocationId', '=', 'providerLocations.id')->where('groupCompositions.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('groupCompositions.providerLocationId', '=', 'providerLocationStates.id')->where('groupCompositions.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('groupCompositions.providerLocationId', '=', 'providerLocationCities.id')->where('groupCompositions.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('groupCompositions.providerLocationId', '=', 'subLocations.id')->where('groupCompositions.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('groupCompositions.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['groupCompositions.providerLocationId', $providerLocation], ['groupCompositions.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['groupCompositions.providerLocationId', $providerLocation], ['groupCompositions.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['groupCompositions.providerLocationId', $providerLocation], ['providerPrograms.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['groupCompositions.providerLocationId', $providerLocation], ['groupCompositions.entityType', 'subLocation']]);
            //     }
            // }
            $data = $data->get();
            return fractal()->collection($data)->transformWith(new GroupCompositionDetailTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Group Widget
    public function groupWidgetAdd($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $group = Group::where('udid', $id)->first();
            $input = ['updatedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1];
            GroupWidget::where('groupId', $group->groupId)->delete();
            foreach ($request->widgets as $value) {
                $input = ["udid" => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'groupId' => $group->groupId, 'widgetId' => $value, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                GroupWidget::create($input);
            }
            $data = GroupWidget::where('groupId', $group->groupId)->get();
            $resp = fractal()->collection($data)->transformWith(new GroupWidgetTransformer())->toArray();
            $message = ["message" => trans('messages.createdSuccesfully')];
            return array_merge($message, $resp);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Group Widget
    public function groupWidgetList($request, $id)
    {
        try {
            $group = Group::where('udid', $id)->first();
            $data = GroupWidget::where('groupId', $group->groupId)->select('groupWidgets.*');

            // $data->leftJoin('providers', 'providers.id', '=', 'groupWidgets.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'groupWidgets.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('groupWidgets.providerLocationId', '=', 'providerLocations.id')->where('groupWidgets.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('groupWidgets.providerLocationId', '=', 'providerLocationStates.id')->where('groupWidgets.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('groupWidgets.providerLocationId', '=', 'providerLocationCities.id')->where('groupWidgets.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('groupWidgets.providerLocationId', '=', 'subLocations.id')->where('groupWidgets.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('groupWidgets.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['groupWidgets.providerLocationId', $providerLocation], ['groupWidgets.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['groupWidgets.providerLocationId', $providerLocation], ['groupWidgets.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['groupWidgets.providerLocationId', $providerLocation], ['providerPrograms.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['groupWidgets.providerLocationId', $providerLocation], ['groupWidgets.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['groupWidgets.programId', $program], ['groupWidgets.entityType', $entityType]]);
            // }
            $data = $data->get();
            return fractal()->collection($data)->transformWith(new GroupWidgetTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
