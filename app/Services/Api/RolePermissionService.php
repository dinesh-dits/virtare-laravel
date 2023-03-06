<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\Module\Module;
use App\Models\Role\AccessRole;
use App\Models\Role\Role;
use App\Models\UserRole\UserRole;
use Illuminate\Support\Facades\DB;
use App\Models\Widget\WidgetAccess;
use Illuminate\Support\Facades\Auth;
use App\Models\RolePermission\RolePermission;
use App\Transformers\Role\RoleListTransformer;
use App\Transformers\RolePermission\RolePerTransformer;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Transformers\RolePermission\PermissionTransformer;
use App\Transformers\RolePermission\RolePermissionTransformer;

class RolePermissionService
{

    // List Role
    public function getAllRoles($request,$id){
        try{
            $roles = Role::all();
            $rolesData = array();
            $i=0;
          /*  foreach($roles as $key=>$role){
               $role = Role::find($role->id);
               $role->update(['udid'=>Str::uuid()->toString()]);
               $role->save();
            }*/
            $excludeArray[]='SuperAdmin';
            $excludeArray[]='Patient';
            $excludeArray[]='FamilyMember';           
            if($id == 1){
                array_push($excludeArray,"Non System User");  
            }
            foreach($roles as $key=>$role){
                if( !in_array($role->roles, $excludeArray)){
                    $rolesData[$i]['udid'] = $role->udid;
                    $rolesData[$i]['name'] = $role->roles;
                    $i++;
                }
            }
            return response()->json(['data' =>$rolesData], 200);
        }catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
    public function roleList($request, $id)
    {
        try {
            $data = AccessRole::select('accessRoles.*')->with('roleType');

            // $data->leftJoin('providers', 'providers.id', '=', 'accessRoles.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'accessRoles.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('accessRoles.providerLocationId', '=', 'providerLocations.id')->where('accessRoles.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('accessRoles.providerLocationId', '=', 'providerLocationStates.id')->where('accessRoles.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('accessRoles.providerLocationId', '=', 'providerLocationCities.id')->where('accessRoles.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('accessRoles.providerLocationId', '=', 'subLocations.id')->where('accessRoles.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('accessRoles.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['accessRoles.providerLocationId', $providerLocation], ['accessRoles.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['accessRoles.providerLocationId', $providerLocation], ['accessRoles.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['accessRoles.providerLocationId', $providerLocation], ['accessRoles.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['accessRoles.providerLocationId', $providerLocation], ['accessRoles.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['accessRoles.programId', $program], ['accessRoles.entityType', $entityType]]);
            // }
            if (!$id) {
                $data->select('accessRoles.*')
                    ->leftJoin('globalCodes as g1', 'g1.id', '=', 'accessRoles.roleTypeId');
                   // ->leftJoin('globalCodes as g2', 'g2.id', '=', 'accessRoles.levelId');
                if ($request->search) {
                    $data->where([['accessRoles.roles', 'LIKE', '%' . $request->search . '%']])
                        ->orWhere([['g1.name', 'LIKE', '%' . $request->search . '%']])
                        ->orWhere([['g2.name', 'LIKE', '%' . $request->search . '%']]);
                }
                if ($request->orderField == 'roleType') {
                    $data->orderBy('g1.name', $request->orderBy);
                } elseif ($request->orderField == 'name') {
                    $data->orderBy('accessRoles.roles', $request->orderBy);
                } elseif ($request->orderField == 'description') {
                    $data->orderBy('accessRoles.roleDescription', $request->orderBy);
                } else {
                    $data->orderBy('accessRoles.roles', 'ASC');
                }
                if ($request->all) {
                    $data = $data->get();
                    return fractal()->collection($data)->transformWith(new RoleListTransformer())->toArray();
                } else {
                    $data = $data->paginate(env('PER_PAGE', 20));
                    return fractal()->collection($data)->transformWith(new RoleListTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
                }
            } else {
                $data = $data->where('accessRoles.udid', $id)->first();
                return fractal()->item($data)->transformWith(new RoleListTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Role
    public function createRole($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $role = [
                'udid' => Str::uuid()->toString(),
                'roles' => $request->input('name'),
                'roleDescription' => $request->input('description'),
                'roleTypeId' => '147',
                'levelId' => $request->input('level'),
            ];
            $data = AccessRole::create($role);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'accessRoles', 'tableId' => $data->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($role), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            $roleData = AccessRole::where('id', $data->id)->first();
            $message = ["message" => trans('messages.createdSuccesfully')];
            $resp = fractal()->item($roleData)->transformWith(new RoleListTransformer())->toArray();
            $endData = array_merge($message, $resp);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Role
    public function updateRole($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $role = AccessRole::where('udid', $id)->first();
            $roleId = $role->id;
            if (($roleId == 1)) {
                return response()->json(['message' => 'unauthorized']);
            } else {
                $role = array();
                if (!empty($request->input('name'))) {
                    $role['roles'] = $request->input('name');
                }
                if (!empty($request->input('description'))) {
                    $role['roleDescription'] = $request->input('description');
                }
                if (!empty($request->input('level'))) {
                    $role['levelId'] = $request->input('level');
                }
                if (empty($request->input('isActive'))) {
                    $role['isActive'] = 0;
                } else {
                    $role['isActive'] = 1;
                }
                $role['updatedBy'] = Auth::id();
                $role['providerId'] = $provider;
                $role['providerLocationId'] = $providerLocation;
                if (!empty($role)) {
                    AccessRole::where('id', $roleId)->update($role);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'accessRoles', 'tableId' => $roleId, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($role), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                }
            }
            return response()->json(['message' => trans('messages.updatedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Role
    public function deleteRole($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $role = AccessRole::where('udid', $id)->first();
            $roleId = $role->id;
            if (($roleId == 1)) {
                return response()->json(['message' => 'unauthorized']);
            } else {
                $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'deletedAt' => Carbon::now(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                $tables = [
                    UserRole::where('accessRoleId', $roleId),
                    WidgetAccess::where('accessRoleId', $roleId),
                    RolePermission::where('accessRoleId', $roleId),
                    AccessRole::where('id', $roleId),
                ];
                foreach ($tables as $table) {
                    $table->update($input);
                }
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'accessRoles', 'tableId' => $roleId, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
                return response()->json(['message' => "Deleted Successfully"]);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Role Permission
    public function createRolePermission($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $role = AccessRole::where('udid', $id)->first();
            $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'deletedAt' => Carbon::now(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            RolePermission::where('accessRoleId', $role->id)->update($input);
            $permission = RolePermission::where('accessRoleId', $role->id)->first();
            if ($permission) {
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'rolePermissions', 'tableId' => $permission->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
            }
            $action = $request->actions;
            foreach ($action as $actionId) {
                $udid = Str::uuid()->toString();
                $accessRoleId = $role->id;
                $actionId = $actionId;
                $createdBy = Auth::id();
                DB::select('CALL createRolePermission("' . $provider . '","' . $udid . '","' . $accessRoleId . '","' . $actionId . '","' . $createdBy . '")');
            }
            return response()->json(['message' => trans('messages.createdSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Role and Permission List with Access Role Id
    public function rolePermissionList($request, $id)
    {
        try {
            $data = RolePermission::select('rolePermissions.*')->with('role', 'action');

            // $data->leftJoin('providers', 'providers.id', '=', 'rolePermissions.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'rolePermissions.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('rolePermissions.providerLocationId', '=', 'providerLocations.id')->where('rolePermissions.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('rolePermissions.providerLocationId', '=', 'providerLocationStates.id')->where('rolePermissions.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('rolePermissions.providerLocationId', '=', 'providerLocationCities.id')->where('rolePermissions.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('rolePermissions.providerLocationId', '=', 'subLocations.id')->where('rolePermissions.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('rolePermissions.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['rolePermissions.providerLocationId', $providerLocation], ['rolePermissions.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['rolePermissions.providerLocationId', $providerLocation], ['rolePermissions.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['rolePermissions.providerLocationId', $providerLocation], ['rolePermissions.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['rolePermissions.providerLocationId', $providerLocation], ['rolePermissions.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['rolePermissions.programId', $program], ['rolePermissions.entityType', $entityType]]);
            // }
            $role = AccessRole::where('udid', $id)->first();
            $data = $data->where('rolePermissions.accessRoleId', $role->id)->get();
            $array = ['role' => fractal()->collection($data)->transformWith(new RolePermissionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray()];
            return $array;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Mudule List
    public function permissionsList($request)
    {
        try {
            $data = Module::select('modules.*')->with('screens');


            // $data->leftJoin('providers', 'providers.id', '=', 'modules.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'modules.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('modules.providerLocationId', '=', 'providerLocations.id')->where('modules.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('modules.providerLocationId', '=', 'providerLocationStates.id')->where('modules.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('modules.providerLocationId', '=', 'providerLocationCities.id')->where('modules.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('modules.providerLocationId', '=', 'subLocations.id')->where('modules.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('modules.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['modules.providerLocationId', $providerLocation], ['modules.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['modules.providerLocationId', $providerLocation], ['modules.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['modules.providerLocationId', $providerLocation], ['modules.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['modules.providerLocationId', $providerLocation], ['modules.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['modules.programId', $program], ['modules.entityType', $entityType]]);
            // }
            $data = $data->get();
            $array = ['modules' => fractal()->collection($data)->transformWith(new PermissionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray()];
            return $array;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Role and Permission List Access Role
    public function rolePermissionEdit($id)
    {
        try {
            $role = AccessRole::where('udid', $id)->first();
            if (isset($role->id)) {
                $data = DB::select('CALL rolePermissionListing(' . $role->id . ')');
                return fractal()->collection($data)->transformWith(new RolePerTransformer())->toArray();
            } else {
                return response()->json(['message' => "Invalid Role Id."], 402);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
