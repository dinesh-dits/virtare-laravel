<?php

namespace App\Services\Api;

use App\Events\SetUpPasswordEvent;
use App\Helper;
use App\Models\Contact\Contact;
use App\Models\Dashboard\Timezone;
use Exception;
use App\Models\User\User;
use App\Models\Staff\Staff;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Transformers\People\PeopleTransformer;
use App\Models\Role\Role;
use App\Models\Role\AccessRole;
use App\Models\UserRole\UserRole;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class PeopleService
{

    // Add People
    public function peopleCreate($request)
    {
        try {
            $timeZone = Timezone::where('udid', $request->input('timeZoneId'))->first();
            $roleDetail = Role::where('udid', $request->input('roleId'))->first();
            $client = Helper::tableName('App\Models\Client\Client', $request->input('clientId'));
            if (isset($client) && !empty($client)) {
                if (isset($roleDetail->id) && !empty($roleDetail->id)) {
                    if ($roleDetail->id == 47) { // Non system user
                        $clientService = new ClientService();
                        $contactId = $clientService->contactAdd($request, $request->input('clientId'));
                    } else {
                        $timeZoneId = (!empty($timeZone) && isset($timeZone)) ? $timeZone->id : NULL;
                        $data = $this->peopleAdd($request, $request->input('clientId'), $roleDetail->id, $timeZoneId, 0);
                        if (!$data) {
                            return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                        }
                    }
                } else {
                    return response()->json(['message' => trans('messages.INVALID_ROLE')]);
                }
                return response()->json(['message' => trans('messages.createdSuccesfully')]);
            } else {
                return response()->json(['message' => trans('messages.404')]);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function detailPeople($id)
    {
        try {
            $staffDetail = Staff::where('udid', $id)->with('user')->first();
            $contactDetail = Contact::where('udid', $id)->first();
            if ($staffDetail) {
                $data = $staffDetail;
            } else {
                $data = $contactDetail;
            }
            if (isset($data->id) && !empty($data->id)) {
                return fractal()->item($data)->transformWith(new PeopleTransformer())->toArray();
            }
            return response()->json(['message' => trans('messages.404')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function listuser($id, $type)
    {
        try {
            $listData = array();
            $key = 0;
            $peoples = Staff::where('clientId', $id)->get();
            $listData[$key]['udid'] = 0;
            $listData[$key]['name'] = ($type == 1) ? 'Add Site Head' : (($type == 2) ? "Add Team Member" : "Add Team Head");
            $listData[$key]['title'] = '';
            foreach ($peoples as $people) {
                $key++;
                if (!empty($people->lastName)) {
                    $fullName = str_replace("  ", " ", ucfirst($people->lastName) . ',' . ' ' . ucfirst($people->firstName) . ' ' . ucfirst($people->middleName));
                } else {
                    $fullName = str_replace("  ", " ", ucfirst($people->firstName) . ' ' . ucfirst($people->middleName));
                }
                $listData[$key]['udid'] = $people->udid;
                $listData[$key]['name'] = $fullName;
                $listData[$key]['title'] = $people->title;
            }
            return response()->json(['data' => $listData], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List People
    public function peopleList($id)
    {
        try {
            $staff = Staff::where('clientId', $id)->get();
            $contact = Contact::where(['referenceId' => $id, 'entityType' => 'Client'])->get();
            $collection = new Collection();
            $collection = $collection->merge($staff);
            $collection = $collection->merge($contact);
            $merge = $this->paginate($collection, env("PER_PAGE", 20));
            return fractal()->collection($merge)->transformWith(new peopleTransformer())
                ->paginateWith(new IlluminatePaginatorAdapter($merge))->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);

        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    // Update People
    public function peopleupdate($request, $id)
    {
        try {
            $data = Staff::where(['udid' => $id])->first();
            $contact = Contact::where(['udid' => $id])->first();
            $roleDetail = Role::where('udid', $request->input('roleId'))->first();
            $timeZone = Timezone::where('udid', $request->input('timeZoneId'))->first();
            if (isset($roleDetail->id) && !empty($roleDetail->id)) {
                if ($contact) {
                    if ($contact->roleId == 11 && $roleDetail->id != 11) {
                        $data = $this->peopleAdd($request, $contact->referenceId, $roleDetail->id, $timeZone->id, 1);
                        if (!$data) {
                            return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                        }
                        Contact::where('udid', $id)->delete();
                    } else {
                        $clientService = new ClientService();
                        $contactId = $clientService->contactRequestInputs($request);
                        Contact::where(['referenceId' => $contact->referenceId, 'entityType' => 'Client'])->update($contactId);
                    }
                } elseif ($data) {
                    if ($data->user->roleId != 11 && $roleDetail->id == 11) {
                        $clientService = new ClientService();
                        $contactId = $clientService->contactAdd($request, $data->clientId);
                        Staff::where('udid', $id)->delete();
                        User::where('id', $data->userId)->delete();
                    } else {
                        $this->peopleUpdateData($request, $data, $roleDetail, $timeZone, $id);
                    }
                } else {
                    return response()->json(['message' => trans('messages.404')]);
                }
            } else {
                return response()->json(['message' => trans('messages.INVALID_ROLE')]);
            }
            return response()->json(['message' => trans('messages.updatedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Common Update Function People
    public function peopleRequestInputs($request): array
    {
        $people = array();
        if ($request->input('title')) {
            $people['title'] = $request->input('title');
        }
        if ($request->input('firstName')) {
            $people['firstName'] = $request->input('firstName');
        }
        if ($request->input('middleName')) {
            $people['middleName'] = $request->input('middleName');
        }
        if ($request->input('lastName')) {
            $people['lastName'] = $request->input('lastName');
        }
        if ($request->input('phoneNumber')) {
            $people['phoneNumber'] = $request->input('phoneNumber');
        }
        if ($request->input('specializationId')) {
            $people['specializationId'] = $request->input('specializationId');
        }
        $people['updatedBy'] = Auth::id();
        return $people;
    }

    // Update Common Update Function User
    public function userRequestInputs($request): array
    {
        $timeZone = Timezone::where('udid', $request->input('timeZoneId'))->first();
        $people = array();
        if ($request->input('email')) {
            $people['email'] = $request->input('email');
        }
        if ($request->input('timeZoneId')) {
            $people['timeZoneId'] = $timeZone->id;
        }
        if ($request->input('roleId')) {
            $people['roleId'] = $request->input('roleId');
        }
        $people['updatedBy'] = Auth::id();
        return $people;
    }

    // Check user role
    public function peopleAdd($request, $client, $role, $timeZone, $contact)
    {
        $peopleData = $request->input('contactPerson') ? $request->input('contactPerson') : $request->all();
        $password = Str::random("10");
        $user = [
            'udid' => Str::uuid()->toString(),
            'email' => $peopleData['email'],
            'password' => Hash::make($password),
            'emailVerify' => 1,
            'createdBy' => Auth::id(),
            'roleId' => $role,
            'timeZoneId' => $timeZone,
        ];
        $data = new User();
        $data = $data->userAdd($user);
        if (!$data) {
            return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
        }

        $input = [
            'firstName' => $peopleData['firstName'], 'title' => $peopleData['title'], 'userId' => $data->id, 'isContact' => $contact,
            'middleName' => $peopleData['middleName'], 'lastName' => $peopleData['lastName'], 'clientId' => $client,
            'phoneNumber' => $peopleData['phoneNumber'], 'udid' => Str::uuid()->toString(), 'specializationId' => $peopleData['specializationId']
        ];
        $people = new Staff();
        $people = $people->peopleAdd($input);
        if (!$people) {
            return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
        }
        $accessRole = AccessRole::where('roleId', $role)->first();
        if (isset($accessRole->id) && !empty($accessRole->id)) {
            $user_role = array();
            $user_role['udid'] = Str::uuid()->toString();
            $user_role['accessRoleId'] = $accessRole->id;
            $user_role['staffId'] = $people->id;
            UserRole::create($user_role);
        }
        $emailData = [
            'email' => $peopleData['email'],
            'firstName' => $peopleData['firstName'],
            'template_name' => 'welcome_email'
        ];
        event(new SetUpPasswordEvent($emailData));
        return $people->id;
    }

    // People Update
    public function peopleUpdateData($request, $data, $roleDetail, $timeZone, $id)
    {
        $updateUser = array();
        if (isset($data->id) && !empty($data->id)) {
            $userDetail = User::where('id', $data->userId)->first();
            // if ($request->filled('email') && $request->email != $userDetail->getOriginal('email')) {
            //     $updateUser['email'] = $request->email;
            // }
            if ($request->filled('roleId') && $roleDetail->id != $userDetail->getOriginal('roleId')) {
                $updateUser['roleId'] = $roleDetail->id;
            }
            if ($request->filled('timeZoneId') && $timeZone->id != $userDetail->getOriginal('timeZoneId')) {
                $updateUser['timeZoneId'] = $timeZone->id;
            }
            if (0 < count($updateUser)) {
                $userObj = new User();
                $userObj = $userObj->updateUser($data->userId, $updateUser);
            }
            $people = $this->peopleRequestInputs($request);
            $peopleObj = new Staff();
            $peopleObj = $peopleObj->updateStaff($id, $people);
            return response()->json(['message' => trans('messages.updatedSuccesfully')]);
        } else {
            return response()->json(['message' => trans('messages.404')]);
        }
    }
}
