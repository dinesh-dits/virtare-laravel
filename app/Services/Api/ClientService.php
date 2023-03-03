<?php

namespace App\Services\Api;

use App\Helper;
use Carbon\Carbon;
use App\Models\Role\Role;
use Illuminate\Support\Str;
use App\Models\Client\Client;
use App\Models\Log\ChangeLog;
use App\Models\Contact\Contact;
use App\Models\Program\Program;
use App\Models\Client\Site\Site;
use App\Models\Dashboard\Timezone;
use App\Services\Api\PeopleService;
use Illuminate\Support\Facades\Auth;
use App\Models\Patient\PatientProvider;
use App\Transformers\Client\ClientTransformer;
use App\Models\Client\AssignProgram\AssignProgram;
use App\Transformers\Client\ClientDetailTransformer;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Models\Patient\PatientProgram;
use App\Models\Patient\PatientFlag;
use App\Models\Staff\Staff;

class ClientService
{
    public function addDefaultClient($request)
    {
        try {
            //$request= json_decode($request,true);
            //  print_r($request->all());die;
            $startDate = Helper::dateOnly($request->startDate);
            $endDate = Helper::dateOnly($request->endDate);
            $input = $request->only(
                'legalName',
                'friendlyName',
                'npi',
                'addressLine1',
                'addressLine2',
                'city',
                'stateId',
                'zipCode',
                'contractTypeId',
                'fax',
                'phoneNumber'
            );
            $other = [
                'createdBy' => Auth::id(),
                'startDate' => $startDate,
                'endDate' => $endDate,
                'udid' => Str::uuid()->toString(),
                'statusId' => 427
            ];
            $data = array_merge($input, $other);
            $clientData = new Client();
            $clientData = $clientData->clientAdd($data);
            if (!$clientData) {
                return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
            if (isset($request->programs)) {
                $programData = [];
                $programArray = Program::whereIn('udid', $request->programs)->get('id');
                foreach ($programArray as $key => $program) {
                    $programData[$key] = ['entityType' => 'Client', 'referenceId' => $clientData->udid, 'programId' => $program->id];
                }
                $AssignProgram = new AssignProgram();
                $AssignProgram = $AssignProgram->addData($programData);
                if (!$AssignProgram) {
                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                }
            }
            if (isset($request->contactPerson)) {
                $contactPerson = $request->input('contactPerson');
                $roleDetail = Role::where('udid', $contactPerson['roleId'])->first();
                $timeZone = Timezone::where('udid', $contactPerson['timeZoneId'])->first();
                if (isset($clientData->id) && !empty($clientData->id)) {
                    if (isset($roleDetail->id) && !empty($roleDetail->id)) {
                        if ($roleDetail->id == 11) {
                            $clientService = new ClientService();
                            $clientService->contactAdd($request, $clientData->id);
                        } else {
                            $timeZoneId = (!empty($timeZone) && isset($timeZone)) ? $timeZone->id : NULL;
                            $data = new PeopleService();
                            $data = $data->peopleAdd($request, $clientData->id, $roleDetail->id, $timeZoneId, 1);
                            if (!$data) {
                                return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                            }
                        }
                    } else {
                        return response()->json(['message' => trans('messages.INVALID_ROLE')]);
                    }
                } else {
                    return response()->json(['message' => trans('messages.404')]);
                }
            }
            Staff::query()->update(['clientId' => $clientData->id]);
            return true;
            /*  $client = Client::where(['id' => $clientData->id])->first();
            $userData = fractal()->item($client)->transformWith(new ClientTransformer())->toArray();
            $message = ['message' => trans('messages.createdSuccesfully')];
            return array_merge($message, $userData);*/
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function clientAdd($request)
    {
        try {
            $ipInfo = file_get_contents('http://ip-api.com/json/' . $request->ip());
            $ipInfo = json_decode($ipInfo);
            $timezone = 'UTC';
            if (isset($ipInfo->timezone)) {
                $timezone = $ipInfo->timezone;
            }
            $startDate = Helper::dateOnly($request->startDate, $timezone);
            $endDate = Helper::dateOnly($request->endDate, $timezone);
            $input = $request->only(
                'legalName',
                'friendlyName',
                'npi',
                'addressLine1',
                'addressLine2',
                'city',
                'stateId',
                'zipCode',
                'contractTypeId',
                'fax',
                'phoneNumber'
            );
            $other = [
                'createdBy' => Auth::id(),
                'startDate' => $startDate,
                'endDate' => $endDate,
                'udid' => Str::uuid()->toString(),
                'statusId' => 427
            ];
            $data = array_merge($input, $other);
            $clientData = new Client();
            $clientData = $clientData->clientAdd($data);
            if (!$clientData) {
                return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
            if (isset($request->programs)) {
                $programData = [];
                $programArray = Program::whereIn('udid', $request->programs)->get('id');
                foreach ($programArray as $key => $program) {
                    $programData[$key] = ['entityType' => 'Client', 'referenceId' => $clientData->udid, 'programId' => $program->id];
                }
                $AssignProgram = new AssignProgram();
                $AssignProgram = $AssignProgram->addData($programData);
                if (!$AssignProgram) {
                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                }
            }
            if (isset($request->contactPerson)) {
                $contactPerson = $request->input('contactPerson');
                $roleDetail = Role::where('udid', $contactPerson['roleId'])->first();
                $timeZone = Timezone::where('udid', $contactPerson['timeZoneId'])->first();
                if (isset($clientData->udid) && !empty($clientData->udid)) {
                    if (isset($roleDetail->id) && !empty($roleDetail->id)) {
                        if ($roleDetail->id == 11) {
                            $clientService = new ClientService();
                            $clientService->contactAdd($request, $clientData->udid);
                        } else {
                            $timeZoneId = (!empty($timeZone) && isset($timeZone)) ? $timeZone->id : NULL;
                            $data = new PeopleService();
                            $data = $data->peopleAdd($request, $clientData->udid, $roleDetail->id, $timeZoneId, 1);
                            if (!$data) {
                                return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                            }
                        }
                    } else {
                        return response()->json(['message' => trans('messages.INVALID_ROLE')]);
                    }
                } else {
                    return response()->json(['message' => trans('messages.404')]);
                }
            }
            $client = Client::where(['id' => $clientData->id])->first();
            $userData = fractal()->item($client)->transformWith(new ClientTransformer())->toArray();
            $message = ['message' => trans('messages.createdSuccesfully')];
            return array_merge($message, $userData);
        } catch (\Exception $e) {
            echo $e->getMessage() . '+' . $e->getFile() . '+' . $e->getLine();
            //  throw new \RuntimeException($e);
        }
    }

    public function clientList($request, $id)
    {
        try {
            $client = Client::select('clients.*')->with('status');
            if (!$id) {
                if ($request->orderField == 'statusName') {
                    $client->leftJoin('globalCodes as g1', 'g1.id', '=', 'clients.statusId')->orderBy('g1.name', $request->orderBy);
                } elseif ($request->orderField == 'friendlyName') {
                    $client->orderBy('clients.friendlyName', $request->orderBy);
                } elseif ($request->orderField == 'contractTypeName') {
                    $client->leftJoin('globalCodes as g2', 'g2.id', '=', 'clients.contractTypeId')->orderBy('g2.name', $request->orderBy);
                } elseif ($request->orderField == 'city') {
                    $client->orderBy('clients.city', $request->orderBy);
                } else {
                    $client->orderBy('clients.createdAt', 'DESC');
                }
                $client = $client->paginate(env('PER_PAGE', 20));
                return fractal()->collection($client)->transformWith(new ClientTransformer())->paginateWith(new IlluminatePaginatorAdapter($client))->toArray();
            }
            $client = $client->where('udid', $id)->first();
            if (!$client) {
                return response()->json(['message' => trans('messages.404')]);
            }
            return fractal()->item($client)->transformWith(new ClientDetailTransformer())->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function clientUpdate($request, $id)
    {
        try {
            $data = Client::where(['udid' => $id])->first();
            if (!$data) {
                return response()->json(['message' => trans('messages.UUID_NOT_FOUND')], 404);
            }
            $client = $this->clientRequestInputs($request);
            if (isset($request->programs)) {
                AssignProgram::where(['referenceId' => $id, 'entityType' => 'Client'])->delete();
                $programData = [];
                $programArray = Program::whereIn('udid', $request->programs)->get('id');
                foreach ($programArray as $key => $program) {
                    $programData[$key] = ['entityType' => 'Client', 'referenceId' => $id, 'programId' => $program->id];
                }
                $AssignProgram = new AssignProgram();
                $AssignProgram = $AssignProgram->addData($programData);
                if (!$AssignProgram) {
                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                }
            }
            $clientObj = new Client();
            $clientObj = $clientObj->updateClient($id, $client);
            if (!$clientObj) {
                return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
            $data = Client::where(['udid' => $id])->first();
            $userData = fractal()->item($data)->transformWith(new ClientTransformer())->toArray();
            $message = ['message' => trans('messages.updatedSuccesfully')];
            return array_merge($message, $userData);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function statusUpdate($request, $id)
    {
        try {
            $data = Client::where(['udid' => $id])->first();
            if (!$data) {
                return response()->json(['message' => trans('messages.UUID_NOT_FOUND')], 404);
            }
            $client = [];
            $client['statusId'] = $request->statusId;
            $client['updatedBy'] = Auth::id();
            $clientObj = new Client();
            $clientObj = $clientObj->updateClient($id, $client);

            if (!$clientObj) {
                return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
            $data = Client::where(['udid' => $id])->first();
            return fractal()->item($data)->transformWith(new ClientTransformer())->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function unSuspendClient($request, $id)
    {
        try {
            $data = Client::where(['udid' => $id])->first();
            if (!$data) {
                return response()->json(['message' => trans('messages.UUID_NOT_FOUND')], 404);
            }
            $client = [];
            $client['statusId'] = 427;
            $client['isActive'] = 1;
            $client['updatedBy'] = Auth::id();
            $clientObj = new Client();
            $clientObj = $clientObj->updateClient($id, $client);

            if (!$clientObj) {
                return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
            $data = Client::where(['udid' => $id])->first();
            return fractal()->item($data)->transformWith(new ClientTransformer())->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function clientDelete($request, $id)
    {
        try {
            $input = $this->deleteInputs();
            $client = Client::where(['udid' => $id])->first();
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'clients', 'tableId' => $client->id,
                'value' => json_encode($input), 'type' => 'deleted', 'ip' => request()->ip(), 'createdBy' => Auth::id(),
            ];
            $log = new ChangeLog();
            $log->makeLog($changeLog);
            Site::where(['clientId' => $client->id])->update($input);
            $Client = new Client();
            $Client = $Client->dataSoftDelete($id, $input);
            if (!$Client) {
                return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function deleteInputs(): array
    {
        return ['isActive' => 0, 'isDelete' => 1, 'deletedBy' => Auth::id(), 'deletedAt' => Carbon::now()];
    }

    // Update Common Update Function Client
    public function clientRequestInputs($request): array
    {

        $client = array();
        if ($request->legalName) {
            $client['legalName'] = $request->legalName;
        }
        if ($request->friendlyName) {
            $client['friendlyName'] = $request->friendlyName;
        }
        if ($request->npi) {
            $client['npi'] = $request->npi;
        }
        if ($request->addressLine1) {
            $client['addressLine1'] = $request->addressLine1;
        }
        if ($request->addressLine2) {
            $client['addressLine2'] = $request->addressLine2;
        }
        if ($request->city) {
            $client['city'] = $request->city;
        }
        if ($request->stateId) {
            $client['stateId'] = $request->stateId;
        }
        if ($request->input('statusId', null)) {
            $client['statusId'] = $request->input('statusId', null);
        }
        if ($request->phoneNumber) {
            $client['phoneNumber'] = $request->phoneNumber;
        }
        if ($request->zipCode) {
            $client['zipCode'] = $request->zipCode;
        }
        if ($request->contractTypeId) {
            $client['contractTypeId'] = $request->contractTypeId;
        }
        if ($request->startDate) {
            $ipInfo = file_get_contents('http://ip-api.com/json/' . $request->ip());
            $ipInfo = json_decode($ipInfo);
            $timezone = 'UTC';
            if (isset($ipInfo->timezone)) {
                $timezone = $ipInfo->timezone;
            }
            $startDate = Helper::dateOnly($request->startDate, $timezone);
            $client['startDate'] = $startDate;
        }
        if ($request->endDate) {
            $ipInfo = file_get_contents('http://ip-api.com/json/' . $request->ip());
            $ipInfo = json_decode($ipInfo);
            $timezone = 'UTC';
            if (isset($ipInfo->timezone)) {
                $timezone = $ipInfo->timezone;
            }
            $endDate = Helper::dateOnly($request->endDate, $timezone);
            $client['endDate'] = $endDate;
        }
        $client['updatedBy'] = Auth::id();
        return $client;
    }

    // Update Common Update Function Contact
    public function contactRequestInputs($data): array
    {
        $client = array();
        if ($data['firstName']) {
            $client['firstName'] = $data['firstName'];
        }
        if ($data['middleName']) {
            $client['middleName'] = $data['middleName'];
        }
        if ($data['lastName']) {
            $client['lastName'] = $data['lastName'];
        }
        if ($data['phoneNumber']) {
            $client['phoneNumber'] = $data['phoneNumber'];
        }
        if ($data['email']) {
            $client['email'] = $data['email'];
        }
        $client['updatedBy'] = Auth::id();
        return $client;
    }

    // Add Contact
    public function contactAdd($request, $id)
    {
        $contact = $request->input('contactPerson') ? $request->input('contactPerson') : $request->all();
        $specialization = (!empty($contact['specializationId'])) ? $contact['specializationId'] : NULL;
        $roleDetail = Role::where('udid', $contact['roleId'])->first();
        $timeZone = Timezone::where('udid', $contact['timeZoneId'])->first();
        if (isset($roleDetail->id) && !empty($roleDetail->id)) {
            $roleId = $roleDetail->id;
        } else {
            return response()->json(['message' => trans('messages.INVALID_ROLE')]);
        }
        if (isset($timeZone->id) && !empty($timeZone->id)) {
            $timeZoneId = $timeZone->id;
        } else {
            return response()->json(['message' => trans('messages.INVALID_TIMEZONE')]);
        }
        $other = [
            'referenceId' => $id,
            'entityType' => 'Client',
            'firstName' => $contact['firstName'],
            'middleName' => $contact['middleName'],
            'lastName' => $contact['lastName'],
            'specializationId' => $specialization,
            'timeZoneId' => $timeZoneId,
            'phoneNumber' => $contact['phoneNumber'],
            'email' => $contact['email'],
            'title' => $contact['title'],
            'roleId' => $roleId,
            'udid' => Str::uuid()->toString()
        ];
        $contact = new Contact();
        $contact = $contact->contactAdd($other);
        if (!$contact) {
            return 0; //response()->json(['message' => trans('messages.INTERNAL_ERROR')]);
        } else {
            return $contact->id;
        }
    }

    // Client Programs
    public function programList($request, $entity, $id)
    {
        try {
            if (!empty($entity == 'site')) {
                $entityType = 'Site';
                // $entityTypeId = Helper::tableName('App\Models\Client\Site\Site', $id);
            } else {
                $entityType = 'Client';
                // $entityTypeId = Helper::tableName('App\Models\Client\Client', $id);
            }
            $listData = array();
            $peoples = AssignProgram::where(['referenceId' => $id, 'entityType' => $entityType])->get('programId');
            $program = Program::whereIn('id', $peoples)->get();
            foreach ($program as $key => $people) {
                $listData[$key]['udid'] = $people->udid;
                $listData[$key]['code'] = $people->code;
                $listData[$key]['name'] = $people->name;
            }
            if (count($listData) > 0) {
                $key = $key + 1;
            } else {
                $key = 0;
            }
            return response()->json(['data' => $listData], 200);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function get_patients($request, $id)
    {
        try {
            $aptients = array();
            $sites = array();
            $CareTeam = array();
            $CareTeamId = array();
            $clients = Client::with(['sites', 'teams'])->where(['udid' => $id])->first();
            if (!$clients) {
                return response()->json(['message' => trans('messages.404')]);
            }
            if ($clients->count() > 0) {
                // foreach($clients as $key=>$client){
                if ($clients->sites) {
                    foreach ($clients->sites as $key => $site) {
                        $sites[$site->udid] = $site->friendlyName;
                    }
                }
                if ($clients->teams) {
                    foreach ($clients->teams as $key => $team) {
                        $CareTeam[$team->udid]['name'] = $team->name;
                        $CareTeam[$team->udid]['site'] = $team->siteId;
                        $CareTeamId[$key] = $team->udid;
                    }
                }
                if (0 < count($CareTeamId)) {
                    $patients = PatientProvider::with('patients')->whereIn('providerId', $CareTeamId)->get();
                    $count = 1;
                    foreach ($patients as $key => $patient) {
                        if (isset($patient->patients)) {
                            $flag = $this->patientFlag($patient->patients->id);
                            $flag = explode('@', $flag);
                        } else {
                            $flag = array();
                        }
                        $aptients[$key]['status'] = isset($flag[0]) ? $flag[0] : '';
                        $aptients[$key]['statusAlt'] = isset($flag[1]) ? $flag[1] : '';
                        $aptients[$key]['name'] = $patient->patients->firstName . ' ' . $patient->patients->lastName;
                        $aptients[$key]['join_date'] = strtotime($patient->patients->createdAt);
                        $aptients[$key]['site'] = $sites[$CareTeam[$patient->providerId]['site']];
                        $aptients[$key]['careteam'] = $CareTeam[$patient->providerId]['name'];
                        $aptients[$key]['complience'] = ($patient->patients->nonCompliance == 0) ? 'No' : 'Yes';
                        $aptients[$key]['program'] = $this->getPrograms($patient->patients->id);
                        $count++;
                    }
                }
                return response()->json(['data' => $aptients]);
            } else {
                return response()->json(['message' => trans('messages.404')]);
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getPrograms($patientId)
    {
        $prgrams = PatientProgram::with('program')->where('patientId', $patientId)->get();
        $prg = array();
        foreach ($prgrams as $key => $prgram) {
            $prg[$key] = $prgram->program;
        }
        if (0 < count($prg))
            return $prg;
        // return implode(',', $prg);
        else
            return '-';
    }


    public function patientFlag($patientId)
    {
        $flag = PatientFlag::with('flag')->where('patientId', $patientId)->first();
        if (isset($flag->id)) {
            return $flag->flag->color . '@' . $flag->flag->name;
        } else {
            return '';
        }
    }

    public function getAllAddress($request, $clientId)
    {
        $clientExist = Client::where(['udid' => $clientId])->first('id');
        if (!isset($clientExist->id)) {
            return response()->json(['message' => trans('messages.404')]);
        }
        $client = [];
        $sites = [];
        $client = Client::query()
            ->with(['state' => function ($query) {
                $query->select('id', 'udid', 'iso');
            }])->where(['udid' => $clientId])
            ->select('addressLine1', 'addressLine2', 'stateId', 'city', 'zipCode')
            ->get()->toArray();
        $sites = Site::query()
            ->with(['state' => function ($query) {
                $query->select('id', 'udid', 'iso');
            }])->where(['clientId' => $clientId, 'virtual' => 2])
            ->select('addressLine1', 'addressLine2', 'stateId', 'city', 'zipCode')->get()->toArray();
        $data = array_merge($client, $sites);
        if (!count($data)) {
            return [];
        }
        $address = [];
        foreach ($data as $key => $value) {
            if ($key == 0) {
                $address[0]['udid'] = 1;
                $address[0]['name'] = 'add new address';
            }
            $key++;
            $address[$key]['fullAddress'] = $value['addressLine1'] . ', ' . $value['city'] . ', ' . $value['state']['iso'] . '-' . $value['zipCode'];
            $address[$key]['fullAddressOnHover'] = $value['addressLine1'] . ((!empty($value['addressLine2']) ? ', ' : '')) . $value['addressLine2'] . ', ' . $value['city'] . ', ' . $value['state']['iso'] . ', ' . $value['zipCode'];
            $address[$key]['addressLine1'] = $value['addressLine1'];
            $address[$key]['addressLine2'] = $value['addressLine2'];
            $address[$key]['stateId'] = $value['state']['id'];
            $address[$key]['stateIso'] = $value['state']['iso'];
            $address[$key]['city'] = $value['city'];
            $address[$key]['zipCode'] = $value['zipCode'];
        }
        $address = array_map("unserialize", array_unique(array_map("serialize", $address)));
        return array_values($address);
    }
}
