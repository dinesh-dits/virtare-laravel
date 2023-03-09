<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\AccessRoleService;

class AccessRoleController extends Controller
{
    // List Access Role
    public function index()
    {
        return (new AccessRoleService)->index();
    }

    // Assigned Roles
    public function assignedRoles($id = null)
    {
        return (new AccessRoleService)->assignedRoles($id);
    }

    // Assigned Role Action Staff
    public function assignedRoleAction(Request $request, $id = null )
    {
      
        return (new AccessRoleService)->assignedRoleAction($request,$id) ;
    }

    // Assigned Role Action Group
    public function assignedRoleActionGroup(Request $request, $id = null)
    {
        return (new AccessRoleService)->assignedRoleActionGroup($request, $id);
    }

    // Merge Permission
    public function mergePermission(Request $request)
    {
        return (new AccessRoleService)->mergePermission($request);
    }
}
