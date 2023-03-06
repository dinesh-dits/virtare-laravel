<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\FamilyMemberService;

class FamilyMemberController extends Controller
{
    // List Patient
    public function listPatient(Request $request, $id = null)
    {
        return (new FamilyMemberService)->listPatient($request, $id);
    }
}
