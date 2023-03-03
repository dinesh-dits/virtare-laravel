<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Api\GroupService;

class GroupController extends Controller
{

    public function groupList(Request $request,$id=null)
    {
        return (new GroupService)->groupList($request,$id);
    }
    
    public function createGroup(Request $request)
    {
        return (new GroupService)->createGroup($request);
    }

    public function updateGroup(Request $request , $id)
    {
        return (new GroupService)->updateGroup($request,$id);
    }

    public function deleteGroup(Request $request , $id)
    {
        return (new GroupService)->deleteGroup($request,$id);
    }

    public function staffGroupList(Request $request ,$id=null)
    {
        return (new GroupService)->staffGroupList($request,$id);
    }

    public function createStaffGroup(Request $request ,$id)
    {
        return (new GroupService)->createStaffGroup($request,$id);
    }

    public function deleteStaffGroup(Request $request ,$id,$staffGroupId)
    {
        return (new GroupService)->deleteStaffGroup($request,$id,$staffGroupId);
    }

    public function groupProgramList(Request $request ,$id=null)
    {
        return (new GroupService)->groupProgramList($request,$id);
    }

    public function creategroupProgram(Request $request, $id)
    {
        return (new GroupService)->creategroupProgram($request,$id);
    }

    public function deleteGroupProgram(Request $request, $id,$groupProgramId)
    {
        return (new GroupService)->deleteGroupProgram($request,$id,$groupProgramId);
    }

    public function groupProviderList(Request $request, $id)
    {
        return (new GroupService)->groupProviderList($request,$id);
    }

    public function createGroupProvider(Request $request, $id)
    {
        return (new GroupService)->createGroupProvider($request,$id);
    }

    public function deleteGroupProvider(Request $request, $id,$groupProviderId)
    {
        return (new GroupService)->deleteGroupProvider($request,$id,$groupProviderId);
    }

    public function groupPermissionList(Request $request, $id)
    {
        return (new GroupService)->groupPermissionList($request,$id);
    }

    public function createGroupPermission(Request $request, $id)
    {
        return (new GroupService)->createGroupPermission($request,$id);
    }

    public function programProviderList(Request $request, $id)
    {
        return (new GroupService)->programProviderList($request,$id);
    }

    public function addGroupComposition(Request $request, $id)
    {
        return (new GroupService)->groupCompositionAdd($request,$id);
    }

    public function listGroupComposition(Request $request, $id)
    {
        return (new GroupService)->groupCompositionList($request,$id);
    }

    public function addGroupWidget(Request $request, $id)
    {
        return (new GroupService)->groupWidgetAdd($request,$id);
    }

    public function listGroupWidget(Request $request, $id)
    {
        return (new GroupService)->groupWidgetList($request,$id);
    }
}
