<?php

namespace App\Services\Api;

use App\Events\SetUpPasswordEvent;
use App\Helper;
use App\Models\Client\CareTeam;
use App\Models\Client\CareTeamMember;
use App\Models\Client\Client;
use App\Models\Client\Site\Site;
use App\Models\Dashboard\Timezone;
use App\Models\Role\Role;
use App\Models\Staff\Staff;
use App\Models\User\User;
use App\Transformers\CareTeamTransformer\CareTeamListTransformer;
use App\Transformers\CareTeamTransformer\CareTeamTransformer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\Program\Program;
use Illuminate\Support\Facades\Auth;
use App\Models\Client\AssignProgram\AssignProgram;
use App\Models\Role\AccessRole;
use App\Models\UserRole\UserRole;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class CareTeamService
{

    public function careTeamCreate($request)
    {
        DB::beginTransaction();
        try {
            $siteId = Helper::tableName('App\Models\Client\Site\Site', $request->siteId);
            $clientId = Helper::tableName('App\Models\Client\Client', $request->clientId);
            $site = Site::find($siteId);
            $client = Client::find($clientId);
            if (!$site || !$client) {
                return response()->json(['message' => trans('messages.UUID_NOT_FOUND')], 404);
            }
            $input = $request->only('name');
            $other = [
                'createdBy' => Auth::id(),
                'udid' => Str::uuid()->toString(),
                'siteId' => $request->input('siteId'),
                'clientId' => $request->input('clientId'),
            ];
            $data = array_merge($input, $other);
            $careTeam = new CareTeam();
            $careTeam = $careTeam->storeData($data);
            if ($request->input('programs')) {
                $programData = [];
                $programArray = Program::whereIn('udid', $request->programs)->get('id');
                foreach ($programArray as $key => $program) {
                    $programData[$key] = ['entityType' => 'CareTeam', 'referenceId' => $careTeam->udid, 'programId' => $program->id];
                }
                $AssignProgram = new AssignProgram();
                $AssignProgram = $AssignProgram->addData($programData);
                if (!$AssignProgram) {
                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                }
            }
            if ($careTeam) {
                $setSiteHeadAsMember = $this->setSiteHeadAsMember($site, $careTeam);
                $setData = $this->addMemberTeamHead($request, $careTeam);
                if (!$setData) {
                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                }
                DB::commit();
                return response()->json(['message' => trans('messages.CREATED_SUCCESS')]);
            }
            return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \RuntimeException($e);
        }
    }

    public function careTeamList($request, $id)
    {
        try {
            $CareTeam = CareTeam::select('care_teams.*')->with('site');
            if (!$id) {
                if ($request->orderField == 'name') {
                    $CareTeam->orderBy('care_teams.name', $request->orderBy);
                } elseif ($request->orderField == 'teamHead') {
                    $CareTeam->leftJoin('care_team_members', 'care_team_members.careTeamId', '=', 'care_teams.udid')
                        ->leftJoin('staffs', 'staffs.userId', '=', 'care_team_members.contactId')->orderBy('staffs.title', $request->orderBy);
                } else {
                    $CareTeam->orderBy('care_teams.createdAt', 'DESC');
                }
                $CareTeam = $CareTeam->paginate(env('PER_PAGE', 20));
                return fractal()->collection($CareTeam)->transformWith(new CareTeamListTransformer())->toArray();
            }
            $cT = CareTeam::where(['udid' => $id])->first();
            if (!$cT) {
                return response()->json(['message' => trans('messages.UUID_NOT_FOUND')], 404);
            }
            $CareTeam = $CareTeam->where(['id' => $cT->id])->first();
            return fractal()->item($CareTeam)->transformWith(new CareTeamTransformer())->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function careTeamListBySiteId($request, $id)
    {
        try {
            $siteId = Helper::tableName('App\Models\Client\Site\Site', $id);
            $site = Site::find($siteId);
            if (!$site) {
                return response()->json(['message' => trans('messages.UUID_NOT_FOUND')], 404);
            }
            $CareTeam = CareTeam::where(['siteId' => $id]);
            $CareTeam = $CareTeam->orderByDesc('id')->paginate(env('PER_PAGE', 20));
            return fractal()->collection($CareTeam)->transformWith(new CareTeamTransformer())->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function careTeamListByClientId($request, $id)
    {
        try {
            $clientId = Helper::tableName('App\Models\Client\Client', $id);
            if (!$clientId) {
                return response()->json(['message' => trans('messages.UUID_NOT_FOUND')], 404);
            }
            $CareTeam = CareTeam::with('head')->where(['clientId' => $id]);

            $CareTeam = $CareTeam->orderByDesc('id')->paginate(env('PER_PAGE', 20));
            //  print_r($CareTeam);
            return fractal()->collection($CareTeam)->transformWith(new CareTeamListTransformer())
                ->paginateWith(new IlluminatePaginatorAdapter($CareTeam))->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function careTeamUpdate($request, $id)
    {
        DB::beginTransaction();
        try {
            $siteId = Helper::tableName('App\Models\Client\Site\Site', $request->siteId);
            $data = CareTeam::where(['udid' => $id])->first();
            $dataCareTeam = CareTeam::where(['udid' => $id])->first();
            if (!$data) {
                return response()->json(['message' => trans('messages.UUID_NOT_FOUND')], 404);
            }
            $reqData = $this->RequestInputs($request);
            $other = [
                'siteId' => $request->input('siteId'),
            ];
            $reqData = array_merge($reqData, $other);
            if (isset($request->programs)) {
                AssignProgram::where(['referenceId' => $id, 'entityType' => 'CareTeam'])->delete();
                $programData = [];
                $programArray = Program::whereIn('udid', $request->programs)->get('id');
                foreach ($programArray as $key => $program) {
                    $programData[$key] = ['entityType' => 'CareTeam', 'referenceId' => $id, 'programId' => $program->id];
                }
                $AssignProgram = new AssignProgram();
                $AssignProgram = $AssignProgram->addData($programData);
                if (!$AssignProgram) {
                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                }
            }
            $CareTeam = new CareTeam();
            $CareTeam = $CareTeam->updateCareTeam($id, $reqData);
            if (!$CareTeam) {
                return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }

            $setData = $this->addMemberTeamHead($request, $dataCareTeam);
            if (!$setData) {
                return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
            DB::commit();
            return response()->json(['message' => trans('messages.DATA_UPDATED')]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \RuntimeException($e);
        }
    }

    public function careTeamDelete($request, $id)
    {
        try {
            $input = $this->deleteInputs();
            $CareTeam = CareTeam::where(['udid' => $id])->first();
            if (!$CareTeam) {
                return response()->json(['message' => trans('messages.UUID_NOT_FOUND')], 404);
            }
            $careId = $CareTeam->id;
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'care_teams', 'tableId' => $CareTeam->id,
                'value' => json_encode($input), 'type' => 'deleted', 'ip' => request()->ip(), 'createdBy' => Auth::id(),
            ];
            $log = new ChangeLog();
            $log->makeLog($changeLog);
            $CareTeam = new CareTeam();
            $CareTeamMember = new CareTeamMember();
            AssignProgram::where(['referenceId' => $id, 'entityType' => 'CareTeam'])->delete();
            $CareTeam = $CareTeam->dataSoftDelete($id, $input);
            $CareTeamMember->dataSoftDeleteByCareTeamId($careId, $input);
            if (!$CareTeam) {
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

    public function RequestInputs($request): array
    {
        $data = array();
        if ($request->name) {
            $data['name'] = $request->name;
        }
        $data['updatedBy'] = Auth::id();
        return $data;
    }

    public function addMemberTeamHead($request, $dataCareTeam)
    {
        if ($request->teamHeadId === 0) {
            if ($request->contactPerson) {
                $timeZone = Timezone::where('udid', $request->contactPerson['timeZoneId'])->first();
                $roleDetail = Role::where('udid', $request->contactPerson['roleId'])->first();
                $password = Str::random("10");
                $user = [
                    'udid' => Str::uuid()->toString(),
                    'email' => $request->contactPerson['email'],
                    'password' => Hash::make($password),
                    'emailVerify' => 1,
                    'createdBy' => Auth::id(),
                    'roleId' => @$roleDetail->id,
                    'timeZoneId' => @$timeZone->id,
                ];
                $data = new User();
                $data = $data->userAdd($user);
                if (!$data) {
                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                }
                // $client = Helper::tableName('App\Models\Client\Client', $request->clientId);
                $input = [
                    'firstName' => $request->contactPerson['firstName'], 'title' => $request->contactPerson['title'], 'userId' => $data->id,
                    'middleName' => $request->contactPerson['middleName'], 'lastName' => $request->contactPerson['lastName'], 'clientId' => $request->input('clientId'),
                    'phoneNumber' => $request->contactPerson['phoneNumber'], 'udid' => Str::uuid()->toString()
                ];
                $people = new Staff();
                $people = $people->peopleAdd($input);
                $emailData = [
                    'email' => $request->contactPerson['email'],
                    'firstName' => $request->contactPerson['firstName'],
                    'template_name' => 'welcome_email'
                ];
                event(new SetUpPasswordEvent($emailData));
                $accessRole = AccessRole::where('roleId', $roleDetail->id)->first();
                if (isset($accessRole->id) && !empty($accessRole->id)) {
                    $user_role = array();
                    $user_role['udid'] = Str::uuid()->toString();
                    $user_role['accessRoleId'] = $accessRole->id;
                    $user_role['staffId'] = $people->id;
                    UserRole::create($user_role);
                }
                //set password event for mail
                if (!$people) {
                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                }
                // event(new SetUpPasswordEvent($request, $data));
                if ($data->id) {
                    $data = [
                        'createdBy' => Auth::id(),
                        'udid' => Str::uuid()->toString(),
                        'contactId' => $data->id,
                        'careTeamId' => $dataCareTeam->udid,
                        'isHead' => 1
                    ];
                    CareTeamMember::where(['careTeamId' => $dataCareTeam->udid])->update(['isHead' => 0]);
                    $careTeamMember = new CareTeamMember();
                    $careTeamMember = $careTeamMember->storeData($data);
                    if (!$careTeamMember) {
                        return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                    }
                    return $careTeamMember;
                }
            } else {
                return 1;
            }
        } else {
            CareTeamMember::where(['careTeamId' => $dataCareTeam->udid])->update(['isHead' => 0]);
            $staff = Staff::where('udid', $request->teamHeadId)->first();
            if (!$staff) {
                return response()->json(['message' => trans('messages.UUID_NOT_FOUND')], 404);
            }
            $checkExist = CareTeamMember::where(['careTeamId' => $dataCareTeam->udid, 'contactId' => $staff->userId])->first();
            if (isset($checkExist->id) && $checkExist->id) {
                CareTeamMember::where(['careTeamId' => $dataCareTeam->udid, 'contactId' => $staff->userId])->update(['isHead' => 1]);
                return 1;
            }
            $data = [
                'createdBy' => Auth::id(),
                'udid' => Str::uuid()->toString(),
                'contactId' => $staff->userId,
                'careTeamId' => $dataCareTeam->udid,
                'isHead' => 1
            ];
            $careTeamMember = new CareTeamMember();
            $careTeamMember = $careTeamMember->storeData($data);
            return $careTeamMember;
        }
    }

    public function addMember($request, $dataCareTeam)
    {

        if ($request->teamHeadId === 0) {
            if ($request->contactPerson) {
                $timeZone = Timezone::where('udid', $request->contactPerson['timeZoneId'])->first();
                $roleDetail = Role::where('udid', $request->contactPerson['roleId'])->first();
                $password = Str::random("10");
                $user = [
                    'udid' => Str::uuid()->toString(),
                    'email' => $request->contactPerson['email'],
                    'password' => Hash::make($password),
                    'emailVerify' => 1,
                    'createdBy' => Auth::id(),
                    'roleId' => @$roleDetail->id,
                    'timeZoneId' => @$timeZone->id,
                ];
                $data = new User();
                $data = $data->userAdd($user);
                if (!$data) {
                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                }
                $input = [
                    'firstName' => $request->contactPerson['firstName'], 'title' => $request->contactPerson['title'], 'userId' => $data->id,
                    'middleName' => $request->contactPerson['middleName'], 'lastName' => $request->contactPerson['lastName'], 'clientId' => $request->clientId,
                    'phoneNumber' => $request->contactPerson['phoneNumber'], 'udid' => Str::uuid()->toString()
                ];
                $people = new Staff();
                $people = $people->peopleAdd($input);
                $accessRole = AccessRole::where('roleId', $roleDetail->id)->first();
                if (isset($accessRole->id) && !empty($accessRole->id)) {
                    $user_role = array();
                    $user_role['udid'] = Str::uuid()->toString();
                    $user_role['accessRoleId'] = $accessRole->id;
                    $user_role['staffId'] = $people->id;
                    UserRole::create($user_role);
                }
                //set password event for mail
                if (!$people) {
                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                }
                $emailData = [
                    'email' => $request->contactPerson['email'],
                    'firstName' => $request->contactPerson['firstName'],
                    'template_name' => 'welcome_email'
                ];
                event(new SetUpPasswordEvent($emailData));
                if ($data->id) {
                    $data = [
                        'createdBy' => Auth::id(),
                        'udid' => Str::uuid()->toString(),
                        'contactId' => $data->id,
                        'careTeamId' => $dataCareTeam->udid,
                        'isHead' => 0
                    ];
                    $careTeamMember = new CareTeamMember();
                    $careTeamMember = $careTeamMember->storeData($data);
                    if (!$careTeamMember) {
                        return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                    }
                    if ($careTeamMember) {
                        return response()->json(['message' => trans('messages.CREATED_SUCCESS')]);
                    }
                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                }
            } else {
                return 1;
            }
        } else {
            $staff = Staff::where('udid', $request->teamHeadId)->first();
            if (!$staff) {
                return response()->json(['message' => trans('messages.UUID_NOT_FOUND')], 404);
            }
            $checkExist = CareTeamMember::where(['careTeamId' => $dataCareTeam->udid, 'contactId' => $staff->userId])->first();
            if (isset($checkExist->id) && $checkExist->id) {
                return response()->json(['message' => trans('messages.ALREADY_EXIST')], 223);
            }
            $data = [
                'createdBy' => Auth::id(),
                'udid' => Str::uuid()->toString(),
                'contactId' => $staff->userId,
                'careTeamId' => $dataCareTeam->udid,
                'isHead' => 0
            ];
            $careTeamMember = new CareTeamMember();
            $careTeamMember = $careTeamMember->storeData($data);
            if ($careTeamMember) {
                return response()->json(['message' => trans('messages.CREATED_SUCCESS')]);
            }
            return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
        }
    }

    public function createUser()
    {
    }

    public function setSiteHeadAsMember($site, $careTeam)
    {
        $data = [
            'createdBy' => Auth::id(),
            'udid' => Str::uuid()->toString(),
            'contactId' => $site->siteHead,
            'careTeamId' => $careTeam->udid,
            'isHead' => 0
        ];
        return (new CareTeamMember())->storeData($data);
    }

    public static function setUpdatedSiteHeadAsMember($siteId, $headId)
    {
        $careTeams = CareTeam::where(['siteId' => $siteId])->get('udid')->toArray();
        $careTeamMembers = CareTeamMember::whereIn('careTeamId', $careTeams)
            ->where(['contactId' => $headId])->get('careTeamId as udid')->toArray();
        $careTeams_array = [];
        $careTeamMembers_array = [];
        foreach ($careTeams as $key => $element) {
            $careTeams_array[$key] = $element['udid'];
        }
        foreach ($careTeamMembers as $key => $element) {
            $careTeamMembers_array[$key] = $element['udid'];
        }
        $result = array_diff($careTeams_array, $careTeamMembers_array);
        $data = [];
        if (count($result)) {
            foreach ($result as $key => $value) {
                $data[$key] = [
                    'createdBy' => Auth::id(),
                    'udid' => Str::uuid()->toString(),
                    'contactId' => $headId,
                    'careTeamId' => $value,
                    'isHead' => 0
                ];
            }
            return (new CareTeamMember())->insertData($data);
        }
        return true;
    }
}
