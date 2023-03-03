<?php

namespace App\Services\Api;

use App\Helper;
use Carbon\Carbon;
use App\Models\Role\Role;
use App\Models\Staff\Staff;
use Illuminate\Support\Str;
use App\Models\Client\Client;
use App\Models\Log\ChangeLog;
use App\Models\Program\Program;
use App\Models\Client\Site\Site;
use App\Models\Dashboard\Timezone;
use App\Services\Api\ClientService;
use App\Services\Api\PeopleService;
use Illuminate\Support\Facades\Auth;
use App\Transformers\Site\SiteTransformer;
use App\Transformers\Site\SiteArrayTransformer;
use App\Transformers\Site\SiteDeatailTransformer;
use App\Models\Client\AssignProgram\AssignProgram;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class SiteService
{

    public function siteAdd($request, $id)
    {
        try {
            $allData = $request->all();
            $client = Helper::tableName('App\Models\Client\Client', $id);
            $clientDetail = Client::where('id', $client)->first();
            $contactId = 0;
            if (isset($clientDetail->id) && !empty($clientDetail->id)) {
                if ($request->has('siteHeadId') && $request->input('siteHeadId') === 0) {
                    $contactPerson = $request->input('contactPerson');
                    $roleDetail = Role::where('udid', $contactPerson['roleId'])->first();
                    $timeZone = Timezone::where('udid', $contactPerson['timeZoneId'])->first();
                    if (isset($clientDetail->id) && !empty($clientDetail->id)) {
                        if (isset($roleDetail->id) && !empty($roleDetail->id)) {
                            if ($roleDetail->id == 11) {
                                $contactId = new ClientService();
                                $contactId = $contactId->contactAdd($request, $id);
                            } else {
                                $timeZoneId = (!empty($timeZone) && isset($timeZone)) ? $timeZone->id : NULL;
                                $data = new PeopleService();
                                $data = $data->peopleAdd($request, $id, $roleDetail->id, $timeZoneId, 0);
                                if (!$data) {
                                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                                }
                                $contact = Staff::where('id', $data)->first();
                                $contactId = $contact->userId;
                            }
                        } else {
                            return response()->json(['message' => trans('messages.INVALID_ROLE')]);
                        }
                    } else {
                        return response()->json(['message' => trans('messages.404')]);
                    }
                } else {
                    $Staffdata = Staff::where(['udid' => $request->input('siteHeadId')])->first();
                    if (isset($Staffdata->id) && !empty($Staffdata->id)) {
                        $contactId = $Staffdata->userId;
                    }
                }
                $virtual = $request->input('virtual');
                $input = $request->only('friendlyName', 'comment');
                $other = [
                    'createdBy' => Auth::id(),
                    'udid' => Str::uuid()->toString(),
                    'clientId' => $id,
                    'virtual' => $virtual,
                    'siteHead' => $contactId
                ];
                if ($virtual == 2 && $request->has('address')) {
                    $other['addressLine1'] = $allData['address']['addressLine1'];
                    $other['addressLine2'] = $allData['address']['addressLine2'];
                    $other['stateId'] = $allData['address']['stateId'];
                    $other['city'] = $allData['address']['city'];
                    $other['zipCode'] = $allData['address']['zipCode'];
                }
                $data = array_merge($input, $other);

                $siteData = new Site();
                $siteData = $siteData->siteAdd($data);
                if (!$siteData) {
                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                }

                if ($request->input('programs')) {
                    $programData = [];
                    $programArray = Program::whereIn('udid', $request->input('programs'))->get('id');
                    foreach ($programArray as $key => $program) {
                        $programData[$key] = ['entityType' => 'Site', 'referenceId' => $siteData->udid, 'programId' => $program->id];
                    }
                    $AssignProgram = new AssignProgram();
                    $AssignProgram = $AssignProgram->addData($programData);
                    if (!$AssignProgram) {
                        return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                    }
                }
                return response()->json(['message' => trans('messages.createdSuccesfully')], 201);
            } else {
                return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function siteList($request, $id, $siteId)
    {
        try {
            $site = Site::select('sites.*')->with(['head', 'status']);
            if (!$siteId) {
                $site = $site->where('sites.clientId', $id);
                if ($request->orderField == 'friendlyName') {
                    $site->orderBy('sites.friendlyName', $request->orderBy);
                } elseif ($request->orderField == 'city') {
                    $site->orderBy('sites.city', $request->orderBy);
                } elseif ($request->orderField == 'isHead') {
                    $site->leftJoin('staffs', 'staffs.userId', '=', 'sites.siteHead')->orderBy('staffs.lastName', $request->orderBy);
                } else {
                    $site->orderBy('sites.createdAt', 'DESC');
                }
                $site = $site->orderBy('id', 'desc')
                    ->paginate(env('PER_PAGE', 20));
                return fractal()->collection($site)->transformWith(new SiteTransformer())
                    ->paginateWith(new IlluminatePaginatorAdapter($site))->toArray();
            }
            $site = $site->where('udid', $siteId)->first();
            if (!$site) {
                return response()->json(['message' => trans('messages.404')]);
            }
            return fractal()->item($site)->transformWith(new SiteDeatailTransformer())->toArray();
        } catch (\Exception $e) {
            echo $e->getMessage() . '' . $e->getLine() . '' . $e->getFile();
            // throw new \RuntimeException($e);
        }
    }

    public function siteListArray($request, $id)
    {
        try {
            $site = Site::with(['head', 'status']);
            $site = $site->where('clientId', $id);
            $site = $site->orderBy('id', 'desc')->get();
            return fractal()->collection($site)->transformWith(new SiteArrayTransformer())->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function siteUpdate($request, $id, $siteId)
    {
        try {
            $allData = $request->all();
            $siteData = Site::with('assignProgram')->where('udid', $siteId)->first();
            if (isset($siteData->id) && !empty($siteData->id)) {
                if ($request->has('siteHeadId') && $request->input('siteHeadId') === 0) {
                    $contactPerson = $request->input('contactPerson');
                    $roleDetail = Role::where('udid', $contactPerson['roleId'])->first();
                    $timeZone = Timezone::where('udid', $contactPerson['timeZoneId'])->first();
                    $clientDetail = Client::where('udid', $id)->first();
                    if (isset($clientDetail->id) && !empty($clientDetail->id)) {
                        if (isset($roleDetail->id) && !empty($roleDetail->id)) {
                            if ($roleDetail->id == 11) {
                                $contactId = new ClientService();
                                $contactId = $contactId->contactAdd($request, $id);
                            } else {
                                $timeZoneId = (!empty($timeZone) && isset($timeZone)) ? $timeZone->id : NULL;
                                $data = new PeopleService();
                                $data = $data->peopleAdd($request, $id, $roleDetail->id, $timeZoneId, 0);
                                if (!$data) {
                                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                                }
                                $contact = Staff::where('id', $data)->first();
                                if (isset($contact->id) && !empty($contact->id)) {
                                    $contactId = $contact->userId;
                                }
                            }
                        } else {
                            return response()->json(['message' => trans('messages.INVALID_ROLE')]);
                        }
                    } else {
                        return response()->json(['message' => trans('messages.404')], 404);
                    }
                } else {
                    $Staffdata = Staff::where(['udid' => $request->input('siteHeadId')])->first();
                    if (isset($Staffdata->id) && !empty($Staffdata->id)) {
                        $contactId = $Staffdata->userId;
                    }
                }
                $virtual = $request->input('virtual');
                $input = $request->only('friendlyName', 'comment');
                $other = [
                    'updatedBy' => Auth::id(),
                    'virtual' => $virtual,
                ];
                if ($contactId != $siteData->getOriginal('siteHead')) {
                    $other['siteHead'] = $contactId;
                    CareTeamService::setUpdatedSiteHeadAsMember($siteId, $contactId);
                }
                if ($virtual == 2 && $request->has('address')) {
                    $other['addressLine1'] = $allData['address']['addressLine1'];
                    $other['addressLine2'] = $allData['address']['addressLine2'];
                    $other['stateId'] = $allData['address']['stateId'];
                    $other['city'] = $allData['address']['city'];
                    $other['zipCode'] = $allData['address']['zipCode'];
                }
                $data = array_merge($input, $other);
                $site = Site::updateDetails($siteData->id, $data);
                /** Update New Programs */
                $programs = array();
                $existingPrograms = array();
                if ($siteData->assignProgram) {
                    foreach ($siteData->assignProgram as $key => $assignedProgram) {
                        $existingPrograms[$key] = $assignedProgram->programId;
                    }
                }
                $programArray = Program::whereIn('udid', $request->programs)->get('id');
                if ($programArray->count() > 0) {
                    foreach ($programArray as $key => $program) {
                        $programs[] = $program->id;
                    }
                }
                $toAdd = array_diff($programs, $existingPrograms);
                $toremove = array_diff($existingPrograms, $programs);
                if (is_array($toremove) && 0 < count($toremove)) {
                    foreach ($toremove as $key => $remove) {
                        $input = $this->deleteInputs();
                        AssignProgram::where(['entityType' => 'Site', 'referenceId' => $siteData->udid, 'programId' => $remove])->update($input);
                    }
                }
                if (is_array($toAdd) && 0 < count($toAdd)) {
                    foreach ($toAdd as $key => $add) {
                        $programData[$key] = ['entityType' => 'Site', 'referenceId' => $siteData->udid, 'programId' => $add];
                    }
                    $AssignProgram = new AssignProgram();
                    $AssignProgram = $AssignProgram->addData($programData);
                }
                return response()->json(['message' => trans('messages.updatedSuccesfully')]);
            } else {
                return response()->json(['message' => trans('messages.404')]);
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function siteDelete($request, $id, $siteId)
    {
        try {
            $input = $this->deleteInputs();
            $site = Site::where(['udid' => $siteId])->first();
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'sites', 'tableId' => $site->id,
                'value' => json_encode($input), 'type' => 'deleted', 'ip' => request()->ip(), 'createdBy' => Auth::id(),
            ];
            $log = new ChangeLog();
            $log->makeLog($changeLog);
            $site = new Site();
            $site = $site->dataSoftDelete($siteId, $input);
            if (!$site) {
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

    public function siteRequestInputs($request): array
    {
        $site = array();
        if ($request->friendlyName) {
            $site['friendlyName'] = $request->friendlyName;
        }
        if ($request->addressLine1) {
            $site['addressLine1'] = $request->addressLine1;
        }
        if ($request->addressLine2) {
            $site['addressLine2'] = $request->addressLine2;
        }
        if ($request->city) {
            $site['city'] = $request->city;
        }
        if ($request->stateId) {
            $site['stateId'] = $request->stateId;
        }
        if ($request->statusId) {
            $site['statusId'] = $request->statusId;
        }
        if ($request->zipCode) {
            $site['zipCode'] = $request->zipCode;
        }
        $site['updatedBy'] = Auth::id();
        return $site;
    }
}
