<?php

namespace App\Services\Api;

use App\Events\SetUpPasswordEvent;
use Exception;
use App\Helper;
use App\Models\User\User;
use App\Models\Staff\Staff;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Log\ChangeLog;
use App\Models\Patient\Patient;
use App\Models\User\UserSetting;
use Illuminate\Support\Facades\DB;
use Webklex\PHPIMAP\ClientManager;
use Illuminate\Support\Facades\URL;
use App\Models\Patient\PatientStaff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Notification\Notification;
use App\Transformers\User\UserTransformer;
use App\Models\Communication\Communication;
use App\Models\ConfigMessage\ConfigMessage;
use App\Models\Patient\PatientFamilyMember;
use App\Transformers\Patient\PatientTransformer;
use App\Transformers\User\UserSettingTransformer;
use App\Models\Communication\CommunicationInbound;
use App\Models\Communication\CommunicationMessage;
use App\Transformers\Patient\PatientFamilyMemberTransformer;

//use Webklex\PHPIMAP\Client;

class UserService
{
    // Show User Profile
    public function userProfile($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            if (auth()->user()->roleId == 4) {
                $data = Patient::select('patients.*');

                // $data->leftJoin('providers', 'providers.id', '=', 'patients.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
                // $data->leftJoin('programs', 'programs.id', '=', 'patients.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

                // $data->leftJoin('providerLocations', function ($join) {
                //     $join->on('patients.providerLocationId', '=', 'providerLocations.id')->where('patients.entityType', '=', 'Country');
                // })->whereNull('providerLocations.deletedAt');

                // $data->leftJoin('providerLocationStates', function ($join) {
                //     $join->on('patients.providerLocationId', '=', 'providerLocationStates.id')->where('patients.entityType', '=', 'State');
                // })->whereNull('providerLocationStates.deletedAt');

                // $data->leftJoin('providerLocationCities', function ($join) {
                //     $join->on('patients.providerLocationId', '=', 'providerLocationCities.id')->where('patients.entityType', '=', 'City');
                // })->whereNull('providerLocationCities.deletedAt');

                // $data->leftJoin('subLocations', function ($join) {
                //     $join->on('patients.providerLocationId', '=', 'subLocations.id')->where('patients.entityType', '=', 'subLocation');
                // })->whereNull('subLocations.deletedAt');

                // if (request()->header('providerId')) {
                //     $provider = Helper::providerId();
                //     $data->where('patients.providerId', $provider);
                // }
                // if (request()->header('providerLocationId')) {
                //     $providerLocation = Helper::providerLocationId();
                //     if (request()->header('entityType') == 'Country') {
                //         $data->where([['patients.providerLocationId', $providerLocation], ['patients.entityType', 'Country']]);
                //     }
                //     if (request()->header('entityType') == 'State') {
                //         $data->where([['patients.providerLocationId', $providerLocation], ['patients.entityType', 'State']]);
                //     }
                //     if (request()->header('entityType') == 'City') {
                //         $data->where([['patients.providerLocationId', $providerLocation], ['patients.entityType', 'City']]);
                //     }
                //     if (request()->header('entityType') == 'subLocation') {
                //         $data->where([['patients.providerLocationId', $providerLocation], ['patients.entityType', 'subLocation']]);
                //     }
                // }
                // if (request()->header('programId')) {
                //     $program = Helper::programId();
                //     $entityType = Helper::entityType();
                //     $data->where([['patients.programId', $program], ['patients.entityType', $entityType]]);
                // }
                $data = $data->where('patients.userId', auth()->user()->id)->first();
                return fractal()->item($data)->transformWith(new PatientTransformer(true))->toArray();
            } elseif (auth()->user()->roleId == 6) {
                $data = PatientFamilyMember::select('patientFamilyMembers.*');

                // $data->leftJoin('providers', 'providers.id', '=', 'patientFamilyMembers.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
                // $data->leftJoin('programs', 'programs.id', '=', 'patientFamilyMembers.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

                // $data->leftJoin('providerLocations', function ($join) {
                //     $join->on('patientFamilyMembers.providerLocationId', '=', 'providerLocations.id')->where('patientFamilyMembers.entityType', '=', 'Country');
                // })->whereNull('providerLocations.deletedAt');

                // $data->leftJoin('providerLocationStates', function ($join) {
                //     $join->on('patientFamilyMembers.providerLocationId', '=', 'providerLocationStates.id')->where('patientFamilyMembers.entityType', '=', 'State');
                // })->whereNull('providerLocationStates.deletedAt');

                // $data->leftJoin('providerLocationCities', function ($join) {
                //     $join->on('patientFamilyMembers.providerLocationId', '=', 'providerLocationCities.id')->where('patientFamilyMembers.entityType', '=', 'City');
                // })->whereNull('providerLocationCities.deletedAt');

                // $data->leftJoin('subLocations', function ($join) {
                //     $join->on('patientFamilyMembers.providerLocationId', '=', 'subLocations.id')->where('patientFamilyMembers.entityType', '=', 'subLocation');
                // })->whereNull('subLocations.deletedAt');

                // if (request()->header('providerId')) {
                //     $provider = Helper::providerId();
                //     $data->where('patientFamilyMembers.providerId', $provider);
                // }
                // if (request()->header('providerLocationId')) {
                //     $providerLocation = Helper::providerLocationId();
                //     if (request()->header('entityType') == 'Country') {
                //         $data->where([['patientFamilyMembers.providerLocationId', $providerLocation], ['patientFamilyMembers.entityType', 'Country']]);
                //     }
                //     if (request()->header('entityType') == 'State') {
                //         $data->where([['patientFamilyMembers.providerLocationId', $providerLocation], ['patientFamilyMembers.entityType', 'State']]);
                //     }
                //     if (request()->header('entityType') == 'City') {
                //         $data->where([['patientFamilyMembers.providerLocationId', $providerLocation], ['patientFamilyMembers.entityType', 'City']]);
                //     }
                //     if (request()->header('entityType') == 'subLocation') {
                //         $data->where([['patientFamilyMembers.providerLocationId', $providerLocation], ['patientFamilyMembers.entityType', 'subLocation']]);
                //     }
                // }
                // if (request()->header('programId')) {
                //     $program = Helper::programId();
                //     $entityType = Helper::entityType();
                //     $data->where([['patientFamilyMembers.programId', $program], ['patientFamilyMembers.entityType', $entityType]]);
                // }
                $data = $data->where('patientFamilyMembers.userId', auth()->user()->id)->first();
                return fractal()->item($data)->transformWith(new PatientFamilyMemberTransformer())->toArray();
            } else {
                $data = User::select('users.*')->leftJoin('providerLocations', 'providerLocations.id', '=', 'users.providerLocationId');
                if (request()->header('providerId')) {
                    $provider = Helper::providerId();
                    $data->where('users.providerId', $provider);
                }
                if (request()->header('providerLocationId')) {
                    $providerLocation = Helper::providerLocationId();
                    if (request()->header('entityType') == 'Country') {
                        $data->where([['users.providerLocationId', $providerLocation], ['users.entityType', 'Country']]);
                    }
                    if (request()->header('entityType') == 'State') {
                        $data->where([['users.providerLocationId', $providerLocation], ['users.entityType', 'State']]);
                    }
                    if (request()->header('entityType') == 'City') {
                        $data->where([['users.providerLocationId', $providerLocation], ['users.entityType', 'City']]);
                    }
                    if (request()->header('entityType') == 'subLocation') {
                        $data->where([['users.providerLocationId', $providerLocation], ['users.entityType', 'subLocation']]);
                    }
                }
                if (request()->header('programId')) {
                    $program = Helper::programId();
                    $entityType = Helper::entityType();
                    $data->where([['users.programId', $program], ['users.entityType', $entityType]]);
                }
                $data = $data->where('users.id', auth()->user()->id)->first();
                return fractal()->item($data)->transformWith(new UserTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Profile
    public function profile($request)
    {
        try {
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            if (auth()->user()->roleId == 4) {
                $data = [
                    "nickName" => $request->nickname,
                    "phoneNumber" => $request->contact_no,
                    "updatedBy" => Auth::user()->id,
                    'providerId' => $providerId,
                    'providerLocationId' => $providerLocation
                ];
                Patient::where('userId', Auth::user()->id)->update($data);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patients', 'tableId' => Auth::user()->patient->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($data), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);

                $file = array();
                if (!empty($request->path)) {
                    if (strpos($request->path, "http") === false) {
                        $file['profilePhoto'] = str_replace(str_replace("public", "", URL::to('/') . '/'), "", $request->path);
                    }

                    $file['providerId'] = $providerId;
                    $file['providerLocationId'] = $providerLocation;
                }
                User::where('id', Auth::user()->id)->update($file);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => Auth::user()->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($file), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
                $user = User::where('udid', Auth::user()->udid)->first();
                return fractal()->item($user->patient)->transformWith(new PatientTransformer(false))->toArray();
            } elseif (auth()->user()->roleId == 6) {
                $family = [
                    "phoneNumber" => $request->phoneNumber,
                    "contactTypeId" => $request->contactType,
                    "contactTimeId" => $request->contactTime,
                    "updatedBy" => Auth::user()->id,
                    'providerId' => $providerId,
                    'providerLocationId' => $providerLocation
                ];
                PatientFamilyMember::where('userId', auth()->user()->id)->update($family);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientFamilyMembers', 'tableId' => Auth::user()->familyMember->id,
                    'value' => json_encode($family), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);
                $file = array();
                if (!empty($request->path)) {
                    if (strpos($request->path, "http") === false) {
                        $file['profilePhoto'] = str_replace(str_replace("public", "", URL::to('/') . '/'), "", $request->path);
                    }
                    $file['providerId'] = $providerId;
                    $file['providerLocationId'] = $providerLocation;
                }
                User::where('id', Auth::user()->id)->update($file);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => Auth::user()->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($file), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
                $user = PatientFamilyMember::where('userId', auth()->user()->id)->first();
                return fractal()->item($user)->transformWith(new PatientFamilyMemberTransformer(true))->toArray();
            } else {
                $staff = [
                    "phoneNumber" => $request->phoneNumber,
                    "updatedBy" => Auth::user()->id,
                    'providerId' => $providerId,
                    'providerLocationId' => $providerLocation
                ];
                Staff::where('userId', Auth::user()->id)->update($staff);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'staffs', 'tableId' => Auth::user()->staff->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($staff), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
                $file = array();
                if (!empty($request->path)) {
                    if (strpos($request->path, "http") === false) {
                        $file['profilePhoto'] = str_replace(str_replace("public", "", URL::to('/') . '/'), "", $request->path);
                    }
                    $file['providerId'] = $providerId;
                    $file['providerLocationId'] = $providerLocation;
                }
                User::where('id', Auth::user()->id)->update($file);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => Auth::user()->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($file), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
                $user = User::where('udid', Auth::user()->udid)->first();
                return fractal()->item($user)->transformWith(new UserTransformer(true))->toArray();
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List User
    public function userList($request, $id)
    {
        try {
            $data = User::select('users.*');

            // $data->leftJoin('providers', 'providers.id', '=', 'users.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'users.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('users.providerLocationId', '=', 'providerLocations.id')->where('users.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('users.providerLocationId', '=', 'providerLocationStates.id')->where('users.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('users.providerLocationId', '=', 'providerLocationCities.id')->where('users.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('users.providerLocationId', '=', 'subLocations.id')->where('users.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('users.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['users.providerLocationId', $providerLocation], ['users.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['users.providerLocationId', $providerLocation], ['users.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['users.providerLocationId', $providerLocation], ['users.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['users.providerLocationId', $providerLocation], ['users.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['users.programId', $program], ['users.entityType', $entityType]]);
            // }
            $patient = Patient::where('userId', $id)->first();
            if ($patient) {
                $data = $data->where('users.id', $id)->whereHas('patient', function ($query) use ($id) {
                    $query->where('userId', $id);
                })->first();
            } else {
                $data = $data->where('users.id', $id)->whereHas('staff', function ($query) use ($id) {
                    $query->where('userId', $id);
                })->first();
            }
            return fractal()->item($data)->transformWith(new UserTransformer(true))->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Change Password
    public function passwordChange(Request $request)
    {
        try {
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $password = ['password' => Hash::make($request->newPassword), 'providerId' => $providerId, 'providerLocationId' => $providerLocation];
            User::find(auth()->user()->id)->update($password);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => Auth::user()->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation,
                'value' => json_encode($password), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            return response()->json(['message' => trans('messages.changePassword')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // First Login
    public function loginFirst(Request $request)
    {
        try {
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $data = ['firstLogin' => 0, 'providerId' => $providerId, 'providerLocationId' => $providerLocation];
            User::where('id', auth()->user()->id)->update($data);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => Auth::user()->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation,
                'value' => json_encode($data), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            return response()->json(['message' => trans('messages.updatedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Forget Password
    public function forgotPassword(Request $request)
    {
        try {

            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $post = $request->all();
            $result = array();
            if (isset($post["email"]) && !empty($post["email"])) {
                $email = $post["email"];
                $result = User::where('email', $email)->first();
                // print_r( $result ); die('STOP');
                if (!empty($result) && isset($result->udid)) {
                    $data = DB::select(
                        'CALL findUserByUserId("' . $result->id . '")',
                    );
                    if (isset($data[0]) && !empty($data[0])) {
                        $result = $data[0];
                    }
                }

            } else {
                $email = "";
            }

            if (isset($post["phone"]) && !empty($post["phone"])) {
                $phone = $post["phone"];
                $data = DB::select(
                    'CALL findUserByPhone("' . $phone . '")',
                );

                if (isset($data[0]) && !empty($data[0])) {
                    $result = $data[0];
                }
            } else {
                $phone = "";
            }

            if (empty($email) && empty($phone)) {
                return response()->json(['message' => "Required Email"], 500);
            }

            if ($result && isset($result->udid)) {
                $code = Helper::randomString(50);
                $base_url = env('APP_URL', null);
                if ($base_url == null) {
                    $base_url = URL();
                }
                $forgetToken = [
                    'forgetToken' => $code,
                    'providerId' => $providerId,
                    'providerLocationId' => $providerLocation
                ];
                User::where("udid", $result->udid)->update($forgetToken);
                $forgotUrl = $base_url . "#/setup-password?code=" . $code;
                $variablesArr = array(
                    "forgotUrl" => $forgotUrl
                );
                if (isset($result->phoneNumber)) {
                    $msgSMSObj = ConfigMessage::where("type", "forgotPassword")
                        ->where("entityType", "sendSMS")
                        ->first();
                    if (isset($msgSMSObj->messageBody)) {
                        $messageBody = $msgSMSObj->messageBody;
                        Helper::getMessageBody($messageBody, $variablesArr);
                    }
                }
                $emailData = [
                    'email' => $result->email,
                    'firstName' => $result->firstName,
                    'template_name' => 'forgot_password'
                ];
                event(new SetUpPasswordEvent($emailData));
                return response()->json(['message' => trans('messages.forgetPasswordSuccess')]);
            } else {
                return response()->json(['message' => trans('messages.forgetPasswordSuccess')]);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // New Password
    public function newPassword(Request $request)
    {
        try {
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $get = $request->all();
            if (isset($get["code"]) && !empty($get["code"])) {
                if (isset($get["newPassword"]) && !empty($get["newPassword"])) {
                    $newPassword = $get["newPassword"];
                } else {
                    $newPassword = "";
                }
                if (isset($get["confirmNewPassword"]) && !empty($get["confirmNewPassword"])) {
                    $confirmNewPassword = $get["confirmNewPassword"];
                } else {
                    $confirmNewPassword = "";
                }
                if ($confirmNewPassword !== $newPassword) {
                    return response()->json(['message' => "New Password and Confirm Password must be match."], 500);
                }
                $result = User::where('forgetToken', $get["code"])->first();
                if ($result) {
                    $pass = Hash::check($confirmNewPassword, $result->password);
                    if (!$pass) {
                        $password = ['password' => Hash::make($newPassword), 'providerId' => $providerId];
                        User::find($result->id)->update($password);
                        $forgetToken = ['forgetToken' => "", 'providerId' => $providerId];
                        User::find($result->id)->update($forgetToken);
                        $changeLog = [
                            'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => $result->id, 'providerId' => $providerId,
                            'value' => json_encode($password), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                        ];
                        ChangeLog::create($changeLog);
                        return response()->json(['message' => "Password Changed Successfully."]);
                    } else {
                        return response()->json(['message' => " You Enter previous password "], 500);
                    }
                } else {
                    return response()->json(['message' => "Invalid code."], 500);
                }
            } else {
                return response()->json(['message' => "Required code."], 500);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Forget Password Code Verify
    public function forgotPasswordCodeVerify(Request $request)
    {
        try {
            $get = $request->all();
            if (isset($get["code"]) && !empty($get["code"])) {
                $result = User::where('forgetToken', $get["code"])->first();
                if (isset($result->forgetToken)) {
                    if ($result->forgetToken !== null || $result->forgetToken !== "") {
                        return response()->json(['data' => true, 'message' => trans('messages.code_exist')]);
                    } else {
                        return response()->json(['data' => false, 'message' => trans('messages.not_found')], 404);
                    }
                }
                return response()->json(['data' => false, 'message' => trans('messages.not_found')], 404);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Test Mail Sent to User
    public function testMail(Request $request)
    {
        try {
            $post = $request->all();
            $to = $post["sendTo"];

            $msgObj = ConfigMessage::where("type", "forgotPassword")
                ->where("entityType", "sendMail")
                ->first();
            $forgotUrl = "abc.com";
            $variablesArr = array(
                "forgotUrl" => $forgotUrl
            );

            if (isset($msgObj->messageBody)) {
                $messageBody = $msgObj->messageBody;
                $message = Helper::getMessageBody($messageBody, $variablesArr);
            } else {
                $message = "Please follow link bellow for password reset: " . $forgotUrl;
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
                $subject = "Reset Password Link";
            }
            Helper::commonMailjet($to, $fromName, $message, $subject);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add and Update User Setting
    public function userSetting($request)
    {
        try {
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $userId = auth()->user()->id;
            $data = UserSetting::where([['userId', $userId], ['config', $request->input('config')]])->first();
            if (!empty($data)) {
                $input = [
                    'udid' => Str::uuid()->toString(),
                    'userId' => $userId,
                    'setting' => $request->input('setting'),
                    'config' => $request->input('config'),
                    'providerId' => $providerId,
                    'providerLocationId' => $providerLocation
                ];
                UserSetting::where([['userId', $userId], ['config', $request->input('config')]])->update($input);
            } else {
                $input = [
                    'udid' => Str::uuid()->toString(),
                    'userId' => $userId,
                    'setting' => $request->input('setting'),
                    'config' => $request->input('config'),
                    'providerId' => $providerId,
                    'providerLocationId' => $providerLocation
                ];
                UserSetting::create($input);
            }
            return response()->json(['message' => trans('messages.createdSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // User Setting List
    public function userSettingList($request)
    {
        try {
            $userId = auth()->user()->id;
            $config = $request->input('config');
            $data = UserSetting::select('userSettings.*')->where('userId', $userId);

            // $data->leftJoin('providers', 'providers.id', '=', 'userSettings.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'userSettings.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('userSettings.providerLocationId', '=', 'providerLocations.id')->where('userSettings.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('userSettings.providerLocationId', '=', 'providerLocationStates.id')->where('userSettings.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('userSettings.providerLocationId', '=', 'providerLocationCities.id')->where('userSettings.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('userSettings.providerLocationId', '=', 'subLocations.id')->where('userSettings.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('userSettings.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['userSettings.providerLocationId', $providerLocation], ['userSettings.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['userSettings.providerLocationId', $providerLocation], ['userSettings.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['userSettings.providerLocationId', $providerLocation], ['userSettings.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['userSettings.providerLocationId', $providerLocation], ['userSettings.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['userSettings.programId', $program], ['userSettings.entityType', $entityType]]);
            // }

            if (!empty($config)) {
                $data = $data->where('config', $config)->first();
                if (!empty($data)) {
                    return fractal()->item($data)->transformWith(new UserSettingTransformer())->toArray();
                } else {
                    return "{}";
                }
            } else {
                $data = $data->get();
                return fractal()->collection($data)->transformWith(new UserSettingTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getSMS($request)
    {
        try {
            $data = $request->all();
            $fromNo = $data[0]['message']['from'];
            $from = str_replace("+91", "", $fromNo);
            $toNo = $data[0]['to'];
            $to = str_replace("+1", "", $toNo);
            $subject = $data[0]['message']['text'];
            $body = $data[0]['description'];
            $data = Patient::where('phoneNumber', $from)->first();
            if (!is_null($data)) {
                $userId = $data->userId;
                $careTeam = PatientStaff::where([['isPrimary', 1], ['isCareTeam', 0], ['patientId', $data->id]])->first();
                if (!is_null($careTeam)) {
                    $refrenceData = Staff::where('id', $careTeam->staffId)->first();
                    $refrenceId = $refrenceData->userId;
                    $communication = Communication::where([['from', $userId], ['subject', 'SMS']])->first();
                    if (is_null($communication)) {
                        $comm = [
                            'udid' => Str::uuid()->toString(),
                            'from' => $userId,
                            'referenceId' => $refrenceId,
                            'messageTypeId' => '327',
                            'subject' => 'SMS',
                            'priorityId' => '70',
                            'messageCategoryId' => '40',
                            'createdBy' => $userId,
                            'entityType' => 'staff',
                        ];
                        $commData = Communication::create($comm);
                        $communicationMessageInput = [
                            'communicationId' => $commData->id,
                            'message' => $subject,
                            'createdBy' => $userId,
                            'udid' => Str::uuid()->toString()
                        ];
                        CommunicationMessage::create($communicationMessageInput);

                        $unprocessData = [
                            'udid' => Str::uuid()->toString(),
                            'communicationId' => $commData->id,
                            'from' => $from,
                            'to' => $to,
                            'type' => 'SMS',
                            'subject' => 'SMS',
                            'message' => $subject,
                        ];
                        CommunicationInbound::create($unprocessData);

                        $notificationData = [
                            'body' => "You have received new SMS from" . ' ' . ucfirst($data->lastName) . ' ' . ucfirst($data->firstName),
                            'title' => 'SMS Communication',
                            'userId' => $refrenceId,
                            'isSent' => 0,
                            'entity' => 'Communication',
                            'referenceId' => $userId,
                            'createdBy' => $userId,
                        ];
                        Notification::create($notificationData);
                    } else {
                        $communicationMessageInput = [
                            'communicationId' => $communication->id,
                            'message' => $subject,
                            'createdBy' => $userId,
                            'udid' => Str::uuid()->toString()
                        ];
                        CommunicationMessage::create($communicationMessageInput);
                        $notificationData = [
                            'body' => "You have received new SMS from" . ' ' . ucfirst($data->lastName) . ' ' . ucfirst($data->firstName),
                            'title' => 'SMS Communication',
                            'userId' => $refrenceId,
                            'isSent' => 0,
                            'entity' => 'Communication',
                            'referenceId' => $userId,
                            'createdBy' => $userId,
                        ];
                        Notification::create($notificationData);
                    }
                } elseif ($careTeam = PatientStaff::where([['isPrimary', 0], ['isCareTeam', 1], ['patientId', $data->id]])->first()) {
                    if (!is_null($careTeam)) {
                        $refrenceData = Staff::where('id', $careTeam->staffId)->first();
                        $refrenceId = $refrenceData->userId;
                        $communication = Communication::where([['from', $userId], ['subject', 'SMS']])->first();
                        if (is_null($communication)) {
                            $comm = [
                                'udid' => Str::uuid()->toString(),
                                'from' => $userId,
                                'referenceId' => $refrenceId,
                                'messageTypeId' => '105',
                                'subject' => 'SMS',
                                'priorityId' => '70',
                                'messageCategoryId' => '40',
                                'createdBy' => $userId,
                                'entityType' => 'staff',
                            ];
                            $commData = Communication::create($comm);
                            $communicationMessageInput = [
                                'communicationId' => $commData->id,
                                'message' => $subject,
                                'createdBy' => $userId,
                                'udid' => Str::uuid()->toString()
                            ];
                            CommunicationMessage::create($communicationMessageInput);

                            $unprocessData = [
                                'udid' => Str::uuid()->toString(),
                                'communicationId' => $commData->id,
                                'from' => $from,
                                'to' => $to,
                                'type' => 'SMS',
                                'subject' => 'SMS',
                                'message' => $subject,
                            ];
                            CommunicationInbound::create($unprocessData);

                            $notificationData = [
                                'body' => "You have received new SMS from" . ' ' . ucfirst($data->lastName) . ' ' . ucfirst($data->firstName),
                                'title' => 'SMS Communication',
                                'userId' => $refrenceId,
                                'isSent' => 0,
                                'entity' => 'Communication',
                                'referenceId' => $userId,
                                'createdBy' => $userId,
                            ];
                            Notification::create($notificationData);
                        } else {
                            $communicationMessageInput = [
                                'communicationId' => $communication->id,
                                'message' => $subject,
                                'createdBy' => $userId,
                                'udid' => Str::uuid()->toString()
                            ];
                            CommunicationMessage::create($communicationMessageInput);

                            $notificationData = [
                                'body' => "You have received new SMS from" . ' ' . ucfirst($data->lastName) . ' ' . ucfirst($data->firstName),
                                'title' => 'SMS Communication',
                                'userId' => $refrenceId,
                                'isSent' => 0,
                                'entity' => 'Communication',
                                'referenceId' => $userId,
                                'createdBy' => $userId,
                            ];
                            Notification::create($notificationData);
                        }
                    }
                } else {
                    $unprocessData = [
                        'udid' => Str::uuid()->toString(),
                        'from' => $from,
                        'to' => $to,
                        'type' => 'SMS',
                        'subject' => 'SMS',
                        'message' => $subject,
                    ];
                    CommunicationInbound::create($unprocessData);
                }
            } else {
                $data = Staff::where('phoneNumber', $from)->first();
                if (!is_null($data)) {
                    $unprocessData = [
                        'udid' => Str::uuid()->toString(),
                        'from' => $from,
                        'to' => $to,
                        'type' => 'SMS',
                        'subject' => 'SMS',
                        'message' => $subject,
                    ];
                    CommunicationInbound::create($unprocessData);
                } else {
                    $inbound = CommunicationInbound::where([['from', $from], ['communicationId', '!=', null]])->first();
                    if (!is_null($inbound)) {
                        $user = Communication::where('id', $inbound->communicationId)->first();
                        $userId = $user->from;
                        $communicationMessageInput = [
                            'communicationId' => $inbound->communicationId,
                            'message' => $body,
                            'createdBy' => $userId,
                            'udid' => Str::uuid()->toString()
                        ];
                        CommunicationMessage::create($communicationMessageInput);
                    } else {
                        $unprocessData = [
                            'udid' => Str::uuid()->toString(),
                            'from' => $from,
                            'to' => $to,
                            'type' => 'SMS',
                            'subject' => 'SMS',
                            'message' => $subject,
                        ];
                        CommunicationInbound::create($unprocessData);
                    }
                }
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function sentSMS($request)
    {
        try {
            $sentTo = $request->input('mobile');
            $message = $request->input('message');
            Helper::sendBandwidthMessage($message, $sentTo);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getMail($request)
    {
        try {
            $cm = new ClientManager('config/imap.php');
            $cm = new ClientManager($options = []);
            $client = $cm->account('default');
            $client = $cm->make([
                'host' => 'imap.gmail.com',
                'port' => 993,
                'encryption' => 'ssl',
                'validate_cert' => true,
                'username' => 'ajay.kushwaha19@gmail.com',
                'password' => 'vuanchfpkuensytc',
                'protocol' => 'imap'
            ]);
            $client->connect();
            $folder = $client->getFolder('INBOX');
            $messages = $folder->messages()->unseen()->get();
            foreach ($messages as $message) {
                $result = $message->getHeader();
                $email = $result->from[0]->mail;
                $data = User::where('email', $email)->first();
                if (!empty($data)) {
                    $subject = $message->getSubject()[0];
                    $body = $message->getTextBody(true);
                    $refrenceId = '1';
                    if ($data->roleId == 4) {
                        $patient = Patient::where('userId', $data->id)->first();
                        $careTeam = PatientStaff::where([['isPrimary', 1], ['isCareTeam', 0], ['patientId', $patient->id]])->first();
                        $refrenceData = Staff::where('id', $careTeam->staffId)->first();
                        $refrenceId = $refrenceData->userId;
                    }
                    $comm = [
                        'udid' => Str::uuid()->toString(),
                        'from' => $data->id,
                        'referenceId' => $refrenceId,
                        'messageTypeId' => '105',
                        'subject' => $subject,
                        'priorityId' => '70',
                        'messageCategoryId' => '40',
                        'createdBy' => $data->id,
                        'entityType' => 'staff',
                    ];
                    $commData = Communication::create($comm);
                    $communicationMessageInput = [
                        'communicationId' => $commData->id,
                        'message' => $body,
                        'createdBy' => $data->id,
                        'udid' => Str::uuid()->toString()
                    ];
                    CommunicationMessage::create($communicationMessageInput);
                }
            }
            $returnData = ['message' => trans('messages.createdSuccesfully')];
            return $returnData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
