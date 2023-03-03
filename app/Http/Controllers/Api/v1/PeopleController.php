<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Services\Api\PeopleService;
use App\Http\Controllers\Controller;
use App\Http\Requests\People\PeopleRequest;

class PeopleController extends Controller
{
    public function createPeople(PeopleRequest $request)
    {
        return (new PeopleService)->peopleCreate($request);
    }

    public function listPeople($id = null)
    {
        return (new PeopleService)->peopleList($id);
    }

    public function detailPeople($id = null)
    {
        return (new PeopleService)->detailPeople($id);
    }

    public function listuser($id, $type = NULL)
    {
        return (new PeopleService)->listuser($id, $type);
    }

    public function updatePeople(Request $request, $id)
    {
        return (new PeopleService)->peopleUpdate($request, $id);
    }
}
