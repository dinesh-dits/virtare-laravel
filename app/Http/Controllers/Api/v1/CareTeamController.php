<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CareTeam\CareTeamRequest;
use App\Http\Requests\CareTeam\CareTeamUpdateRequest;
use App\Services\Api\CareTeamService;
use Illuminate\Http\Request;

class CareTeamController extends Controller
{

    public function createCareTeam(CareTeamRequest $request)
    {
        return (new CareTeamService())->careTeamCreate($request);
    }

    public function listCareTeam(Request $request, $id = null)
    {
        return (new CareTeamService())->careTeamList($request, $id);
    }

    public function listCareTeamBySiteId(Request $request, $id)
    {
        return (new CareTeamService())->careTeamListBySiteId($request, $id);
    }

    public function careTeamListByClientId(Request $request, $id)
    {
        return (new CareTeamService())->careTeamListByClientId($request, $id);
    }

    public function updateCareTeam(CareTeamUpdateRequest $request, $id)
    {
        return (new CareTeamService())->careTeamUpdate($request, $id);
    }

    public function deleteCareTeam(Request $request, $id)
    {
        return (new CareTeamService())->careTeamDelete($request, $id);
    }
}
