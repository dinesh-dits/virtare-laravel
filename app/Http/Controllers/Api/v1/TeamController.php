<?php

namespace App\Http\Controllers\Api\v1;


use Illuminate\Http\Request;
use App\Services\Api\TeamService;
use App\Http\Controllers\Controller;


class TeamController extends Controller
{
    // Staff Team
    public function team(Request $request, $patientId = null, $type, $id = null)
    {
        return (new TeamService)->team($request, $patientId, $type, $id);
    }

    // Patient Team
    public function all(Request $request, $patientId  = null)
    {
        return  [
            "data" => [
                "staff" => (new TeamService)->team($request, $patientId, "staff", null),
                "physician" => (new TeamService)->team($request, $patientId, "physician", null),
                "familyMember" => (new TeamService)->team($request, $patientId, "familyMember", null)
            ]
        ];
    }
}
