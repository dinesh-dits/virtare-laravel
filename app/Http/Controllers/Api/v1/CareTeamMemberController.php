<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CareTeam\CareTeamMemberRequest;
use App\Services\Api\CareTeamMemberService;
use Illuminate\Http\Request;

class CareTeamMemberController extends Controller
{
    public function addMember(CareTeamMemberRequest $request)
    {
        return (new CareTeamMemberService())->addMember($request);
    }

    public function listCareTeamMember(Request $request, $id = null)
    {
        return (new CareTeamMemberService())->careTeamMemberList($request, $id);
    }

    public function listCareTeamMemberByContactId(Request $request, $id)
    {
        return (new CareTeamMemberService())->listCareTeamMemberByContactId($request, $id);
    }

    public function listCareTeamMemberByCareTeamId(Request $request, $id)
    {
        return (new CareTeamMemberService())->listCareTeamMemberByCareTeamId($request, $id);
    }

    public function deleteCareTeamMember(Request $request, $id)
    {
        return (new CareTeamMemberService())->deleteCareTeamMember($request, $id);
    }
}
