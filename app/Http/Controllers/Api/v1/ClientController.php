<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Services\Api\ClientService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\ClientRequest;

class ClientController extends Controller
{
    public function addDefaultClient()
    {
        $request = new \Illuminate\Http\Request();
        //  $request->replace(['foo' => 'bar']);
        //dd($request->foo);
        $request->setMethod('POST');

        $data = '{"phoneNumber":"1234567890","legalName":"Virtare","friendlyName":"Virtare","npi":"1234567890","addressLine1":"Virtare","addressLine2":"","city":"NY","stateId":36,"zipCode":"84601","startDate":"1675189801","endDate":"1680201001","contractTypeId":432,"programs":["yE7jm5oJA2","5nXbDZrEkU","5zI65GTfLS","tvzoEoYAMW","uwUlLAHUOO","1Np1zogdA5","1Np1zogdA7"],"contactPerson":{"title":"Admin","firstName":"Virtare","middleName":"","lastName":"Health","email":"virtare@yopmail.com","phoneNumber":"4567891233","specializationId":416,"roleId":"6be0a453-1442-426a-af4f-bff79222dc22","timeZoneId":"641b4ec6-78a3-48c2-a821-5cb04f02ad60"}}';
        $request->request->add(json_decode($data, true));
        return (new ClientService)->addDefaultClient($request);
    }

    public function addClient(ClientRequest $request)
    {
        return (new ClientService)->clientAdd($request);
    }

    public function listClient(Request $request, $id = null)
    {
        return (new ClientService)->clientList($request, $id);
    }

    public function updateClient(ClientRequest $request, $id)
    {
        return (new ClientService)->clientUpdate($request, $id);
    }

    public function program(Request $request, $entity, $id)
    {
        return (new ClientService)->programList($request, $entity, $id);
    }

    public function deleteClient(Request $request, $id)
    {
        return (new ClientService)->clientDelete($request, $id);
    }

    public function updateStatus(Request $request, $id)
    {
        return (new ClientService)->statusUpdate($request, $id);
    }

    public function unSuspendClient(Request $request, $id)
    {
        return (new ClientService)->unSuspendClient($request, $id);
    }

    public function get_patients(Request $request, $id)
    {
        return (new ClientService)->get_patients($request, $id);
    }

    public function getAllAddress(Request $request, $id)
    {
        return (new ClientService)->getAllAddress($request, $id);
    }

}
