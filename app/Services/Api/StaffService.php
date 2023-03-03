<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Carbon\Carbon;
use App\Models\User\User;
use App\Models\Staff\Staff;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\Patient\Patient;
use App\Models\Group\StaffGroup;
use App\Models\UserRole\UserRole;
use App\Models\Staff\StaffProgram;
use Illuminate\Support\Facades\DB;
use App\Models\Staff\StaffLocation;
use App\Models\Task\TaskAssignedTo;
use App\Models\Patient\PatientStaff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Appointment\Appointment;
use App\Models\Communication\CallRecord;
use App\Models\StaffContact\StaffContact;
use App\Models\Communication\Communication;
use App\Models\ConfigMessage\ConfigMessage;
use App\Transformers\Group\GroupTransformer;
use App\Transformers\Staff\StaffTransformer;
use App\Transformers\Role\UserRoleTransformer;
use App\Transformers\Patient\PatientTransformer;
use App\Transformers\Staff\StaffRoleTransformer;
use App\Models\Staff\StaffProvider\StaffProvider;
use App\Transformers\Group\StaffGroupTransformer;
use App\Transformers\Staff\GroupStaffTransformer;
use App\Models\StaffAvailability\StaffAvailability;
use App\Transformers\Staff\StaffContactTransformer;
use App\Transformers\Staff\StaffProgramTransformer;
use App\Transformers\Staff\StaffLocationTransformer;
use App\Transformers\Staff\StaffProviderTransformer;
use App\Transformers\Patient\PatientCountTransformer;
use App\Transformers\Staff\StaffAvailabilityTransformer;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Transformers\Appointment\AppointmentDataTransformer;

class StaffService
{
    // Add Staff
    public function addStaff($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $postData = $request->all();
            $password = Str::random("10");
            $user = [
                'udid' => Str::uuid()->toString(),
                'email' => $request->email,
                'password' => Hash::make($password),
                'emailVerify' => 1,
                'createdBy' => Auth::id(),
                'roleId' => 3,
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'entityType' => $entityType,
            ];
            $data = User::create($user);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => $data->id, 'providerId' => $provider,
                'value' => json_encode($user), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);

            if ($request->input('type')) {
                $type = $request->input('type');
            } else {
                $type = 342;
            }
            $staff = [
                'udid' => Str::uuid()->toString(),
                'userId' => $data->id,
                'firstName' => $request->firstName,
                'middleName' => $request->middleName,
                'lastName' => $request->lastName,
                'phoneNumber' => $request->phoneNumber,
                'genderId' => $request->genderId,
                'specializationId' => $request->specializationId,
                'networkId' => $request->networkId,
                'roleId' => 3,
                'createdBy' => Auth::id(),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'entityType' => $entityType,
                'typeId' => $type,
                'organisation' => $request->organisation,
                'location' => $request->location,
            ];

            if (isset($postData["designationId"]) && !empty($postData["designationId"])) {
                $staff["designationId"] = $postData["designationId"];
            }
            if (isset($postData["extension"])) {
                $staff["extension"] = $request->extension;
            }
            $newData = Staff::create($staff);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'staffs', 'tableId' => $newData->id, 'providerId' => $provider,
                'value' => json_encode($staff), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            if ($request->input('defaultProvider')) {
                $defaultProvider = [
                    'udid' => Str::uuid()->toString(), 'providerId' => $request->input('defaultProvider'), 'createdBy' => Auth::id(), 'isDefault' => 1,
                    'staffId' => $newData->id
                ];
                StaffProvider::create($defaultProvider);
            }
            if ($request->input('providers')) {
                foreach ($request->input('providers') as $provider) {
                    if ($provider == $request->input('defaultProvider')) {
                        continue;
                    }
                    $defaultProvider = [
                        'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'createdBy' => Auth::id(), 'isDefault' => 0,
                        'staffId' => $newData->id
                    ];
                    StaffProvider::create($defaultProvider);
                }
            }
            $staffData = Staff::where('id', $newData->id)->first();
            $message = ["message" => trans('messages.createdSuccesfully')];
            $resp = fractal()->item($staffData)->transformWith(new StaffTransformer())->toArray();
            $endData = array_merge($message, $resp);
            if (isset($postData["phoneNumber"]) && !empty($postData["phoneNumber"])) {
                $msgSMSObj = ConfigMessage::where("type", "addStaff")
                    ->where("entityType", "sendSMS")
                    ->first();
                $variablesArr = array(
                    "password" => $password
                );
                $message = "Your account was successfully created with Virtare Health. Your password is " . $password;
                if (isset($msgSMSObj->messageBody)) {
                    $messageBody = $msgSMSObj->messageBody;
                    $message = Helper::getMessageBody($messageBody, $variablesArr);
                }
                $responseApi = Helper::sendBandwidthMessage($message, $postData["phoneNumber"]);
            }
            if (isset($request->email)) {
                $base_url = env('APP_URL');
                $user = User::where('id', $data->id)->first();
                $token = auth()->login($user);
                $dataToken = $token;
                $staffUdid = $newData->udid;
                $to = $request->email;
                $msgObj = ConfigMessage::where("type", "addStaff")
                    ->where("entityType", "sendMail")
                    ->first();
                $msgHeaderObj = ConfigMessage::where("type", "header")
                    ->where("entityType", "sendMail")
                    ->first();
                $msgFooterObj = ConfigMessage::where("type", "footer")
                    ->where("entityType", "sendMail")
                    ->first();
                $variablesArr = array(
                    "base_url" => $base_url,
                    "dataToken" => $dataToken,
                    "staffUdid" => $staffUdid,
                );
                if (isset($msgObj->messageBody)) {
                    $messageBody = $msgObj->messageBody;
                    if (isset($msgHeaderObj->messageBody) && !empty($msgHeaderObj->messageBody)) {
                        $messageBody = $msgHeaderObj->messageBody . $messageBody;
                    }
                    if (isset($msgFooterObj->messageBody) && !empty($msgFooterObj->messageBody)) {
                        $messageBody = $messageBody . $msgFooterObj->messageBody;
                    }
                    $message = Helper::getMessageBody($messageBody, $variablesArr);
                } else {
                    $message = '';
                    $message .= "You have been granted access to the Virtare Healthcare site Tethr. Here is link for future reference - " . $base_url;
                    // $message .= "<p>Click on this link to set up a password" . $base_url . rawurlencode("#") . "/staff/" . $staffUdid . "/create-password?token=" . $dataToken . "</p>";
                    $message .= "<p><a href=" . $base_url . rawurlencode('#') . '/staff/' . $staffUdid . '/create-password?token=' . $dataToken . ">Click here to set up the password of your Virate Health account." . "</a></p>";
                }
                if (isset($msgObj->otherParameter)) {
                    $otherParameter = json_decode($msgObj->otherParameter);
                    if (isset($otherParameter->fromName)) {
                        $fromName = $otherParameter->fromName;
                    }
                } else {
                    $fromName = "Virtare";
                }

                if (isset($msgObj->subject)) {
                    $subject = $msgObj->subject;
                } else {
                    $subject = "Create New Account";
                }

                Helper::commonMailjet($to, $fromName, $message, $subject);
            }
            Helper::updateFreeswitchUser();
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Staff
    public function listStaff($request, $id)
    {
        try {
            if (!$id) {
                $data = Staff::select('staffs.*')->leftjoin('userRoles', 'userRoles.staffId', '=', 'staffs.id')
                    ->leftjoin('accessRoles', 'accessRoles.id', '=', 'userRoles.accessRoleId')
                    ->leftJoin('globalCodes as g1', 'g1.id', '=', 'staffs.specializationId')
                    ->leftJoin('users', 'users.id', '=', 'staffs.userId')
                    ->leftJoin('globalCodes as g4', 'g4.id', '=', 'staffs.designationId')
                    ->leftJoin('globalCodes as g3', 'g3.id', '=', 'staffs.typeId')
                    ->leftJoin('staffProviders', 'staffProviders.staffId', '=', 'staffs.id')
                    ->leftJoin('staffLocations', 'staffLocations.staffId', '=', 'staffs.id')
                    ->leftJoin('globalCodes as g2', 'g2.id', '=', 'staffs.networkId')
                    ->with('provider');

             /*   $data->leftJoin('providers', 'providers.id', '=', 'staffProviders.providerId')->whereNull('providers.deletedAt')->whereNull('staffProviders.deletedAt');
                $data->leftJoin('programs', 'programs.id', '=', 'staffs.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

                $data->leftJoin('providerLocations', function ($join) {
                    $join->on('staffLocations.providerLocationId', '=', 'providerLocations.id')->where('staffLocations.entityType', '=', 'Country');
                })->whereNull('providerLocations.deletedAt');

                $data->leftJoin('providerLocationStates', function ($join) {
                    $join->on('staffLocations.providerLocationId', '=', 'providerLocationStates.id')->where('staffLocations.entityType', '=', 'State');
                })->whereNull('providerLocationStates.deletedAt');

                $data->leftJoin('providerLocationCities', function ($join) {
                    $join->on('staffLocations.providerLocationId', '=', 'providerLocationCities.id')->where('staffLocations.entityType', '=', 'City');
                })->whereNull('providerLocationCities.deletedAt');

                $data->leftJoin('subLocations', function ($join) {
                    $join->on('staffLocations.providerLocationId', '=', 'subLocations.id')->where('staffLocations.entityType', '=', 'subLocation');
                })->whereNull('subLocations.deletedAt');
*/
                // if (auth()->user()->roleId == 3) {
                //     $data->where('staffs.id', auth()->user()->staff->id);
                // }

                if ($request->search) {
                    $data->where(DB::raw("CONCAT(trim(`staffs`.`firstName`), ' ', trim(`staffs`.`lastName`))"), 'LIKE', "%" . $request->search . "%")
                        ->orWhere(DB::raw("CONCAT(trim(`staffs`.`lastName`), ' ', trim(`staffs`.`firstName`))"), 'LIKE', "%" . $request->search . "%")
                        ->orWhere('g3.name', 'LIKE', "%" . $request->search . "%")
                        ->orWhere('g2.name', 'LIKE', "%" . $request->search . "%")
                        ->orWhere('g1.name', 'LIKE', "%" . $request->search . "%")
                        ->orWhere('g4.name', 'LIKE', "%" . $request->search . "%")
                        ->orWhere('staffs.organisation', 'LIKE', "%" . $request->search . "%")
                        ->orWhere('staffs.location', 'LIKE', "%" . $request->search . "%")
                        ->orWhere('users.email', 'LIKE', $request->search);
                }

              /*  if (request()->header('providerId')) {
                    $provider = Helper::providerId();
                    $data->where('staffProviders.providerId', $provider);
                }

                if (request()->header('providerLocationId')) {
                    $providerLocation = Helper::providerLocationId();
                    if (request()->header('entityType') == 'Country') {
                        $data->where([['staffLocations.locationId', $providerLocation], ['staffLocations.entityType', 'Country']]);
                    }
                    if (request()->header('entityType') == 'State') {
                        $data->where([['staffLocations.locationId', $providerLocation], ['staffLocations.entityType', 'State']]);
                    }
                    if (request()->header('entityType') == 'City') {
                        $data->where([['staffLocations.locationId', $providerLocation], ['staffLocations.entityType', 'City']]);
                    }
                    if (request()->header('entityType') == 'subLocation') {
                        $data->where([['staffLocations.locationId', $providerLocation], ['staffLocations.entityType', 'subLocation']]);
                    }
                }
                if (request()->header('programId')) {
                    $program = Helper::programId();
                    $entityType = Helper::entityType();
                    $data->where([['staffs.programId', $program], ['staffs.entityType', $entityType]]);
                }*/
                if ($request->isActive) {
                    $data->where('staffs.isActive', 1);
                }
                if ($request->type) {
                    $data->where('g3.name', $request->type);
                }
                if ($request->filter) {
                    $data->where(function ($query) use ($request) {
                        $query
                            ->where('g1.name', $request->filter)
                            ->orWhere('g2.name', $request->filter);
                    });
                }
                if ($request->orderField == 'fullName') {
                    $data->orderBy('staffs.firstName', $request->orderBy);
                } elseif ($request->orderField == 'createdAt') {
                    $data->orderBy('staffs.createdAt', $request->orderBy);
                } elseif ($request->orderField == 'role') {
                    $data->orderByRaw('group_concat(accessRoles.roles) ' . $request->orderBy)->whereNull('userRoles.deletedAt');
                } elseif ($request->orderField == 'specialization') {
                    $data->orderBy('g1.name', $request->orderBy);
                } elseif ($request->orderField == 'organisation') {
                    $data->orderBy('staffs.organisation', $request->orderBy);
                } elseif ($request->orderField == 'location') {
                    $data->orderBy('staffs.location', $request->orderBy);
                } else {
                    $data->orderBy('staffs.createdAt', "DESC");
                }
               // echo auth()->user()->roleId; die;
                if (auth()->user()->roleId == 2) { // Admin
                  //  print_r(auth()->user()->staff);
                    $data->where('staffs.clientId', auth()->user()->staff->clientId);
                }


                $data = $data->groupBy('staffs.id')->paginate(env('PER_PAGE', 20));
                return fractal()->collection($data)->transformWith(new StaffTransformer(false))->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
            } else {
                $data = Staff::where('udid', $id)->with('roles', 'userRole', 'appointment')->first();
                return fractal()->item($data)->transformWith(new StaffTransformer())->toArray();
            }
        } catch (Exception $e) {
            echo $e->getMessage(); die;
            throw new \RuntimeException($e);
        }
    }

    // Update Staff
    public function updateStaff($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $staffId = Staff::where('udid', $id)->first();
            $uId = $staffId->userId;
            $user = [
                'email' => $request->input('email'),
                'updatedBy' => Auth::id(),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation
            ];
            User::where('id', $uId)->update($user);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => $uId, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($user), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            $staff = [
                'firstName' => $request->firstName,
                'middleName' => $request->middleName,
                'lastName' => $request->lastName,
                'extension' => $request->extension,
                'phoneNumber' => $request->phoneNumber,
                'genderId' => $request->genderId,
                'specializationId' => $request->specializationId,
                'designationId' => $request->designationId,
                'networkId' => $request->networkId,
                'updatedBy' => Auth::id(),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
            ];
            Staff::where('udid', $id)->update($staff);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'staffs', 'tableId' => $uId, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($staff), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);

            $staffData = Staff::where('udid', $id)->first();

            if ($request->input('defaultProvider')) {
                $provideraInput = StaffProvider::where([['staffId', $staffData->id], ['providerId', $request->input('defaultProvider')], ['isDefault', 1]])->first();
                if (!$provideraInput) {
                    StaffProvider::where([['staffId', $staffData->id], ['isDefault', 1]])->delete();
                    $defaultProvider = [
                        'udid' => Str::uuid()->toString(), 'providerId' => $request->input('defaultProvider'), 'createdBy' => Auth::id(), 'isDefault' => 1,
                        'staffId' => $staffData->id
                    ];
                    StaffProvider::create($defaultProvider);
                }
            }
            if ($request->input('providers')) {
                $providerData = StaffProvider::where('staffId', $staffData->id)->get();
                if ($providerData) {
                    StaffProvider::where([['staffId', $staffData->id], ['isDefault', 0]])->delete();
                }
                foreach ($request->input('providers') as $provider) {
                    $defaultProvider = [
                        'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'createdBy' => Auth::id(), 'isDefault' => 0,
                        'staffId' => $staffData->id
                    ];
                    StaffProvider::create($defaultProvider);
                }
            }
            $message = $message = ["message" => trans('messages.updatedSuccesfully')];;
            $resp = fractal()->item($staffData)->transformWith(new StaffTransformer())->toArray();
            return array_merge($message, $resp);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Staff
    public function staffDelete($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $data = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            $staff = Staff::where('udid', $id)->first();
            $user = $staff->userId;
            $tables = [
                User::where('id', $user),
                StaffContact::where('staffId', $staff->id),
                StaffAvailability::where('staffId', $staff->id),
                StaffProvider::where('staffId', $staff->id),
                UserRole::where('staffId', $staff->id),
                PatientStaff::where('staffId', $staff->id),
                Appointment::where('staffId', $staff->id),
                CallRecord::where('staffId', $staff->id),
                StaffProgram::where('staffId', $staff->id),
                StaffLocation::where('staffId', $staff->id),
                Communication::where('from', $staff->id),
                TaskAssignedTo::where([['assignedTo', $staff->id], ['entityType', 'staff']]),
                Communication::where([['referenceId', $staff->id], ['entityType', 'staff']]),
                Communication::where('from', $user),
                Staff::where('id', $staff->id),
            ];
            foreach ($tables as $table) {
                $table->update($data);
                $table->delete();
            }
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Staff Status
    public function updateStaffStatus($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $staffId = Staff::where('udid', $id)->first();
            $uId = $staffId->userId;
            $user = [
                'isActive' => $request->input('isActive'),
                'updatedBy' => Auth::id(),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation
            ];
            User::where('id', $uId)->update($user);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => $uId, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($user), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            $staff = [
                'isActive' => $request->isActive,
                'updatedBy' => auth()->user()->id,
                'providerId' => $provider,
                'providerLocationId' => $providerLocation
            ];
            Staff::where('udid', $id)->update($staff);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'staffs', 'tableId' => $id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($staff), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            $staffData = Staff::where('udid', $id)->first();
            $message = ["message" => trans('messages.updatedSuccesfully')];
            $resp = fractal()->item($staffData)->transformWith(new StaffTransformer())->toArray();
            $endData = array_merge($message, $resp);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }

    }

    // Add Staff Contact
    public function addStaffContact($request, $id)
    {
        try {
            $staff = Staff::where('udid', $id)->first();
            $udid = Str::uuid()->toString();
            $firstName = $request->input('firstName');
            $middleName = $request->input('middleName');
            $lastName = $request->input('lastName');
            $extension = $request->input('extension');
            $email = $request->input('email');
            $phoneNumber = $request->input('phoneNumber');
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $staffId = $staff->id;
            DB::select('CALL createStaffContact("' . $provider . '","' . $udid . '","' . $firstName . '","' . $middleName . '","' . $lastName . '","' . $extension . '","' . $email . '","' . $phoneNumber . '","' . $staffId . '")');
            $staffContactData = StaffContact::where('udid', $udid)->first();
            $message = ["message" => trans('messages.addedSuccesfully')];
            $resp = fractal()->item($staffContactData)->transformWith(new StaffContactTransformer())->toArray();
            $endData = array_merge($message, $resp);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Staff Contact
    public function listStaffContact($request, $id, $staffContactId)
    {
        try {
            $staff = Staff::where('udid', $id)->first();
            $data = StaffContact::select('staffContacts.*')->where('staffContacts.staffId', $staff->id);

            // $data->leftJoin('providers', 'providers.id', '=', 'staffContacts.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'staffContacts.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('staffContacts.providerLocationId', '=', 'providerLocations.id')->where('staffContacts.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('staffContacts.providerLocationId', '=', 'providerLocationStates.id')->where('staffContacts.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('staffContacts.providerLocationId', '=', 'providerLocationCities.id')->where('staffContacts.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('staffContacts.providerLocationId', '=', 'subLocations.id')->where('staffContacts.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('staffContacts.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['staffContacts.providerLocationId', $providerLocation], ['staffContacts.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['staffContacts.providerLocationId', $providerLocation], ['staffContacts.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['staffContacts.providerLocationId', $providerLocation], ['staffContacts.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['staffContacts.providerLocationId', $providerLocation], ['staffContacts.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['staffContacts.programId', $program], ['staffContacts.entityType', $entityType]]);
            // }
            if (!$staffContactId) {
                $data = $data->get();
                return fractal()->collection($data)->transformWith(new StaffContactTransformer())->toArray();
            } elseif ($staffContactId) {
                $data = $data->where('staffContacts.udid', $staffContactId)->first();
                return fractal()->item($data)->transformWith(new StaffContactTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Staff Contact
    public function updateStaffContact($request, $staffId, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $staffContact = [
                'firstName' => $request->input('firstName'),
                'lastName' => $request->input('lastName'),
                'extension' => $request->input('extension'),
                'email' => $request->input('email'),
                'phoneNumber' => $request->input('phoneNumber'),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation
            ];
            $staff = Staff::where('udid', $staffId)->first();
            StaffContact::where([['staffId', $staff->id], ['udid', $id]])->update($staffContact);
            $data = Helper::tableName('App\Models\StaffContact\StaffContact', $id);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'staffContacts', 'tableId' => $data, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($staffContact), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            $staffContactData = StaffContact::where('udid', $id)->first();
            $message = ["message" => "Updated Successfully"];
            $resp = fractal()->item($staffContactData)->transformWith(new StaffContactTransformer())->toArray();
            $endData = array_merge($message, $resp);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Staff Contact
    public function deleteStaffContact($request, $staffId, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            if (!empty($request->staffId)) {
                $staff = Staff::where('udid', $staffId)->first();
                $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                StaffContact::where([['staffId', $staff->id], ['udid', $id]])->update($input);
                $data = Helper::tableName('App\Models\StaffContact\StaffContact', $id);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'staffContacts', 'tableId' => $data, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
                StaffContact::where([['staffId', $staff->id], ['udid', $id]])->delete();
                return response()->json(['message' => "Deleted Successfully"]);
            } else {
                return response()->json(['message' => 'Somethings Went Worng']);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Staff Availability
    public function addStaffAvailability($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $staffId = Helper::entity('staff', $id);
            $udid = Str::uuid()->toString();
            $startTime = Helper::time($request->input('startTime'));
            $endTime = Helper::time($request->input('endTime'));
            $data = StaffAvailability::where('staffId', $staffId)->where(function ($query) use ($startTime, $endTime) {
                $query->where([['startTime', '>=', $startTime], ['endTime', '<=', $endTime]])
                    ->orWhere([['startTime', '<=', $startTime], ['endTime', '>=', $endTime]])
                    ->orWhere(function ($query1) use ($startTime, $endTime) {
                        $query1->whereBetween('startTime', array($startTime, $endTime))
                            ->orWhereBetween('endTime', array($startTime, $endTime))
                            ->where([['startTime', '<=', $startTime], ['endTime', '<=', $endTime]]);
                    })
                    ->orWhere(function ($query1) use ($startTime, $endTime) {
                        $query1->whereBetween('startTime', array($startTime, $endTime))
                            ->orWhereBetween('endTime', array($startTime, $endTime))
                            ->where([['startTime', '>=', $startTime], ['endTime', '>=', $endTime]]);
                    });
            })->exists();
            if ($data) {
                $rules = [
                    'startTime' => ['Start time should be unique'],
                    'endTime' => ['End time should be unique'],
                ];
                return response()->json($rules, 422);
            } else {
                DB::select('CALL createStaffAvailability("' . $provider . '","' . $udid . '","' . $startTime . '","' . $endTime . '","' . $staffId . '")');
                $staffAvailability = StaffAvailability::where('udid', $udid)->first();
                $message = ["message" => trans('messages.addedSuccesfully')];
                $resp = fractal()->item($staffAvailability)->transformWith(new StaffAvailabilityTransformer())->toArray();
                $endData = array_merge($message, $resp);
                return $endData;
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Staff Availability
    public function listStaffAvailability($request, $id, $staffAvailabilityId)
    {
        try {
            $staff = Staff::where('udid', $id)->first();
            $data = StaffAvailability::select('staffAvailabilities.*')->where('staffAvailabilities.staffId', $staff->id);

            // $data->leftJoin('providers', 'providers.id', '=', 'staffAvailabilities.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'staffAvailabilities.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('staffAvailabilities.providerLocationId', '=', 'providerLocations.id')->where('staffAvailabilities.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('staffAvailabilities.providerLocationId', '=', 'providerLocationStates.id')->where('staffAvailabilities.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('staffAvailabilities.providerLocationId', '=', 'providerLocationCities.id')->where('staffAvailabilities.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('staffAvailabilities.providerLocationId', '=', 'subLocations.id')->where('staffAvailabilities.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('staffAvailabilities.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['staffAvailabilities.providerLocationId', $providerLocation], ['staffAvailabilities.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['staffAvailabilities.providerLocationId', $providerLocation], ['staffAvailabilities.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['staffAvailabilities.providerLocationId', $providerLocation], ['staffAvailabilities.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['staffAvailabilities.providerLocationId', $providerLocation], ['staffAvailabilities.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['staffAvailabilities.programId', $program], ['staffAvailabilities.entityType', $entityType]]);
            // }
            if (!$staffAvailabilityId) {
                $data = $data->get();
                return fractal()->collection($data)->transformWith(new StaffAvailabilityTransformer())->toArray();
            } elseif ($staffAvailabilityId) {
                $data = $data->where('staffAvailabilities.udid', $staffAvailabilityId)->first();
                return fractal()->item($data)->transformWith(new StaffAvailabilityTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Staff Availability
    public function updateStaffAvailability($request, $staffId, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $staffData = Helper::entity('staff', $staffId);
            $startTime = Helper::time($request->input('startTime'));
            $endTime = Helper::time($request->input('endTime'));
            $data = StaffAvailability::where('staffId', $staffData)->where(function ($query) use ($startTime, $endTime) {
                $query->where([['startTime', '>=', $startTime], ['endTime', '<=', $endTime]])
                    ->orWhere([['startTime', '<=', $startTime], ['endTime', '>=', $endTime]])
                    ->orWhere(function ($query1) use ($startTime, $endTime) {
                        $query1->whereBetween('startTime', array($startTime, $endTime))
                            ->orWhereBetween('endTime', array($startTime, $endTime))
                            ->where([['startTime', '<=', $startTime], ['endTime', '<=', $endTime]]);
                    })
                    ->orWhere(function ($query1) use ($startTime, $endTime) {
                        $query1->whereBetween('startTime', array($startTime, $endTime))
                            ->orWhereBetween('endTime', array($startTime, $endTime))
                            ->where([['startTime', '>=', $startTime], ['endTime', '>=', $endTime]]);
                    });
            })->where('udid', '!=', $id)->exists();
            if ($data) {
                $rules = [
                    'startTime' => ['Start time should be unique'],
                    'endTime' => ['End time should be unique'],
                ];
                return response()->json($rules, 422);
            } else {
                $staffAvailability = [
                    'startTime' => $startTime,
                    'endTime' => $endTime,
                    'providerId' => $provider,
                    'providerLocationId' => $providerLocation
                ];
                $staff = Staff::where('udid', $staffId)->first();
                StaffAvailability::where([['staffId', $staff->id], ['udid', $id]])->update($staffAvailability);
                $staffAvailabilityData = Helper::entity('staffAvailability', $id);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'staffAvailabilities', 'tableId' => $staffAvailabilityData, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($staffAvailability), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
                $staffAvailability = StaffAvailability::where('udid', $id)->first();
                $message = ["message" => "Updated Successfully"];
                $resp = fractal()->item($staffAvailability)->transformWith(new StaffAvailabilityTransformer())->toArray();
                $endData = array_merge($message, $resp);
                return $endData;
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Deleted Staff Availability
    public function deleteStaffAvailability($request, $staffId, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $staff = Staff::where('udid', $staffId)->first();
            $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            StaffAvailability::where([['staffId', $staff->id], ['udid', $id]])->update($input);
            $staffAvailabilityData = Helper::entity('staffAvailability', $id);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'staffAvailabilities', 'tableId' => $staffAvailabilityData, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            StaffAvailability::where([['staffId', $staff->id], ['udid', $id]])->delete();
            return response()->json(['message' => "Deleted Successfully"]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Satff Role
    public function addStaffRole($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $roles = $request->roles;
            $staff = Staff::where('udid', $id)->first();
            foreach ($roles as $roleId) {
                $udid = Str::uuid()->toString();
                $staffId = $staff->id;
                $accessRoleId = Helper::tableName('App\Models\AccessRole\AccessRole', $roleId);
                $userRole = UserRole::where([['staffId', $staffId], ['accessRoleId', $accessRoleId]])->first();
                if (empty($userRole)) {
                    DB::select('CALL createstaffRole("' . $provider . '","' . $udid . '","' . $staffId . '","' . $accessRoleId . '")');
                } else {
                    return response()->json(["roles" => trans('messages.roleExist')], 422);
                }
            }
            $data = UserRole::where('staffId', $staff->id)->get();
            $userdata = fractal()->collection($data)->transformWith(new UserRoleTransformer())->toArray();
            $message = ['message' => trans('messages.createdSuccesfully')];
            $endData = array_merge($message, $userdata);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Liat Staff Role
    public function listStaffRole($request, $id)
    {
        try {
            $data = UserRole::select('userRoles.*')->with('roles');

            // $data->leftJoin('providers', 'providers.id', '=', 'userRoles.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'userRoles.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('userRoles.providerLocationId', '=', 'providerLocations.id')->where('userRoles.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('userRoles.providerLocationId', '=', 'providerLocationStates.id')->where('userRoles.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('userRoles.providerLocationId', '=', 'providerLocationCities.id')->where('userRoles.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('userRoles.providerLocationId', '=', 'subLocations.id')->where('userRoles.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('userRoles.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['userRoles.providerLocationId', $providerLocation], ['userRoles.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['userRoles.providerLocationId', $providerLocation], ['userRoles.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['userRoles.providerLocationId', $providerLocation], ['userRoles.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['userRoles.providerLocationId', $providerLocation], ['userRoles.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['userRoles.programId', $program], ['userRoles.entityType', $entityType]]);
            // }
            $staff = Staff::where('udid', $id)->first();
            $data = $data->where('userRoles.staffId', $staff->id)->with('roles')->whereHas('roles')->get();
            return fractal()->collection($data)->transformWith(new StaffRoleTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Staff Role
    public function updateStaffRole($request, $staffId, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $staffRole = [
                'userId' => $request->input('userId'),
                'roleId' => $request->input('roleId'),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation
            ];
            $staff = Staff::where('udid', $staffId)->first();
            UserRole::where([['staffId', $staff->id], ['udid', $id]])->update($staffRole);
            $user = Helper::tableName('App\Models\UserRole\UserRole', $id);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'userRoles', 'tableId' => $user, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($staffRole), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            return response()->json(['message' => "Updated Successfully"]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Staff Role
    public function deleteStaffRole($request, $staffId, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $staff = Staff::where('udid', $staffId)->first();
            $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            UserRole::where([['staffId', $staff->id], ['udid', $id]])->update($input);
            $user = Helper::tableName('App\Models\UserRole\UserRole', $id);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'userRoles', 'tableId' => $user, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            UserRole::where([['staffId', $staff->id], ['udid', $id]])->delete();
            return response()->json(['message' => "Deleted Successfully"]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Staff Provider
    public function addStaffProvider($request, $id)
    {
        try {
            $providers = $request->providers;
            $staff = Staff::where('udid', $id)->first();
            foreach ($providers as $providerId) {
                $udid = Str::uuid()->toString();
                $providerId = $providerId;
                $staffId = $staff->id;
                DB::select('CALL createStaffProvider("' . $udid . '","' . $staffId . '","' . $providerId . '")');
            }
            return response()->json(["message" => trans('messages.addedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Staff Provider
    public function listStaffProvider($request, $id)
    {
        try {
            $data = StaffProvider::select('staffProviders.*')->with('providers');

            // $data->leftJoin('providers', 'providers.id', '=', 'staffProviders.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'staffProviders.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('staffProviders.providerLocationId', '=', 'providerLocations.id')->where('staffProviders.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('staffProviders.providerLocationId', '=', 'providerLocationStates.id')->where('staffProviders.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('staffProviders.providerLocationId', '=', 'providerLocationCities.id')->where('staffProviders.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('staffProviders.providerLocationId', '=', 'subLocations.id')->where('staffProviders.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('staffProviders.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['staffProviders.providerLocationId', $providerLocation], ['staffProviders.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['staffProviders.providerLocationId', $providerLocation], ['staffProviders.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['staffProviders.providerLocationId', $providerLocation], ['staffProviders.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['staffProviders.providerLocationId', $providerLocation], ['staffProviders.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['staffProviders.programId', $program], ['staffProviders.entityType', $entityType]]);
            // }
            $staff = Staff::where('udid', $id)->first();
            $data = $data->where('staffProviders.staffId', $staff->id)->get();
            return fractal()->collection($data)->transformWith(new StaffProviderTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Staff Provider
    public function updateStaffProvider($request, $staffId, $id)
    {
        try {
            $providers = $request->providers;
            $staff = Staff::where('udid', $staffId)->first();
            foreach ($providers as $providerId) {
                $staffProvider = [
                    'providerId' => $providerId,
                    'staffId' => $staff->id,
                ];
                StaffProvider::where([['staffId', $staff->id], ['udid', $id]])->update($staffProvider);
                $satffProvider = Helper::entity('satffProvider', $id);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'staffProviders', 'tableId' => $satffProvider, 'providerId' => $providerId,
                    'value' => json_encode($staffProvider), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
            }
            return response()->json(['message' => "Updated Successfully"]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Staff Provider
    public function deleteStaffProvider($request, $staffId, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $staff = Staff::where('udid', $staffId)->first();
            $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            StaffProvider::where([['staffId', $staff->id], ['udid', $id]])->update($input);
            $satffProvider = Helper::entity('satffProvider', $id);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'staffProviders', 'tableId' => $satffProvider, 'providerId', $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            StaffProvider::where([['staffId', $staff->id], ['udid', $id]])->delete();
            return response()->json(['message' => "Deleted Successfully"]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Specialization Count
    public function specializationCount()
    {
        try {
            if (auth()->user()->roleId == 3) {
                if (Helper::haveAccessAction(null, 490)) {
                    $staffId = '';
                } else {
                    $staffId = auth()->user()->staff->id;
                }
            } else {
                $staffId = '';
            }
            $data = DB::select(
                "CALL careCoordinatorSpecializationCount('" . $staffId . "')",
            );


            $specialization = array();
            foreach ($data as $specializationData) {
                $specialization[] = $specializationData->text;
            }
            $dataSpecialization = GlobalCode::where('globalCodeCategoryId', 2)->get();
            $specializationFinalCount = array();
            foreach ($dataSpecialization as $key => $value) {
                $specializationNew = new \stdClass();
                if (!in_array($value['name'], $specialization, true)) {
                    $specializationNew->total = 0;
                    $specializationNew->text = $value['name'];
                    $specializationNew->color = $value['color'];
                    $specializationFinalCount[] = $specializationNew;
                } else {
                    $key = array_search($value['name'], $specialization, true);
                    $specializationFinalCount[] = $data[$key];
                }
            }

            return fractal()->item($specializationFinalCount)->transformWith(new PatientCountTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Network Count
    public function networkCount()
    {
        try {
            if (auth()->user()->roleId == 3) {
                if (Helper::haveAccessAction(null, 490)) {
                    $staffId = '';
                } else {
                    $staffId = auth()->user()->staff->id;
                }
            } else {
                $staffId = '';
            }

            $data = DB::select(
                "CALL careCoordinatorNetworkCount('" . $staffId . "')",
            );

            $network = array();
            foreach ($data as $newtorkData) {
                $network[] = $newtorkData->text;
            }
            $dataNetwork = GlobalCode::where('globalCodeCategoryId', 10)->get();
            $networkFinalCount = array();
            foreach ($dataNetwork as $key => $value) {
                $networkNew = new \stdClass();
                if (!in_array($value['name'], $network, true)) {
                    $networkNew->total = 0;
                    $networkNew->text = $value['name'];
                    $networkNew->color = $value['color'];
                    $networkFinalCount[] = $networkNew;
                } else {
                    $key = array_search($value['name'], $network, true);
                    $networkFinalCount[] = $data[$key];
                }
            }
            return fractal()->item($networkFinalCount)->transformWith(new PatientCountTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Staff Patient List
    public function patientList($request, $id)
    {
        try {
            $staff = Staff::where('udid', $id)->first();
            $staffId = '';
            if (isset($staff->user->roles->id)) {
                $roleId = $staff->user->roles->id;
                $staffId = $staff->id;
            } elseif (isset($staff->id)) {
                $roleId = '';
            } else {
                return response()->json(['message' => "invalid staff id."], 400);
            }
            if ($roleId == 3) {
                if (Helper::haveAccessAction($id, 490)) {
                    $patient = Patient::where(DB::raw("CONCAT(trim(`firstName`), ' ', trim(`lastName`))"), 'LIKE', "%" . $request->search . "%")
                        ->orWhere(DB::raw("CONCAT(trim(`lastName`), ' ', trim(`firstName`))"), 'LIKE', "%" . $request->search . "%");
                } else {
                    $patient = Patient::whereHas('patientStaff', function ($query) use ($request, $staffId) {
                        $query->where('staffId', $staffId)->whereHas('patient', function ($q) use ($request) {
                            $q->where(DB::raw("CONCAT(trim(`firstName`), ' ', trim(`lastName`))"), 'LIKE', "%" . $request->search . "%")
                                ->orWhere(DB::raw("CONCAT(trim(`lastName`), ' ', trim(`firstName`))"), 'LIKE', "%" . $request->search . "%");
                        });
                    });
                }
            } else {
                $patient = Patient::where(DB::raw("CONCAT(trim(`firstName`), ' ', trim(`lastName`))"), 'LIKE', "%" . $request->search . "%")
                    ->orWhere(DB::raw("CONCAT(trim(`lastName`), ' ', trim(`firstName`))"), 'LIKE', "%" . $request->search . "%");
            }
            if ($request->orderField === 'fullName') {
                $patient->orderBy('lastName', $request->orderBy);
            } elseif ($request->orderField === 'age') {
                if ($request->orderBy === 'ASC') {
                    $patient->orderBy('dob', 'DESC');
                } else {
                    $patient->orderBy('dob', 'ASC');
                }
            } elseif ($request->orderField === 'genderName') {
                $patient->select("patients.*")->join('globalCodes', 'globalCodes.id', '=', 'patients.genderId')
                    ->orderBy('globalCodes.name', $request->orderBy);
            } else {
                $patient->orderBy('lastName', 'ASC');
            }
            $patient = $patient->paginate(env('PER_PAGE', 20));
            return fractal()->collection($patient)->transformWith(new PatientTransformer(false))->paginateWith(new IlluminatePaginatorAdapter($patient))->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Staff Appointment List
    public function appointmentList($request, $id)
    {
        try {
            if ($id) {
                $staff = Staff::where('udid', $id)->first();
                $staffId = $staff->id;
            } else {
                $staffId = auth()->user()->staff->id;
            }
            $data = Appointment::select('appointments.*')->where([['appointments.staffId', $staffId], ['appointments.startDateTime', '>=', Carbon::today()]]);

            // $data->leftJoin('providers', 'providers.id', '=', 'appointments.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'appointments.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('appointments.providerLocationId', '=', 'providerLocations.id')->where('appointments.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('appointments.providerLocationId', '=', 'providerLocationStates.id')->where('appointments.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('appointments.providerLocationId', '=', 'providerLocationCities.id')->where('appointments.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('appointments.providerLocationId', '=', 'subLocations.id')->where('appointments.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('appointments.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['appointments.providerLocationId', $providerLocation], ['appointments.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['appointments.providerLocationId', $providerLocation], ['appointments.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['appointments.providerLocationId', $providerLocation], ['appointments.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['appointments.providerLocationId', $providerLocation], ['appointments.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['appointments.programId', $program], ['appointments.entityType', $entityType]]);
            // }
            if ($request->all) {
                $data = $data->get();
                return fractal()->collection($data)->transformWith(new AppointmentDataTransformer())->toArray();
            } else {
                $data = $data->paginate(env('PER_PAGE',20));
                return fractal()->collection($data)->transformWith(new AppointmentDataTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Patient Appointment List
    public function patientAppointment($request, $id)
    {
        try {
            if ($id) {
                $patient = Patient::where('udid', $id)->first();
                $patientId = $patient->id;
            } else {
                $patientId = auth()->user()->patient->id;
            }
            $data = Appointment::select('appointments.*')->where('appointments.patientId', $patientId)->whereDate('appointments.startDateTime', '>=', Carbon::today());

            // $data->leftJoin('providers', 'providers.id', '=', 'appointments.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'appointments.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('appointments.providerLocationId', '=', 'providerLocations.id')->where('appointments.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('appointments.providerLocationId', '=', 'providerLocationStates.id')->where('appointments.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('appointments.providerLocationId', '=', 'providerLocationCities.id')->where('appointments.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('appointments.providerLocationId', '=', 'subLocations.id')->where('appointments.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('appointments.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['appointments.providerLocationId', $providerLocation], ['appointments.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['appointments.providerLocationId', $providerLocation], ['appointments.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['appointments.providerLocationId', $providerLocation], ['appointments.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['appointments.providerLocationId', $providerLocation], ['appointments.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['appointments.programId', $program], ['appointments.entityType', $entityType]]);
            // }
            if ($request->all) {
                $data = $data->get();
                return fractal()->collection($data)->transformWith(new AppointmentDataTransformer())->toArray();
            } else if ($request->chat) {
                $data = $data->where('appointments.patientId', $patientId)->get();
                return fractal()->collection($data)->transformWith(new AppointmentDataTransformer())->toArray();
            } else {
                $data = $data->paginate(env('PER_PAGE',20));
                return fractal()->collection($data)->transformWith(new AppointmentDataTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Reset Staff Password
    public function resetStaffPassword($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $staffId = Staff::where('udid', $id)->first();
            $uId = $staffId->userId;
            $input = [
                'password' => Hash::make($request->input('password')),
                'updatedBy' => Auth::id(),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation
            ];
            User::where('id', $uId)->update($input);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => $uId, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            return response()->json(["message" => trans('messages.updatedSuccesfully')]);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Staff Multiple Locations
    public function staffLocationAdd($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $staffId = Helper::tableName('App\Models\Staff\Staff', $id);
            $staffData = StaffLocation::where([['staffId', $staffId], ['entityType', $request->entityType]], ['locationId', $request->locationId])->first();
            if (!$staffData) {
                $isDefault = $request->isDefault == true ? 1 : 0;
                $input = [
                    'udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'providerId' => $provider, 'isDefault' => $isDefault,
                    'locationId' => $request->locationId, 'staffId' => $staffId, 'entityType' => $request->entityType, 'locationsHierarchy' => json_encode($request->locationsHierarchy)
                ];
                $data = StaffLocation::create($input);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'staffLocations', 'tableId' => $data->id, 'providerId' => $provider, 'providerLocationId' => $request->locationId,
                    'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
                return response()->json(["message" => trans('messages.createdSuccesfully')]);
            } else {
                return response()->json(["locationId" => trans('messages.staffLocation')]);
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Staff Multiple Locations
    public function staffLocationList($request, $id, $locationId)
    {
        try {
            $staffId = Helper::tableName('App\Models\Staff\Staff', $id);
            $data = StaffLocation::select('staffLocations.*')
                ->leftJoin('staffs', 'staffs.id', '=', 'staffLocations.staffId')
                ->where('staffLocations.staffId', $staffId)
                ->orWhere('staffs.id', $staffId);

            // $data->leftJoin('providers', 'providers.id', '=', 'staffLocations.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'staffLocations.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('staffLocations.providerLocationId', '=', 'providerLocations.id')->where('staffLocations.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('staffLocations.providerLocationId', '=', 'providerLocationStates.id')->where('staffLocations.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('staffLocations.providerLocationId', '=', 'providerLocationCities.id')->where('staffLocations.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('staffLocations.providerLocationId', '=', 'subLocations.id')->where('staffLocations.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');


            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('staffLocations.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['staffLocations.providerLocationId', $providerLocation], ['staffLocations.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['staffLocations.providerLocationId', $providerLocation], ['staffLocations.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['staffLocations.providerLocationId', $providerLocation], ['staffLocations.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['staffLocations.providerLocationId', $providerLocation], ['staffLocations.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['staffLocations.programId', $program], ['staffLocations.entityType', $entityType]]);
            // }
            if (!$locationId) {
                if ($request->orderField == 'location') {
                    $data->orderBy('providerLocations.locationName', $request->orderBy);
                } elseif ($request->orderField == 'level') {
                    $data->orderBy('providerLocations.level', $request->orderBy);
                } else {
                    $data->orderBy('providerLocations.locationName', 'ASC');
                }
                $data = $data->get();
                return fractal()->collection($data)->transformWith(new StaffLocationTransformer())->toArray();
            } else {
                $data = $data->where('staffLocations.udid', $locationId)->first();
                return fractal()->item($data)->transformWith(new StaffLocationTransformer())->toArray();
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Staff Multiple Locations
    public function staffLocationDelete($request, $id, $locationId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $staffLocationId = Helper::tableName('App\Models\Staff\StaffLocation', $locationId);
            $input = ['updatedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1];
            StaffLocation::where('udid', $locationId)->update($input);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'staffLocations', 'tableId' => $staffLocationId, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            StaffLocation::where('udid', $locationId)->delete();
            return response()->json(["message" => trans('messages.deletedSuccesfully')]);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Staff Multiple Locations
    public function staffProgramAdd($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $staffId = Helper::tableName('App\Models\Staff\Staff', $id);
            $program = $request->programs;
            foreach ($program as $value) {
                $programData = StaffProgram::where([['programId', $value], ['staffId', $staffId]])->first();
                if (!$programData) {
                    $input = ['udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'providerId' => $provider, 'locationId' => $providerLocation, 'programId' => $value, 'staffId' => $staffId];
                    $data = StaffProgram::create($input);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'staffPrograms', 'tableId' => $data->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                } else {
                    return response()->json(['message' => array(trans('messages.staffProgram'))]);
                }
            }

            return response()->json(["message" => trans('messages.createdSuccesfully')]);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Staff Multiple Locations
    public function staffProgramList($request, $id, $programId)
    {
        try {
            $staffId = Helper::tableName('App\Models\Staff\Staff', $id);
            $data = StaffProgram::select('staffPrograms.*')->where('staffPrograms.staffId', $staffId);
            // $data->leftJoin('providers', 'providers.id', '=', 'staffPrograms.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'staffPrograms.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('staffPrograms.providerLocationId', '=', 'providerLocations.id')->where('staffPrograms.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('staffPrograms.providerLocationId', '=', 'providerLocationStates.id')->where('staffPrograms.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('staffPrograms.providerLocationId', '=', 'providerLocationCities.id')->where('staffPrograms.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('staffPrograms.providerLocationId', '=', 'subLocations.id')->where('staffPrograms.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('staffPrograms.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['staffPrograms.providerLocationId', $providerLocation], ['staffPrograms.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['staffPrograms.providerLocationId', $providerLocation], ['staffPrograms.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['staffPrograms.providerLocationId', $providerLocation], ['staffPrograms.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['staffPrograms.providerLocationId', $providerLocation], ['staffPrograms.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['staffPrograms.programId', $program], ['staffPrograms.entityType', $entityType]]);
            // }
            if (!$programId) {
                if ($request->orderField == 'name') {
                    $data->orderBy('programs.name', $request->orderBy);
                } else {
                    $data->orderBy('programs.name', 'ASC');
                }
                $data = $data->get();
                return fractal()->collection($data)->transformWith(new StaffProgramTransformer())->toArray();
            } else {
                $data = $data->where('staffPrograms.udid', $programId)->first();
                return fractal()->item($data)->transformWith(new StaffProgramTransformer())->toArray();
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Staff Multiple Locations
    public function staffProgramDelete($request, $id, $programId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $staffProgramId = Helper::tableName('App\Models\Staff\StaffProgram', $programId);
            $input = ['updatedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1];
            StaffProgram::where('udid', $programId)->update($input);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'staffPrograms', 'tableId' => $staffProgramId, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            StaffProgram::where('udid', $programId)->delete();
            return response()->json(["message" => trans('messages.deletedSuccesfully')]);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Staff Profile
    public function staffProfileUpdate($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $input = ['profilePhoto' => $request->input('profilePhoto'), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            $staff = Staff::where('udid', $id)->first();
            $user = User::where('id', $staff->userId)->update($input);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => $staff->userId, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            if ($user) {
                return response()->json(['message' => trans('messages.updatedSuccesfully')]);
            } else {
                return response()->json(['message' => trans('messages.error')]);
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Staff Group
    public function staffGroupList($request, $id, $groupId)
    {
        try {
            $staffId = Helper::tableName('App\Models\Staff\Staff', $id);
            $data = StaffGroup::select('staffGroups.*')->where('staffGroups.staffId', $staffId)->get();
            return fractal()->collection($data)->transformWith(new GroupStaffTransformer())->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
