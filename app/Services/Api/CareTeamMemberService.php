<?php

namespace App\Services\Api;

use App\Helper;
use App\Models\Client\CareTeam;
use App\Models\Client\CareTeamMember;
use App\Models\Contact\Contact;
use App\Models\User\User;
use App\Transformers\CareTeamTransformer\CareTeamMemberTransformer;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\Program\Program;
use Illuminate\Support\Facades\Auth;
use App\Models\Client\AssignProgram\AssignProgram;

class CareTeamMemberService
{

    public function addMember($request)
    {
        try {
            $careTeamId = Helper::tableName('App\Models\Client\CareTeam', $request->careTeamId);
            $careTeam = CareTeam::find($careTeamId);
            if (!$careTeam) {
                return response()->json(['message' => trans('messages.UUID_NOT_FOUND')], 404);
            }
            return (new CareTeamService)->addMember($request, $careTeam);

        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function careTeamMemberList($request, $id)
    {
        try {
            $CareTeamMember = CareTeamMember::whereNotNull('id');
            if (!$id) {
                $CareTeamMember = $CareTeamMember->orderByDesc('id')->paginate(env('PER_PAGE', 20));
                return fractal()->collection($CareTeamMember)->transformWith(new CareTeamMemberTransformer())->toArray();
            }
            $CareTeamMember = $CareTeamMember->where('udid', $id)->first();
            return fractal()->item($CareTeamMember)->transformWith(new CareTeamMemberTransformer())->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function listCareTeamMemberByContactId($request, $id)
    {
        try {
            $contactId = Helper::tableName('App\Models\Contact\Contact', $id);
            $Contact = Contact::find($contactId);
            if (!$Contact) {
                return response()->json(['message' => trans('messages.UUID_NOT_FOUND')], 404);
            }
            $CareTeamMember = CareTeamMember::where(['contactId' => $contactId]);
            $CareTeamMember = $CareTeamMember->orderByDesc('id')->paginate(env('PER_PAGE', 20));
            return fractal()->collection($CareTeamMember)->transformWith(new CareTeamMemberTransformer())->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function listCareTeamMemberByCareTeamId($request, $id)
    {
        try {
            $careTeamId = Helper::tableName('App\Models\Client\CareTeam', $id);
            $careTeam = CareTeam::find($careTeamId);
            if (!$careTeam) {
                return response()->json(['message' => trans('messages.UUID_NOT_FOUND')], 404);
            }
            $CareTeamMember = CareTeamMember::with('contact')
                ->where(['careTeamId' => $careTeamId]);
            $CareTeamMember = $CareTeamMember->orderByDesc('id')->paginate(env('PER_PAGE', 20));
            return fractal()->collection($CareTeamMember)->transformWith(new CareTeamMemberTransformer())->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function deleteCareTeamMember($request, $id)
    {
        try {

            $input = $this->deleteInputs();
            $CareTeamMember = CareTeamMember::where(['udid' => $id])->first();
            if (!$CareTeamMember) {
                return response()->json(['message' => trans('messages.UUID_NOT_FOUND')], 404);
            }
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'care_teams', 'tableId' => $CareTeamMember->id,
                'value' => json_encode($input), 'type' => 'deleted', 'ip' => request()->ip(), 'createdBy' => Auth::id(),
            ];
            $log = new ChangeLog();
            $log->makeLog($changeLog);
            $CareTeamMember = new CareTeamMember();
            AssignProgram::where(['referenceId' => $CareTeamMember->udid, 'entityType' => 'CareTeamMember'])->delete();
            $CareTeamMember = $CareTeamMember->dataSoftDelete($id, $input);
            if (!$CareTeamMember) {
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
}
