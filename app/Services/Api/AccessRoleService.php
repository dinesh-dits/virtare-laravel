<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use App\Models\Group\Group;
use App\Models\Staff\Staff;
use Illuminate\Support\Facades\DB;
use App\Transformers\AccessRoles\AccessRoleTransformer;
use App\Transformers\AccessRoles\AssignedRolesTransformer;

class AccessRoleService
{

    // List Access Role
    public function index()
    {
        try {
            $data = DB::select(
                'CALL accessRolesList()',
            );
            return fractal()->collection($data)->transformWith(new AccessRoleTransformer())->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Assigned Roles
    public function assignedRoles($id)
    {
        try {
            if ($id) {
                $staff = Helper::entity('staff', $id);
            } else {

                if (isset(auth()->user()->staff->id)) {
                    $staff = auth()->user()->staff->id;
                } else {
                    $staff = "";
                }
            }

            if (!empty($staff)) {
                $data = DB::select(
                    'CALL assignedRolesList(' . $staff . ')',
                );
            } else {
                $data = [];
            }
            return fractal()->collection($data)->transformWith(new AssignedRolesTransformer())->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Assigned Role Action Staff
    public function assignedRoleAction($request,$id)
    {
        try {         
            if ($id) {
                $staff = Helper::entity('staff', $id);
            } else {
                if (isset(auth()->user()->staff->id)) {
                    $staff = auth()->user()->staff->id;
                } else {
                    $staff = "";
                }
            }
            if (empty($request->actionId)) {
                $actionIdx = '';
            } else {
                $actionIdx = json_encode($request->actionId);
            }
            if (!empty($staff)) {
                if ($actionIdx != "") {
                    $actions = DB::select(
                        "CALL assignedRolesActionsList('" . $staff . "'," . $actionIdx . ")",
                    );
                    $widgets = DB::select(
                        "CALL assignedRolesWidgetsList('" . $staff . "'," . $actionIdx . ")",
                    );
                } else {
                    $actions = DB::select(
                        "CALL assignedRolesActionsList('" . $staff . "','" . $actionIdx . "')",
                    );
                    $widgets = DB::select(
                        "CALL assignedRolesWidgetsList('" . $staff . "','" . $actionIdx . "')",
                    );                    
                }
            } else {
                $actions = [];
                $widgets = [];
            }
            $daat = [
                'actionId' => $actions,
                'widgetId' => $widgets,
            ];
            return $daat;
        } catch (\Exception $e) {            
            throw new \RuntimeException($e);
        }
    }

    // List Assigned Role Action Group
    public function assignedRoleActionGroup($id, $request)
    {
        try {
            $group = Group::where('udid', $id)->first();
            if (empty($request->actionId)) {
                $actionIdx = '';
            } else {
                $actionIdx = json_encode($request->actionId);
            }
            if (!empty($group)) {

                $actions = DB::select(
                    "CALL assignedRolesActionsGroupList('" . $group->groupId . "','" . $actionIdx . "')",
                );
                $widgets = DB::select(
                    "CALL assignedRolesWidgetsGroupList('" . $group->groupId . "','" . $actionIdx . "')",
                );
            } else {
                $actions = [];
                $widgets = [];
            }
            $daat = [
                'actionId' => $actions,
                'widgetId' => $widgets,
            ];
            return $daat;
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Merge Permission
    public function mergePermission($request)
    {
        try {
            $group = Group::where('udid', $request->group)->first();
            $staff = Staff::where('udid', $request->staff)->first();
            if (empty($request->actionId)) {
                $actionIdx = '';
            } else {
                $actionIdx = json_encode($request->actionId);
            }
            $actions = DB::select(
                "CALL mergePermissions('" . $staff->id . "','" . $group->groupId . "','" . $actionIdx . "')",
            );
            $widgets = DB::select(
                "CALL mergeWidgets('" . $staff->id . "','" . $group->groupId . "','" . $actionIdx . "')",
            );
            $daat = [
                'actionId' => $actions,
                'widgetId' => $widgets,
            ];
            return $daat;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
