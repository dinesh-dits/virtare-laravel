<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Carbon\Carbon;
use App\Models\Flag\Flag;
use App\Models\Note\Note;
use App\Models\User\User;
use App\Models\Group\Group;
use App\Models\PatientLogs;
use App\Models\Staff\Staff;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\Client\CareTeam;
use App\Models\Patient\Patient;
use App\Models\Client\Site\Site;
use App\Models\Vital\VitalField;
use App\Models\Document\Document;
use App\Models\Referral\Referral;
use App\Library\ErrorLogGenerator;
use App\Models\Device\DeviceModel;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory\Inventory;
use App\Models\Patient\PatientFlag;
use App\Models\Patient\PatientGoal;
use App\Models\Task\TaskAssignedTo;
use App\Models\Patient\PatientGroup;
use App\Models\Patient\PatientStaff;
use App\Models\Patient\PatientVital;
use App\Models\Patient\TimeLineType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Client\CareTeamMember;
use App\Models\Escalation\Escalation;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Patient\PatientDevice;
use App\Models\CPTCode\CptCodeService;
use App\Models\Patient\PatientProgram;
use App\Models\Patient\PatientTimeLog;
use App\Models\Appointment\Appointment;
use App\Models\Patient\PatientProvider;
use App\Models\Patient\PatientReferral;
use App\Models\Patient\PatientTimeLine;
use App\Models\Patient\PatientCondition;
use App\Models\Patient\PatientInsurance;
use App\Models\Patient\PatientInventory;
use App\Models\Patient\PatientPhysician;
use App\Models\TimeApproval\TimeApproval;
use App\Models\Patient\PatientResponsible;
use App\Models\Communication\Communication;
use App\Models\ConfigMessage\ConfigMessage;
use App\Models\NonCompliance\NonCompliance;
use App\Models\Patient\PatientCriticalNote;
use App\Models\Patient\PatientFamilyMember;
use App\Models\Patient\PatientMedicalHistory;
use App\Models\Patient\PatientMedicalRoutine;
use App\Models\Patient\PatientEmergencyContact;
use App\Transformers\Patient\PatientTransformer;
use App\Services\Api\CptCodeServiceDetailService;
use App\Transformers\Referral\ReferralTransformer;
use App\Transformers\Patient\PatientFlagTransformer;
use App\Models\Communication\CommunicationCallRecord;
use App\Transformers\Patient\PatientGroupTransformer;
use App\Transformers\Patient\PatientVitalTransformer;
use App\Transformers\Patient\PatientDeviceTransformer;
use App\Transformers\Patient\PatientMedicalTransformer;
use App\Transformers\Patient\PatientProgramTransformer;
use App\Transformers\Patient\PatientTimelineTransformer;
use App\Transformers\Patient\PatientConditionTransformer;
use App\Transformers\Patient\PatientInsuranceTransformer;
use App\Transformers\Patient\PatientInventoryTransformer;
use App\Transformers\Patient\PatientPhysicianTransformer;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Services\Api\CPTCodeService as CPTCodeServiceClass;
use App\Transformers\Patient\PatientResponsibleTransformer;
use App\Transformers\Patient\PatientFamilyMemberTransformer;
use App\Transformers\Patient\PatientTimeLineTypeTransformer;
use App\Transformers\Patient\PatientMedicalRoutineTransformer;
use App\Transformers\Patient\PatientCriticalNoteTransformer as PatientPatientCriticalNoteTransformer;
use App\Services\Api\UserService;
use App\Events\SetUpPasswordEvent;


class PatientService
{
    // Add And Update  Patient
    public function patientCreate($request, $id)
    {

        DB::beginTransaction();
        try {
            // $patient = Patient::where('id',98)->first();
            // event(new PateientIntakeEvent($patient));

            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $postData = $request->all();
            if (!$id) {
                // Added Ptient details in User Table
                if (isset($postData["isApp"]) && $postData["isApp"] == true) {
                    if (isset($postData["phoneNumber"]) && !empty($postData["phoneNumber"])) {
                        $phoneNumber = $postData["phoneNumber"];
                        $phoneUserDefined = 1;
                    } else {
                        $phoneNumber = "0123456789";
                        $phoneUserDefined = 0;
                    }

                    if (isset($postData["email"]) && !empty($postData["email"])) {
                        $email = $postData["email"];
                        $emailUserDefined = 1;
                    } else {
                        $strTime = time();
                        $firstName = $request->input('firstName');
                        $firstName = str_replace(" ", "", $firstName);
                        $firstName = strtolower($firstName);
                        $emailStr = $firstName . $strTime . "@tethr.health.com";
                        $email = $emailStr;
                        $emailUserDefined = 0;
                    }
                } else {
                    $phoneNumber = $request->input('phoneNumber');
                    $email = $request->input('email');
                    $phoneUserDefined = 1;
                    $emailUserDefined = 1;
                }

                $password = Str::random("10");

                $user = [
                    'password' => Hash::make($password), 'email' => $email, 'udid' => Str::uuid()->toString(), 'entityType' => $entityType,
                    'emailVerify' => 1, 'createdBy' => Auth::id(), 'roleId' => 4, 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'userDefined' => $emailUserDefined
                ];
                $data = User::create($user);
                $phoneNumber = $request->input('phoneNumber');

                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => $data->id, 'entityType' => $entityType,
                    'value' => json_encode($user), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);

                // Added  patient details in Patient Table
                $patient = [
                    'firstName' => $request->input('firstName'), 'middleName' => $request->input('middleName'), 'lastName' => $request->input('lastName'),
                    'dob' => $request->input('dob'), 'genderId' => $request->input('gender'), 'languageId' => $request->input('language'), 'otherLanguageId' => json_encode($request->input('otherLanguage')),
                    'nickName' => $request->input('nickName'), 'userId' => $data->id, 'phoneNumber' => $phoneNumber, 'contactTypeId' => json_encode($request->input('contactType')),
                    'contactTimeId' => json_encode($request->input('contactTime')), 'medicalRecordNumber' => "", 'countryId' => $request->input('country'), 'entityType' => $entityType, 'userDefined' => $phoneUserDefined,
                    'stateId' => $request->input('state'), 'city' => $request->input('city'), 'zipCode' => $request->input('zipCode'), 'appartment' => $request->input('appartment'),
                    'address' => $request->input('address'), 'createdBy' => Auth::id(), 'height' => $request->input('height'), 'weight' => $request->input('weight'), 'bitrixId' => $request->input('bitrix'),
                    'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'placeOfServiceId' => $request->input('placeOfService'), 'heightInCentimeter' => $request->input('heightInCentimeter')
                ];

                if (isset($postData["isApp"]) && $postData["isApp"] == true) {
                    $patient["isApp"] = 1;
                }

                $newData = Patient::create($patient);


                /*if ($request->input('defaultProvider')) {
                    $defaultProvider = [
                        'udid' => Str::uuid()->toString(), 'providerId' => $request->input('defaultProvider'), 'createdBy' => Auth::id(), 'isDefault' => 1,
                        'patientId' => $newData->id
                    ];
                    PatientProvider::create($defaultProvider);
                }*/
                if ($request->input('providers')) {
                    $careTeams = CareTeam::whereIn('udid', $request->input('providers'))->get();
                    if ($careTeams->count() > 0) {
                        $defaultProvider = array();
                        foreach ($careTeams as $key => $provider) {
                            $defaultProvider[$key] = [
                                'udid' => Str::uuid()->toString(), 'providerId' => $provider->udid, 'createdBy' => Auth::id(), 'isDefault' => 0,
                                'patientId' => $newData->id
                            ];
                        }
                        PatientProvider::insert($defaultProvider);
                    }
                }
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patients', 'tableId' => $newData->id, 'entityType' => $entityType,
                    'value' => json_encode($patient), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);
                if (isset($postData["phoneNumber"]) && !empty($postData["phoneNumber"])) {
                    $msgSMSObj = ConfigMessage::where("type", "patientAdd")
                        ->where("entityType", "sendSMS")
                        ->first();
                    $variablesArr = array(
                        "password" => $password
                    );

                    $message = '';
                    $message .= "<p>Hi " . $request->input('firstName') . '' . $request->input('lastName') . ",</p>";
                    $message .= "<p>Your account was successfully created with Virtare Health. Your password is " . $password . "</p>";
                    $message .= "<p>Thanks</p>";
                    $message .= "<p>Virtare Health</p>";


                    if (isset($msgSMSObj->messageBody)) {
                        $messageBody = $msgSMSObj->messageBody;
                        $message = Helper::getMessageBody($messageBody, $variablesArr);
                    }
                    $responseApi = Helper::sendBandwidthMessage($message, $postData["phoneNumber"]);
                }

                $emailData = [
                    'email' => $request->email,
                    'firstName' => $request->firstName,
                    'template_name' => 'welcome_email'
                ];
                event(new SetUpPasswordEvent($emailData));

                // if (isset($postData["email"]) && !empty($postData["email"])) {

                //     $to = $request->email;
                //     $msgObj = ConfigMessage::where("type", "patientAdd")
                //         ->where("entityType", "sendMail")
                //         ->first();
                //     $msgHeaderObj = ConfigMessage::where("type", "header")
                //         ->where("entityType", "sendMail")
                //         ->first();
                //     $msgFooterObj = ConfigMessage::where("type", "footer")
                //         ->where("entityType", "sendMail")
                //         ->first();
                //     $fullName = ucfirst($request->input('firstName')) . ' ' . ucfirst($request->input('lastName'));
                //     $variablesArr = array(
                //         "fullName" => $fullName,
                //         "password" => $password,
                //         "userEmail" => $email
                //     );
                //     if (isset($msgObj->messageBody)) {
                //         $messageBody = $msgObj->messageBody;
                //         if (isset($msgHeaderObj->messageBody) && !empty($msgHeaderObj->messageBody)) {
                //             $messageBody = $msgHeaderObj->messageBody . $messageBody;
                //         }
                //         if (isset($msgFooterObj->messageBody) && !empty($msgFooterObj->messageBody)) {
                //             $messageBody = $messageBody . $msgFooterObj->messageBody;
                //         }
                //         $message = Helper::getMessageBody($messageBody, $variablesArr);
                //     }
                //     if (isset($msgObj->otherParameter)) {
                //         $otherParameter = json_decode($msgObj->otherParameter);
                //         if (isset($otherParameter->fromName)) {
                //             $fromName = $otherParameter->fromName;
                //         }
                //     } else {
                //         $fromName = "Virtare";
                //     }
                //     if (isset($msgObj->subject)) {
                //         $subject = $msgObj->subject;
                //     } else {
                //         $subject = "Reset Password";
                //     }
                //     Helper::commonMailjet($to, $fromName, $message, $subject);
                // }

                $medicalRecordNumber = "VH" . date('y') . str_pad($newData->id, 8, "0", STR_PAD_LEFT);

                Patient::where("id", $newData->id)->update(['medicalRecordNumber' => $medicalRecordNumber]);
                /*$flag = ['udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'patientId' => $newData->id, 'flagId' => 4];
                PatientFlag::create($flag);*/
                $timeLine = [
                    'patientId' => $newData->id, 'heading' => 'Patient Intake', 'title' => $newData->lastName . ',' . ' ' . $newData->firstName . ' ' . $newData->middleName . ' ' . 'Added to platform', 'type' => 1,
                    'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                ];
                $timeline = PatientTimeLine::create($timeLine);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientTimelines', 'tableId' => $timeline->id, 'entityType' => $entityType,
                    'value' => json_encode($timeLine), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);
                $getPatient = Patient::where('udid', $newData->udid)->with(
                    'user',
                    'family.user',
                    'emergency',
                    'gender',
                    'language',
                    'contactType',
                    'contactTime',
                    'state',
                    'country',
                    'otherLanguage',
                    'flags.flag'
                )->first();
                // $workflowAssign =  new PateientIntakeListener();
                // $assign = $workflowAssign->handle($getPatient->id);
                //  event(new PateientIntakeEvent($getPatient));
                $userdata = fractal()->item($getPatient)->transformWith(new PatientTransformer(false))->toArray();
                $message = ['message' => trans('messages.createdSuccesfully')];
            } else {
                $usersId = Patient::where('udid', $id)->first();
                $uId = $usersId->userId;
                // Updated Ptient details in User Table
                $user = [
                    'email' => $request->input('email'),
                    'updatedBy' => Auth::id(),
                    'providerId' => $providerId,
                    'providerLocationId' => $providerLocation,
                    'entityType' => $entityType,
                    'userDefined' => $request->input('emailUserDefined')
                ];
                User::where('id', $uId)->update($user);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => $uId, 'entityType' => $entityType,
                    'value' => json_encode($user), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);
                // Updated  patient details in Patient Table

                $isApp = $request->input('isApp') == true ? 1 : 0;
                $patient = [
                    'firstName' => $request->input('firstName'), 'middleName' => $request->input('middleName'), 'lastName' => $request->input('lastName'),
                    'dob' => $request->input('dob'), 'genderId' => $request->input('gender'), 'languageId' => $request->input('language'), 'otherLanguageId' => json_encode($request->input('otherLanguage')),
                    'nickName' => $request->input('nickName'), 'phoneNumber' => $request->input('phoneNumber'), 'contactTypeId' => json_encode($request->input('contactType')),
                    'contactTimeId' => json_encode($request->input('contactTime')), 'countryId' => $request->input('country'), 'entityType' => $entityType, 'userDefined' => $request->input('phoneUserDefined'),
                    'stateId' => $request->input('state'), 'city' => $request->input('city'), 'zipCode' => $request->input('zipCode'), 'appartment' => $request->input('appartment'),
                    'address' => $request->input('address'), 'updatedBy' => Auth::id(), 'height' => $request->input('height'), 'weight' => $request->input('weight'),
                    'placeOfServiceId' => $request->input('placeOfService'), 'heightInCentimeter' => $request->input('heightInCentimeter'), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'isApp' => $isApp
                ];

                $newData = Patient::where('udid', $id)->update($patient);

                if ($request->input('isApp') == false && $usersId->getOriginal('isApp') == 1) {    // Send link to create Password
                    $myRequest = new \Illuminate\Http\Request();
                    $myRequest->setMethod('POST');
                    $myRequest->request->add(['email' => $request->input('email')]);
                    (new UserService)->forgotPassword($myRequest);
                    //  $patientId = Helper::sendpasswordresetLink($uId,$basicInfo);
                }
                $logs['recordId'] = $usersId->id;
                $logs['ip_address'] = $request->ip();
                $logs['previousData'] = json_encode($usersId);
                $logs['newData'] = json_encode($patient);
                $logs['date'] = date('Y-m-d H:i:s', time());

                if ($request->input('providers')) {
                    PatientProvider::where(['patientId' => $usersId->id])->delete();
                    $careTeams = CareTeam::whereIn('udid', $request->input('providers'))->get();
                    if ($careTeams->count() > 0) {
                        $defaultProvider = array();
                        foreach ($careTeams as $key => $provider) {
                            $defaultProvider[$key] = [
                                'udid' => Str::uuid()->toString(),
                                'providerId' => $provider->id,
                                'createdBy' => Auth::id(),
                                'isDefault' => 0,
                                'patientId' => $usersId->id
                            ];
                        }
                        PatientProvider::insert($defaultProvider);
                    }
                }
                PatientLogs::create($logs);
                $patientId = Helper::tableName('App\Models\Patient\Patient', $id);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patients', 'tableId' => $patientId, 'providerId' => $providerId, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($patient), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                ];
                ChangeLog::create($changeLog);
                $getPatient = Patient::where('udid', $id)->with(
                    'user',
                    'family.user',
                    'emergency',
                    'gender',
                    'language',
                    'contactType',
                    'contactTime',
                    'state',
                    'country',
                    'otherLanguage',
                    'flags.flag'
                )->first();
                $userdata = fractal()->item($getPatient)->transformWith(new PatientTransformer())->toArray();
                $message = ['message' => trans('messages.updatedSuccesfully')];
            }
            DB::commit();
            $endData = array_merge($message, $userdata);
            if (isset($postData["isApp"]) && $postData["isApp"] == true) {
            } else {
                Helper::updateFreeswitchUser();
            }
            return $endData;
        } catch (Exception $e) {
            echo $e->getMessage() . '' . $e->getLine() . '' . $e->getFile();
            //  DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Patient Listing
    public function patientList($request, $id)
    {
        try {
            if ((!empty($request->input('fromDate')) && !empty($request->input('toDate')))) {
                $fromDateStr = Helper::date($request->input('fromDate'));
                $toDateStr = Helper::date($request->input('toDate'));
                $date1 = date_create($fromDateStr);
                $date2 = date_create($toDateStr);
                $diff = date_diff($date1, $date2);
                $diffrence = $diff->format("%a");
            }
            $roleId = auth()->user()->roleId;
            if ($id) {
                $select = 'patients.*';
            } else {
                $select = array('patients.nonCompliance', 'patients.weight', 'patients.genderId', 'patients.dob', 'patients.id', 'patients.udid', 'patients.firstName', 'patients.middleName', 'patients.lastName', 'patients.userId', 'patients.isActive', 'patients.isApp');
            }

            $patient = Patient::select($select)->with('user', 'family', 'emergency', 'vitals', 'flags')
                ->leftJoin('users', 'users.id', '=', 'patients.userId')
                ->leftJoin('patientStaffs', 'patientStaffs.patientId', '=', 'patients.id')
                ->leftJoin('patientFamilyMembers', 'patientFamilyMembers.patientId', '=', 'patients.id')
                ->leftJoin('patientFlags', 'patientFlags.patientId', '=', 'patients.id')
                ->leftJoin('flags', 'flags.id', '=', 'patientFlags.flagId')
                ->leftJoin('patientProviders', 'patientProviders.patientId', '=', 'patients.id')
                // ->leftJoin('patientLocations', 'patientLocations.patientId', '=', 'patients.id')
                // ->leftJoin('patientGroups', 'patientGroups.patientId', '=', 'patients.id')
                ->whereNull('patients.deletedAt')
                ->whereNull('patientFlags.deletedAt');

            // $patient->leftJoin('providers', 'providers.id', '=', 'patientProviders.providerId')->whereNull('providers.deletedAt');
            // $patient->leftJoin('groups', 'groups.groupId', '=', 'patientGroups.groupId')->whereNull('groups.deletedAt');
            // $patient->leftJoin('programs', 'programs.id', '=', 'patients.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $patient->leftJoin('providerLocations', function ($join) {
            //     $join->on('patientLocations.locationId', '=', 'providerLocations.id')->where('patientLocations.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $patient->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('patientLocations.locationId', '=', 'providerLocationStates.id')->where('patientLocations.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $patient->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('patientLocations.locationId', '=', 'providerLocationCities.id')->where('patientLocations.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $patient->leftJoin('subLocations', function ($join) {
            //     $join->on('patientLocations.locationId', '=', 'subLocations.id')->where('patientLocations.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $patient->where('patientProviders.providerId', $provider);
            // }

            // if (request()->header('groupId')) {
            //     $groupId = Group::where('udid', request()->header('groupId'))->first();
            //     $patient->where('patientGroups.groupId', $groupId->groupId);
            // }

            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $patient->where([['patientLocations.locationId', $providerLocation], ['patientLocations.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $patient->where([['patientLocations.locationId', $providerLocation], ['patientLocations.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $patient->where([['patientLocations.locationId', $providerLocation], ['patientLocations.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $patient->where([['patientLocations.locationId', $providerLocation], ['patientLocations.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $patient->where([['patients.programId', $program], ['patients.entityType', $entityType]]);
            // }
            if ($id) {
                // $patientId = Helper::entity('patient', $id);
                // $notAccess = Helper::haveAccess($patientId);
                // if (!$notAccess) {
                //     if ($roleId == 3) {
                //         if (Helper::haveAccessAction(null, 62)) {
                //             $patient->where('patients.id', $patientId);
                //         } else {
                //             $patient->where('patientStaffs.patientId', $patientId);
                //         }
                //     } elseif ($roleId == 6) {
                //         $patient->where('patientFamilyMembers.patientId', $patientId);
                //     } elseif ($roleId == 1) {
                //         $patient->where('patients.id', $patientId);
                //     } elseif ($roleId == 4) {
                //         $patient->where('patients.id', $patientId);
                //     }
                // } elseif ($roleId == 4) {
                //     $patient->where('patients.id', auth()->user()->patient->id)->first();
                // } else {
                //     return $notAccess;
                // }
                $patient = $patient->where('patients.udid', $id)->first();
                return fractal()->item($patient)->transformWith(new PatientTransformer(true))->toArray();
            } else {
                // if ($roleId == 3) {
                //     if (Helper::haveAccessAction(null, 62)) {
                //         $patient;
                //     } else {
                //         $patient->where('staffId', auth()->user()->staff->id);
                //     }
                // } elseif ($roleId == 6) {
                //     $patient->where('patientFamilyMembers.id', auth()->user()->familyMember->id);
                // } elseif ($roleId == 4) {
                //     $patient = $patient->where('patients.id', auth()->user()->patient->id)->first();
                //     return fractal()->item($patient)->transformWith(new PatientTransformer())->toArray();
                // } else {
                //     if($roleId == 1){
                //         $patient;
                //     }else{
                //         // if (Helper::haveAccessAction(null, 62)) {
                //         //     $patient;
                //         // } else {
                //             $patient->where('staffId', auth()->user()->staff->id);
                //         // }
                //     }
                // }

                $siteHead = Site::where(['siteHead' => Auth::id()])->first();
                if (auth()->user()->roleId == 2) {
                    $client = Staff::where(['userId' => Auth::id()])->get('clientId');
                    $careTeam = CareTeam::whereIn('clientId', $client)->get('udid');
                    $patient->whereIn('patientProviders.providerId', $careTeam);
                    $patient->orWhere(function ($query) {
                        $query->where('patients.createdBy', Auth::id());
                    });
                } elseif (auth()->user()->roleId == 5 || auth()->user()->roleId == 7 || auth()->user()->roleId == 9) {
                    if ($siteHead) {
                        $careTeam = CareTeam::where(['siteId' => $siteHead->id])->get('udid');
                    } else {
                        $careTeam = CareTeamMember::where(['contactId' => Auth::id()])->get('careTeamId');
                    }
                    $patient->whereIn('patientProviders.providerId', $careTeam);
                } else {
                    $patient;
                }


                if ($request->search) {
                    if ($request->isActive) {
                        $patient->where([['patients.isActive', 1], [DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%"]])
                            ->orWhere([['patients.isActive', 1], [DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%"]])
                            ->orWhere([['patients.isActive', 1], [DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%"]])
                            ->orWhere([['patients.isActive', 1], [DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%"]]);
                    } else {
                        if ($roleId == 3) {
                            $currentStaff = auth()->user()->staff->id;
                        } elseif ($roleId == 6) {
                            $currentStaff = auth()->user()->familyMember->id;
                        }

                        if ($roleId == 3 || $roleId == 6) {
                            $patient->where(function ($query) use ($currentStaff, $request) {
                                $query->where(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%");
                                $query->orWhere([['staffId', $currentStaff], [DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%"]]);
                                $query->orWhere([['staffId', $currentStaff], [DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%"]]);
                                $query->orWhere([['staffId', $currentStaff], [DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%"]]);
                            });
                        } else {
                            $patient->where(function ($query) use ($request) {
                                $query->where(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%");
                                $query->orWhere(DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`middleName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%");
                                $query->orWhere(DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%");
                                $query->orWhere(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%");
                            });
                        }
                    }
                }
                if ($request->isActive) {
                    $patient->where('patients.isActive', 1);
                }
                if ($request->filter) {
                    if ($request->filter === 'Active Patients') {
                        $patient->where('patients.isActive', 1);
                    } elseif ($request->filter === 'Inactive Patients') {
                        $patient->where('patients.isActive', 0);
                    } elseif ($request->filter === 'Total Patients') {
                        $patient->where('patients.isActive', '=', 1)
                            ->orWhere('patients.isActive', '=', 0);
                    } elseif ($request->filter === 'New Patients') {
                        if ((!empty($request->input('fromDate')) && !empty($request->input('toDate')))) {
                            $patient->where([['patients.createdAt', '>=', $fromDateStr], ['patients.createdAt', '<=', $toDateStr]]);
                        } else {
                            $patient->where('patients.isActive', '=', 1)
                                ->orWhere('patients.isActive', '=', 0);
                        }
                    } else {
                        $patient->where('flags.name', $request->filter);
                        $patient->whereNull('patients.deletedAt')->whereNull('patientFlags.deletedAt');
                    }
                }
                if (!empty($fromDateStr) && !empty($toDateStr)) {

                    if (
                        $request->filter === 'Escalation' || $request->filter === 'Critical' || $request->filter === 'Moderate' || $request->filter === 'WNL'
                        || $request->filter === 'Watchlist' || $request->filter === 'Trending' || $request->filter === 'Message' || $request->filter === 'Communication' || $request->filter === 'Work Status'
                    ) {
                        if ($diffrence < 3) {
                            $patient->whereNull('patients.deletedAt')->whereNull('patientFlags.deletedAt');
                        } else {
                            $patient->where(function ($query) use ($fromDateStr) {
                                $query->where('patientFlags.createdAt', '>=', $fromDateStr)->orWhereNull('patientFlags.deletedAt');
                            });
                        }
                    } else {
                        $patient->whereBetween('patients.createdAt', [$fromDateStr, $toDateStr]);
                    }
                }
                if ($request->orderField === 'firstName' || $request->orderField === 'lastName' || $request->orderField === 'weight') {
                    $patient->orderBy($request->orderField, $request->orderBy);
                } elseif ($request->orderField === 'fullName') {
                    $patient->orderBy('lastName', $request->orderBy);
                } elseif ($request->orderField === 'compliance') {
                    $patient->orderBy('nonCompliance', $request->orderBy);
                } elseif ($request->orderField === 'nonCompliance') {
                    $patient->orderBy('nonCompliance', $request->orderBy);
                } elseif ($request->orderField === 'flagTmeStamp') {
                    $patient->orderBy('patientFlags.createdAt', $request->orderBy);
                } elseif ($request->orderField === 'lastMessageSent') {
                    $message = DB::select(
                        "CALL messagePriority()"
                    );
                    $messageData = array();
                    foreach ($message as $value) {
                        array_push($messageData, $value->id);
                    }
                    $patient->leftJoin('communications', 'communications.referenceId', '=', 'patients.userId')
                        ->leftJoin('messages', 'messages.communicationId', '=', 'communications.id')
                        ->orderBy('messages.message', $request->orderBy)
                        ->where(function ($query) use ($messageData) {
                            $query->whereIn('messages.message', $messageData)
                                ->orWhereNull('messages.deletedAt')
                                ->orWhereNull('communications.deletedAt');
                        });
                } elseif ($request->orderField === 'bp') {
                    $vitalField = DB::select(
                        "CALL vitalFieldId('" . 1 . "','" . '' . "')"
                    );
                    //    Print_r( $vitalField ); die('STOP');
                    $array = array();
                    foreach ($vitalField as $value) {
                        array_push($array, $value->id);
                    }
                    if ($vitalField) {
                        $patient->leftJoin('patientVitals', 'patientVitals.patientId', '=', 'patients.id')
                            ->orderBy('patientVitals.value', $request->orderBy)
                            ->where(function ($query) use ($array) {
                                $query->whereIn('patientVitals.id', $array)
                                    ->where('patientVitals.deviceTypeId', '=', 99)
                                    ->orWhereNull('patientVitals.deviceTypeId')
                                    ->orWhereNull('patientVitals.id');
                            });
                    }
                } elseif ($request->orderField === 'spo2') {
                    $vitalField = DB::select(
                        "CALL vitalFieldId('" . 4 . "','" . ' ' . "')"
                    );
                    $array = array();
                    foreach ($vitalField as $value) {
                        array_push($array, $value->id);
                    }
                    if ($vitalField) {
                        $patient->leftJoin('patientVitals', 'patientVitals.patientId', '=', 'patients.id')
                            ->orderBy('patientVitals.value', $request->orderBy)->where(function ($query) use ($array) {
                                $query->whereIn('patientVitals.id', $array)
                                    ->where('patientVitals.deviceTypeId', '=', 100)
                                    ->orWhereNull('patientVitals.deviceTypeId')
                                    ->orWhereNull('patientVitals.id');
                            });
                    }
                } elseif ($request->orderField === 'glucose') {
                    $vitalField = DB::select(
                        "CALL vitalFieldId('" . '' . "','" . 101 . "')"
                    );
                    $array = array();
                    foreach ($vitalField as $value) {
                        array_push($array, $value->id);
                    }
                    if ($vitalField) {
                        $patient->leftJoin('patientVitals', 'patientVitals.patientId', '=', 'patients.id')
                            ->orderBy('patientVitals.value', $request->orderBy)->where(function ($query) use ($array) {
                                $query->whereIn('patientVitals.id', $array)
                                    ->where('patientVitals.deviceTypeId', '=', 101)
                                    ->orWhereNull('patientVitals.deviceTypeId')
                                    ->orWhereNull('patientVitals.id');
                            });
                    }
                } elseif ($request->orderField === 'age') {
                    if ($request->orderBy === 'ASC') {
                        $patient->orderBy('dob', 'DESC');
                    } else {
                        $patient->orderBy('dob', 'ASC');
                    }
                } elseif ($request->orderField === 'gender') {
                    $patient->join('globalCodes', 'globalCodes.id', '=', 'patients.genderId')
                        ->orderBy('globalCodes.name', $request->orderBy);
                } else {
                    $patient->orderBy('firstName', 'ASC');
                }
                $patient = $patient->groupBy('patients.id')->paginate(env('PER_PAGE', 20));
                return fractal()->collection($patient)->transformWith(new PatientTransformer(false))->paginateWith(new IlluminatePaginatorAdapter($patient))->toArray();
            }
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Delete patient
    public function patientDelete($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $data = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            $patient = Patient::where('udid', $id)->first();
            if ($patient->inventories) {
                foreach ($patient->inventories as $value) {
                    $input = ['isAvailable' => 1];
                    Inventory::where('id', $value->inventory->id)->update($input);
                }
            }
            $user = $patient->userId;
            $tables = [
                User::where('id', $user),
                Patient::where('udid', $id),
                PatientVital::where('patientId', $patient->id),
                PatientProgram::where('patientId', $patient->id),
                PatientInsurance::where('patientId', $patient->id),
                PatientInventory::where('patientId', $patient->id),
                PatientPhysician::where('patientId', $patient->id),
                PatientFamilyMember::where('patientId', $patient->id),
                PatientMedicalHistory::where('patientId', $patient->id),
                PatientMedicalRoutine::where('patientId', $patient->id),
                PatientFlag::where('patientId', $patient->id),
                PatientCondition::where('patientId', $patient->id),
                PatientEmergencyContact::where('patientId', $patient->id),
                PatientCriticalNote::where('patientId', $patient->id),
                PatientDevice::where('patientId', $patient->id),
                PatientGoal::where('patientId', $patient->id),
                PatientReferral::where('patientId', $patient->id),
                PatientStaff::where('patientId', $patient->id),
                Appointment::where('patientId', $patient->id),
                PatientTimeLog::where('patientId', $patient->id),
                TimeApproval::where('patientId', $patient->id),
                CommunicationCallRecord::where('patientId', $patient->id),
                TaskAssignedTo::where([['assignedTo', $patient->id], ['entityType', 'patient']]),
                Communication::where([['referenceId', $user], ['entityType', 'patient']]),
                Communication::where('from', $user),
                Document::where([['referanceId', $patient->id], ['entityType', 'patient']]),
                Escalation::where([['referenceId', $patient->id], ['entityType', 'patient']]),
            ];
            foreach ($tables as $table) {
                $table->update($data);
                $table->delete();
            }
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Add And Update Patient Condition
    public function patientConditionCreate($request, $id, $conditionId)
    {
        DB::beginTransaction();
        try {
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $patient = Helper::entity('patient', $id);
            if ($request->input('startDate')) {
                $startDate = Helper::dateOnly($request->input('startDate'));
            } else {
                $startDate = NULL;
            }
            if ($request->input('endDate')) {
                $endDate = Helper::dateOnly($request->input('endDate'));
            } else {
                $endDate = NULL;
            }
            if (!$conditionId) {
                $conditions = $request->input('condition');
                foreach ($conditions as $condition) {
                    $con = PatientCondition::where([['patientId', $patient], ['conditionId', $condition]])->whereNull('endDate')->first();
                    if (!$con) {
                        $input = [
                            'conditionId' => $condition, 'patientId' => $patient, 'udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'entityType' => $entityType,
                            'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'startDate' => $startDate, 'endDate' => $endDate
                        ];
                        $conditionData = PatientCondition::create($input);
                        $changeLog = [
                            'udid' => Str::uuid()->toString(), 'table' => 'patientConditions', 'tableId' => $conditionData->id, 'entityType' => $entityType,
                            'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                        ];
                        ChangeLog::create($changeLog);
                        $getPatient = PatientCondition::where('patientId', $patient)->with('patient')->get();
                        $userdata = fractal()->collection($getPatient)->transformWith(new PatientConditionTransformer())->toArray();
                        $message = ["message" => trans('messages.addedSuccesfully')];
                    } else {
                        return response()->json(['condition' => array(trans('messages.condition'))], 422);
                    }
                }
            } else {
                $input = ['endDate' => $endDate, 'startDate' => $startDate, 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType];
                PatientCondition::where('udid', $conditionId)->update($input);
                $conditionData = PatientCondition::where('udid', $conditionId)->first();
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientConditions', 'tableId' => $conditionData->id, 'entityType' => $entityType,
                    'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);
                $getPatient = PatientCondition::where('patientId', $patient)->with('patient')->get();
                $userdata = fractal()->collection($getPatient)->transformWith(new PatientConditionTransformer())->toArray();
                $message = ["message" => trans('messages.updatedSuccesfully')];
            }
            DB::commit();
            return array_merge($message, $userdata);
        } catch (Exception $e) {
            DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // List Patient condition
    public function patientConditionList($request, $id, $conditionId)
    {
        try {
            $patient = Helper::entity('patient', $id);
            $data = PatientCondition::with('patient', 'condition')->select('patientConditions.*')->where('patientConditions.patientId', $patient);

            // $data->leftJoin('providers', 'providers.id', '=', 'patientConditions.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'patientConditions.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('patientConditions.providerLocationId', '=', 'providerLocations.id')->where('patientConditions.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('patientConditions.providerLocationId', '=', 'providerLocationStates.id')->where('patientConditions.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('patientConditions.providerLocationId', '=', 'providerLocationCities.id')->where('patientConditions.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('patientConditions.providerLocationId', '=', 'subLocations.id')->where('patientConditions.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('patientConditions.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['patientConditions.providerLocationId', $providerLocation], ['patientConditions.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['patientConditions.providerLocationId', $providerLocation], ['patientConditions.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['patientConditions.providerLocationId', $providerLocation], ['providerPrograms.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['patientConditions.providerLocationId', $providerLocation], ['patientConditions.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $patient->where([['patientConditions.programId', $program], ['patientConditions.entityType', $entityType]]);
            // }
            if ($conditionId) {
                $data = $data->where('patientConditions.udid', $conditionId)->first();
                return fractal()->item($data)->transformWith(new PatientConditionTransformer())->toArray();
            } else {
                $notAccess = Helper::haveAccess($patient);
                if (!$notAccess) {
                    if ($request->orderField == 'condition') {
                        $data->orderBy('createdAt', 'DESC');
                    } else {
                        $data->orderBy('createdAt', 'ASC');
                    }
                    $data = $data->get();
                    return fractal()->collection($data)->transformWith(new PatientConditionTransformer())->toArray();
                } else {
                    return $notAccess;
                }
            }
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Delete Patient condition
    public function patientConditionDelete($request, $id, $conditionId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $data = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType];
            PatientCondition::where('udid', $conditionId)->update($data);
            $input = PatientCondition::where('udid', $conditionId)->first();
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientConditions', 'tableId' => $input->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($data), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);
            PatientCondition::where('udid', $conditionId)->delete();
            DB::commit();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Add Patient Referals
    public function patientReferalsCreate($request, $id)
    {
        DB::beginTransaction();
        $endData = array();
        try {
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $patient = Helper::tableName('App\Models\Patient\Patient', $id);
            $patientInput = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0, 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType];
            PatientReferral::where('patientId', $patient)->update($patientInput);
            $referralData = PatientReferral::where('patientId', $patient)->first();
            if ($referralData) {
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientReferals', 'tableId' => $referralData->patientReferralId, 'entityType' => $entityType,
                    'value' => json_encode($patientInput), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);
            }
            PatientReferral::where('patientId', $patient)->delete();
            if (!empty($request->input('referralEmail'))) {
                $input = [
                    'firstName' => $request->input('firstName'), 'middleName' => $request->input('middleName'), 'lastName' => $request->input('lastName'), 'email' => $request->input('referralEmail'), 'udid' => Str::uuid()->toString(),
                    'fax' => $request->input('referralFax'), 'createdBy' => Auth::id(), 'phoneNumber' => $request->input('referralPhoneNumber'), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                ];
                $patientData = Referral::create($input);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'referrals', 'tableId' => $patientData->id, 'entityType' => $entityType,
                    'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);
                $input = [
                    'udid' => Str::uuid()->toString(), 'patientId' => $patient, 'referralId' => $patientData->id, 'createdBy' => Auth::id(),
                    'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                ];
                $data = PatientReferral::create($input);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientReferrals', 'tableId' => $data->id, 'entityType' => $entityType,
                    'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);
                $referral = Referral::where('id', $patientData->id)->first();
                $userdata = fractal()->item($referral)->transformWith(new ReferralTransformer())->toArray();
                $message = ['message' => trans('messages.createdSuccesfully')];
                $endData = array_merge($message, $userdata);
            } else if ($request->input('referral')) {
                $patient = Helper::tableName('App\Models\Patient\Patient', $id);
                $referal = Referral::where('udid', $request->input('referral'))->first();
                $input = [
                    'udid' => Str::uuid()->toString(), 'patientId' => $patient, 'referralId' => $referal->id, 'createdBy' => Auth::id(),
                    'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                ];
                $patientData = PatientReferral::create($input);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientReferrals', 'tableId' => $patientData->id, 'entityType' => $entityType,
                    'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);
                $referral = Referral::where('id', $patientData->referralId)->first();
                $userdata = fractal()->item($referral)->transformWith(new ReferralTransformer())->toArray();
                $message = ['message' => trans('messages.createdSuccesfully')];
                $endData = array_merge($message, $userdata);
            }
            DB::commit();
            return $endData;
        } catch (Exception $e) {
            DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Update Patient Referals
    public function patientReferalsUpdate($request, $id, $referalsId)
    {
        DB::beginTransaction();
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $referal = [];
            if (!empty($request->input('referralName'))) {
                $referal['name'] = $request->input('referralName');
            }
            if (!empty($request->input('referralEmail'))) {
                $referal['email'] = $request->input('referralEmail');
            }
            if (!empty($request->input('referralFax'))) {
                $referal['fax'] = $request->input('referralFax');
            }
            if (!empty($request->input('referralPhoneNumber'))) {
                $referal['phoneNumber'] = $request->input('referralPhoneNumber');
            }
            $referal['updatedBy'] = Auth::id();
            $referal['providerId'] = $provider;
            $referal['providerLocationId'] = $providerLocation;
            $patient = PatientReferral::where('udid', $referalsId)->update($referal);
            $input = Helper::tableName('App\Models\Patient\PatientReferal', $referalsId);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientReferals', 'tableId' => $input, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($referal), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            $getPatient = PatientReferral::where('udid', $referalsId)->with('patient', 'designation')->first();
            $userdata = fractal()->item($getPatient)->transformWith(new ReferralTransformer())->toArray();
            $message = ['message' => trans('messages.updatedSuccesfully')];
            DB::commit();
            return array_merge($message, $userdata);
        } catch (Exception $e) {
            DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // List Patient Referals
    public function listPatientReferral($request, $id)
    {
        try {
            $data = Referral::select('referrals.*');

            // $data->leftJoin('providers', 'providers.id', '=', 'referrals.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'referrals.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('referrals.providerLocationId', '=', 'providerLocations.id')->where('referrals.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('referrals.providerLocationId', '=', 'providerLocationStates.id')->where('referrals.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('referrals.providerLocationId', '=', 'providerLocationCities.id')->where('referrals.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('referrals.providerLocationId', '=', 'subLocations.id')->where('referrals.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('referrals.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['referrals.providerLocationId', $providerLocation], ['referrals.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['referrals.providerLocationId', $providerLocation], ['referrals.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['referrals.providerLocationId', $providerLocation], ['providerPrograms.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['referrals.providerLocationId', $providerLocation], ['referrals.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['referrals.programId', $program], ['referrals.entityType', $entityType]]);
            // }
            $patient = Patient::where('udid', $id)->first();
            $data = $data->whereHas('patientReferral', function ($query) use ($patient) {
                $query->where('patientId', $patient->id);
            })->first();
            if ($data) {
                return fractal()->item($data)->transformWith(new ReferralTransformer(false))->toArray();
            }
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Referals List
    public function referral($request)
    {
        try {
            $data = Referral::select("referrals.*", "patients.udid as patientUdid", "patients.firstName as patientFirstName", "patients.middleName as patientMiddleName", "patients.lastName as patientLastName")
                ->where(function ($query) use ($request) {
                    $query->where(DB::raw("CONCAT(trim(`referrals`.`firstName`), ' ', trim(`referrals`.`lastName`))"), 'LIKE', "%" . $request->search . "%");
                    $query->orwhere(DB::raw("CONCAT(trim(`referrals`.`lastName`), ' ', trim(`referrals`.`firstName`))"), 'LIKE', "%" . $request->search . "%");
                    $query->orwhere(DB::raw("CONCAT(trim(`patients`.`firstName`), ' ', trim(`patients`.`lastName`))"), 'LIKE', "%" . $request->search . "%");
                    $query->orwhere(DB::raw("CONCAT(trim(`patients`.`lastName`), ' ', trim(`patients`.`firstName`))"), 'LIKE', "%" . $request->search . "%");
                });
            $data->join('patientReferrals', 'patientReferrals.referralId', '=', 'referrals.id')
                ->join('patients', 'patients.id', '=', 'patientReferrals.patientId')->whereNull('patients.deletedAt')->whereNull('patientReferrals.deletedAt');

            // $data->leftJoin('providers', 'providers.id', '=', 'referrals.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'referrals.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('referrals.providerLocationId', '=', 'providerLocations.id')->where('referrals.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('referrals.providerLocationId', '=', 'providerLocationStates.id')->where('referrals.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('referrals.providerLocationId', '=', 'providerLocationCities.id')->where('referrals.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('referrals.providerLocationId', '=', 'subLocations.id')->where('referrals.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');


            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('referrals.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['referrals.providerLocationId', $providerLocation], ['referrals.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['referrals.providerLocationId', $providerLocation], ['referrals.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['referrals.providerLocationId', $providerLocation], ['referrals.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['referrals.providerLocationId', $providerLocation], ['referrals.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['referrals.programId', $program], ['referrals.entityType', $entityType]]);
            // }

            if ((!empty($request->input('fromDate')) && !empty($request->input('toDate')))) {
                $fromDateStr = Helper::date($request->input('fromDate'));
                $toDateStr = Helper::date($request->input('toDate'));
            }
            if ($request->filter) {
                $referal = Referral::where('udid', $request->filter)->first();
                if ($referal) {
                    $data->where('patientReferrals.referralId', $referal->id);
                }
                if ((!empty($request->input('fromDate')) && !empty($request->input('toDate')))) {
                    $data->where([['patientReferrals.createdAt', '>=', $fromDateStr], ['patientReferrals.createdAt', '<=', $toDateStr]]);
                }
            }
            if (!empty($fromDateStr) && !empty($toDateStr)) {
                $data->where([['patientReferrals.createdAt', '>=', $fromDateStr], ['patientReferrals.createdAt', '<=', $toDateStr]]);
            }
            if ($request->referral) {
                $data = Referral::where(DB::raw("CONCAT(trim(`firstName`), ' ', trim(`lastName`))"), 'LIKE', "%" . $request->search . "%")
                    ->orwhere(DB::raw("CONCAT(trim(`lastName`), ' ', trim(`firstName`))"), 'LIKE', "%" . $request->search . "%");
            }
            if ($request->orderField === 'name') {
                $data->orderBy(DB::raw("CONCAT(trim(`referrals`.`firstName`), ' ', trim(`referrals`.`lastName`))"), $request->orderBy);
            } elseif ($request->orderField === 'designation') {
                $data->Leftjoin('globalCodes', 'globalCodes.id', '=', 'referrals.designationId')
                    ->orderBy('globalCodes.name', $request->orderBy);
            } elseif ($request->orderField === 'email') {
                $data->orderBy('referrals.email', $request->orderBy);
            } elseif ($request->orderField === 'patientName') {
                $data->orderBy('patients.firstName', $request->orderBy);
            } else {
                $data->orderBy('referrals.firstName', 'ASC');
            }
            $data = $data->paginate(env('PER_PAGE', 20));
            return fractal()->collection($data)->transformWith(new ReferralTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Delete Patient Referals
    public function patientReferalsDelete($request, $id, $referalsId)
    {
        DB::beginTransaction();
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $data = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType];
            PatientReferral::where('udid', $referalsId)->update($data);
            $input = Helper::tableName('App\Models\Patient\PatientReferal', $referalsId);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientReferals', 'tableId' => $input, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($data), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);
            PatientReferral::where('udid', $referalsId)->delete();
            DB::commit();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Add And Update patient Physician
    public function patientPhysicianCreate($request, $id, $physicianId)
    {
        DB::beginTransaction();
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $postData = $request->all();
            $patient = Helper::entity('patient', $id);
            if (!$physicianId) {
                $password = Str::random("10");
                $user = [
                    'password' => Hash::make($password), 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType,
                    'email' => $request->input('email'), 'emailVerify' => 1, 'createdBy' => Auth::id(), 'roleId' => 5, 'udid' => Str::uuid()->toString()
                ];
                $userData = User::create($user);
                if (isset($postData["phoneNumber"]) && !empty($postData["phoneNumber"])) {
                    $message = "Your account was successfully created with Virtare Health. Your password is " . $password;
                    //$responseAPi = Helper::sendBandwidthMessage($message, $postData["phoneNumber"]);
                }
                if (isset($request->email)) {
                    $to = $request->email;
                    $msgObj = ConfigMessage::where("type", "patientAdd")
                        ->where("entityType", "sendMail")
                        ->first();
                    $msgHeaderObj = ConfigMessage::where("type", "header")
                        ->where("entityType", "sendMail")
                        ->first();

                    $msgFooterObj = ConfigMessage::where("type", "footer")
                        ->where("entityType", "sendMail")
                        ->first();

                    $variablesArr = array(
                        "password" => $password
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
                        $message = "Your password is " . $password;
                    }

                    if (isset($msgObj->otherParameter)) {
                        $otherParameter = json_decode($msgObj->otherParameter);
                        if (isset($otherParameter->fromName)) {
                            $fromName = $otherParameter->fromName;
                        }
                    } else {
                        $fromName = "Virtare";
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
                    };
                    Helper::commonMailjet($to, $fromName, $message, $subject, '', array(), 'Physician Created', '');
                }

                PatientPhysician::where('patientId', $patient)->update(['isPrimary' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType]);
                $input = [
                    'sameAsReferal' => $request->input('sameAsAbove'), 'patientId' => $patient, 'fax' => $request->input('fax'), 'entityType' => $entityType,
                    'createdBy' => Auth::id(), 'phoneNumber' => $request->input('phoneNumber'), 'userId' => $userData->id, 'designationId' => $request->input('designation'),
                    'name' => $request->input('name'), 'udid' => Str::uuid()->toString(), 'isPrimary' => 1, 'providerId' => $provider, 'providerLocationId' => $providerLocation
                ];
                $patientData = PatientPhysician::create($input);
                $getPatient = PatientPhysician::where('id', $patientData->id)->with('patient', 'designation', 'user')->first();
                $userdata = fractal()->item($getPatient)->transformWith(new PatientPhysicianTransformer())->toArray();
                $message = ["message" => trans('messages.addedSuccesfully')];
            } else {
                if ($request->input('isPrimary')) {
                    PatientPhysician::where('patientId', $patient)->update(['isPrimary' => 0]);
                    $isPrimary = $request->input('isPrimary');
                } else {
                    $isPrimary = $request->input('isPrimary');
                }
                $usersId = PatientPhysician::where('udid', $physicianId)->first();
                $uId = $usersId->userId;
                $userInput = [];
                if (!empty($request->input('email'))) {
                    $userInput['email'] = $request->input('email');
                }
                $userInput['updatedBy'] = Auth::id();
                $userData = User::where('id', $uId)->update($userInput);
                $physician = [];
                if (!empty($request->input('fax'))) {
                    $physician['fax'] = $request->input('fax');
                }
                if (!empty($request->input('phoneNumber'))) {
                    $physician['phoneNumber'] = $request->input('phoneNumber');
                }
                if (!empty($request->input('designation'))) {
                    $physician['designationId'] = $request->input('designation');
                }
                if (!empty($request->input('name'))) {
                    $physician['name'] = $request->input('name');
                }
                if (!empty($request->input('sameAsAbove'))) {
                    $physician['sameAsReferal'] = $request->input('sameAsAbove');
                }
                $physician['updatedBy'] = Auth::id();
                $physician['providerId'] = $provider;
                $physician['providerLocationId'] = $providerLocation;
                $physician['isPrimary'] = $isPrimary;
                $patient = PatientPhysician::where('udid', $physicianId)->update($physician);
                $getPatient = PatientPhysician::where('udid', $physicianId)->with('patient', 'designation', 'user')->first();
                $userdata = fractal()->item($getPatient)->transformWith(new PatientPhysicianTransformer())->toArray();
                $message = ['message' => trans('messages.updatedSuccesfully')];
            }
            DB::commit();
            return array_merge($message, $userdata);
        } catch (Exception $e) {
            DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // List Patient Physician
    public function patientPhysicianList($request, $id, $physicianId)
    {
        try {
            $patient = Helper::entity('patient', $id);
            $data = PatientPhysician::where('patientPhysicians.patientId', $patient)->select('patientPhysicians.*')->with('patient', 'designation', 'user')
                ->leftJoin('providerLocations', 'providerLocations.id', '=', 'patientPhysicians.providerLocationId');
            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('patientPhysicians.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['patientPhysicians.providerLocationId', $providerLocation], ['patientPhysicians.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['patientPhysicians.providerLocationId', $providerLocation], ['patientPhysicians.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['patientPhysicians.providerLocationId', $providerLocation], ['patientPhysicians.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['patientPhysicians.providerLocationId', $providerLocation], ['patientPhysicians.entityType', 'subLocation']]);
            //     }
            // }
            if ($physicianId) {
                $data = $data->where('patientPhysicians.udid', $physicianId)->first();
                return fractal()->item($data)->transformWith(new PatientPhysicianTransformer())->toArray();
            } else {
                $notAccess = Helper::haveAccess($patient);
                if (!$notAccess) {
                    $data = $data->orderBy('patientPhysicians.createdAt', 'DESC')->get();
                    return fractal()->collection($data)->transformWith(new PatientPhysicianTransformer())->toArray();
                } else {
                    return $notAccess;
                }
            }
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Delete Patient Physician
    public function patientPhysicianDelete($request, $id, $physicianId)
    {
        DB::beginTransaction();
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $data = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            PatientPhysician::where('udid', $physicianId)->update($data);
            $input = PatientPhysician::where('udid', $physicianId)->first();
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientPhysicians', 'tableId' => $input->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($data), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            PatientPhysician::where('udid', $physicianId)->delete();
            DB::commit();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Add And Update Patient Program
    public function patientProgramCreate($request, $id, $programId)
    {
        DB::beginTransaction();
        try {
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            if ($request->input('onboardingScheduleDate')) {
                $onboardingScheduleDate = Helper::date($request->input('onboardingScheduleDate'));
            } else {
                $onboardingScheduleDate = NULL;
            }
            if ($request->input('dischargeDate')) {
                $dischargeDate = Helper::date($request->input('dischargeDate'));
            } else {
                $dischargeDate = NULL;
            }
            if ($request->input('renewalDate')) {
                $renewalDate = Helper::date($request->input('renewalDate'));
            } else {
                $renewalDate = NULL;
            }
            if ($programId === null) {
                $patient = Helper::entity('patient', $id);
                $programsList = $request->input('program');
                if (!empty($programsList)) {
                    $programIds = [];
                    foreach ($programsList as $key => $IdProgram) {
                        $program = PatientProgram::where([['programtId', $IdProgram], ['patientId', $patient]])->first();
                        if (!$program) {
                            $input = [
                                'programtId' => $IdProgram, 'onboardingScheduleDate' => $onboardingScheduleDate, 'dischargeDate' => $dischargeDate, 'renewalDate' => $renewalDate,
                                'patientId' => $patient, 'createdBy' => Auth::id(), 'isActive' => $request->input('status'), 'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                            ];
                            $patientData = PatientProgram::create($input);
                            $programIds[$key] = $patientData->id;
                            $changeLog = [
                                'udid' => Str::uuid()->toString(), 'table' => 'patientPrograms', 'tableId' => $patientData->id,
                                'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId
                            ];
                            ChangeLog::create($changeLog);
                        }
                    }
                    $getPatient = PatientProgram::whereIn('id', $programIds)->with('patient', 'program')->get();
                    $userdata = fractal()->collection($getPatient)->transformWith(new PatientProgramTransformer())->toArray();
                    $message = ["message" => trans('messages.addedSuccesfully')];
                }
                /*else {
                    return response()->json(['program' => array(trans('messages.program'))], 422);
                }*/
            } else {

                $input = [
                    'programtId' => $request->input('program'), 'onboardingScheduleDate' => $onboardingScheduleDate, 'dischargeDate' => $dischargeDate, 'renewalDate' => $renewalDate,
                    'updatedBy' => Auth::id(), 'isActive' => $request->input('status'), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                ];

                $patient = PatientProgram::where('udid', $programId)->update($input);
                $patientData = Helper::tableName('App\Models\Patient\PatientProgram', $programId);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientPrograms', 'tableId' => $patientData, 'entityType' => $entityType,
                    'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);
                $getPatient = PatientProgram::where('udid', $programId)->with('patient', 'program')->first();
                $userdata = fractal()->item($getPatient)->transformWith(new PatientProgramTransformer())->toArray();
                $message = ['message' => trans('messages.updatedSuccesfully')];
            }
            DB::commit();
            return array_merge($message, $userdata);
        } catch (Exception $e) {
            DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }


    // Update Multiple Program For a Patient
    public function patientProgramUpdate($request, $id, $programId)
    {
        DB::beginTransaction();
        try {
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $input = [
                'updatedBy' => Auth::id(),
                'isActive' => $request->input('status'),
            ];

            if ($request->input('onboardingScheduleDate')) {
                $input["onboardingScheduleDate"] = Helper::date($request->input('onboardingScheduleDate'));
            }

            if ($request->input('dischargeDate')) {
                $input["dischargeDate"] = Helper::date($request->input('dischargeDate'));
                if (substr($input["dischargeDate"], 0, 10) < Carbon::now()->toDateString()) {
                    $input["isActive"] = 0;
                } elseif (substr($input["dischargeDate"], 0, 10) == Carbon::now()->toDateString()) {
                    $input["isActive"] = 1;
                }
            }
            if ($request->input('renewalDate')) {
                $input["renewalDate"] = Helper::date($request->input('renewalDate'));
            }

            $userdata = [];
            if ($request->input('patientProgram')) {
                $programsList = $request->input('patientProgram');
                if (!empty($programsList)) {
                    foreach ($programsList as $key => $IdProgram) {
                        $patient = PatientProgram::where('udid', $IdProgram)->update($input);
                        $patientData = Helper::tableName('App\Models\Patient\PatientProgram', $programId);
                        $changeLog = [
                            'udid' => Str::uuid()->toString(), 'table' => 'patientPrograms', 'tableId' => $IdProgram, 'entityType' => $entityType,
                            'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                        ];
                        ChangeLog::create($changeLog);
                        $getPatient = PatientProgram::where('udid', $programId)->with('patient', 'program')->first();
                    }
                }
                $userdata = $this->patientProgramList($request, $id, $programId);
                $message = ['message' => trans('messages.updatedSuccesfully')];
            } else {
                $message = ['message' => "Something went wrong!"];
            }
            DB::commit();
            return array_merge($message, $userdata);
        } catch (Exception $e) {
            DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // public function patientProgramUpdate($request, $id, $programId)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $input = [
    //             'updatedBy' => Auth::id(),
    //             'isActive' => $request->input('status'),
    //         ];

    //         if ($request->input('onboardingScheduleDate')) {
    //             $input["onboardingScheduleDate"] = Helper::date($request->input('onboardingScheduleDate'));
    //         }

    //         if ($request->input('dischargeDate')) {
    //             $input["dischargeDate"] = Helper::date($request->input('dischargeDate'));
    //         }

    //         if ($request->input('renewalDate')) {
    //             $input["renewalDate"] = Helper::date($request->input('renewalDate'));
    //         }

    //         $userdata = [];
    //         if ($request->input('patientProgram')) {
    //             $programsList = $request->input('patientProgram');
    //             if (!empty($programsList)) {
    //                 foreach ($programsList as $key => $IdProgram) {
    //                     $patient = PatientProgram::where('udid', $IdProgram)->update($input);
    //                     $patientData = Helper::tableName('App\Models\Patient\PatientProgram', $programId);
    //                     $changeLog = [
    //                         'udid' => Str::uuid()->toString(), 'table' => 'patientPrograms', 'tableId' => $patientData,
    //                         'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(),
    //                     ];
    //                     ChangeLog::create($changeLog);
    //                     $getPatient = PatientProgram::where('udid', $programId)->with('patient', 'program')->first();
    //                 }
    //             }
    //             $userdata = $this->patientProgramList($request, $id, $programId);
    //             $message = ['message' => trans('messages.updatedSuccesfully')];
    //         } else {
    //             $message = ['message' => "Something went wrong!"];
    //         }
    //         DB::commit();
    //         $endData = array_merge($message, $userdata);
    //         return $endData;
    //     } catch (Exception $e) {
    //         DB::rollback();
    //         if (isset(auth()->user()->id)) {
    //             $userId = auth()->user()->id;
    //         } else {
    //             $userId = "";
    //         }
    //         ErrorLogGenerator::createLog($request, $e, $userId);
    //         throw new \RuntimeException($e);
    //     }
    // }

    // List Patient Program
    public function patientProgramList($request, $id, $programId)
    {
        try {
            $patient = Helper::entity('patient', $id);
            $todayDate = Carbon::now()->toDateString();
            $data = PatientProgram::where('patientPrograms.patientId', $patient)
                ->select('patientPrograms.*')->with('patient', 'program');

            /* $data->leftJoin('providers', 'providers.id', '=', 'patientPrograms.providerId')
                 ->where('providers.isActive', 1)
                 ->whereNull('providers.deletedAt');
             $data->leftJoin('programs', 'programs.id', '=', 'patientPrograms.programId')
                 ->where('programs.isActive', 1)
                 ->whereNull('programs.deletedAt');
             $data->leftJoin('providerLocations', function ($join) {
                 $join->on('patientPrograms.providerLocationId', '=', 'providerLocations.id')
                     ->where('patientPrograms.entityType', '=', 'Country');
             })
                 ->whereNull('providerLocations.deletedAt');

             $data->leftJoin('providerLocationStates', function ($join) {
                 $join->on('patientPrograms.providerLocationId', '=', 'providerLocationStates.id')
                     ->where('patientPrograms.entityType', '=', 'State');
             })->whereNull('providerLocationStates.deletedAt');

             $data->leftJoin('providerLocationCities', function ($join) {
                 $join->on('patientPrograms.providerLocationId', '=', 'providerLocationCities.id')
                     ->where('patientPrograms.entityType', '=', 'City');
             })->whereNull('providerLocationCities.deletedAt');

             $data->leftJoin('subLocations', function ($join) {
                 $join->on('patientPrograms.providerLocationId', '=', 'subLocations.id')
                     ->where('patientPrograms.entityType', '=', 'subLocation');
             })->whereNull('subLocations.deletedAt');

             if (request()->header('providerId')) {
                 $provider = Helper::providerId();
                 $data->where('patientPrograms.providerId', $provider);
             }
             if (request()->header('providerLocationId')) {
                 $providerLocation = Helper::providerLocationId();
                 if (request()->header('entityType') == 'Country') {
                     $data->where([['patientPrograms.providerLocationId', $providerLocation], ['patientPrograms.entityType', 'Country']]);
                 }
                 if (request()->header('entityType') == 'State') {
                     $data->where([['patientPrograms.providerLocationId', $providerLocation], ['patientPrograms.entityType', 'State']]);
                 }
                 if (request()->header('entityType') == 'City') {
                     $data->where([['patientPrograms.providerLocationId', $providerLocation], ['patientPrograms.entityType', 'City']]);
                 }
                 if (request()->header('entityType') == 'subLocation') {
                     $data->where([['patientPrograms.providerLocationId', $providerLocation], ['patientPrograms.entityType', 'subLocation']]);
                 }
             }
             if (request()->header('programId')) {
                 $program = Helper::programId();
                 $entityType = Helper::entityType();
                 $data->where([['patientPrograms.programId', $program], ['patientPrograms.entityType', $entityType]]);
             }*/
            if ($programId) {
                $data = $data->where('patientPrograms.udid', $programId)->first();
                if (!is_null($data)) {
                    return fractal()->item($data)->transformWith(new PatientProgramTransformer())->toArray();
                } else {
                    $data = [];
                    return response()->json(['data' => $data]);
                }
            } else {
                $notAccess = Helper::haveAccess($patient);
                if (!$notAccess) {
                    $data = $data->get();
                    return fractal()->collection($data)->transformWith(new PatientProgramTransformer())->toArray();
                } else {
                    return $notAccess;
                }
            }
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Delete Patient Program
    public function patientProgramDelete($request, $id, $programId)
    {
        DB::beginTransaction();
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $data = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType];
            PatientProgram::where('udid', $programId)->update($data);
            $input = Helper::tableName('App\Models\Patient\PatientProgram', $programId);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientPrograms', 'tableId' => $input, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($data), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);
            PatientProgram::where('udid', $programId)->delete();
            DB::commit();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Add And Update Patient Inventory
    public function patientInventoryCreate($request, $id, $inventoryId)
    {
        DB::beginTransaction();
        try {
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            if (!$inventoryId) {
                $patientData = Patient::where('udid', $id)->first();
                $deviceType = Inventory::where('id', $request->input('inventory'))->with('model')->first();
                $deviceAssigned = PatientInventory::where('patientId', $patientData->id)->join('inventories', 'inventories.id', '=', 'patientInventories.inventoryId')->join('deviceModels', 'deviceModels.id', '=', 'inventories.deviceModelId')->where('deviceModels.deviceTypeId', $deviceType->model['deviceTypeId'])->first();
                if (!$deviceAssigned) {
                    $input = [
                        'inventoryId' => $request->input('inventory'), 'patientId' => $patientData->id, 'createdBy' => Auth::id(),
                        'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                    ];
                    $patient = PatientInventory::create($input);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patientInventories', 'tableId' => $patient->id, 'entityType' => $entityType,
                        'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                    ];
                    ChangeLog::create($changeLog);
                    $inventory = Inventory::where('id', $patient->inventoryId)->first();
                    $inventoryData = array('isAvailable' => 0);
                    Inventory::where('id', $patient->inventoryId)->update($inventoryData);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'inventories', 'tableId' => $patient->inventoryId, 'entityType' => $entityType,
                        'value' => json_encode($inventoryData), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                    ];
                    ChangeLog::create($changeLog);
                    $deviceModel = DeviceModel::where('id', $inventory->deviceModelId)->first();
                    $device = GlobalCode::where('id', $deviceModel->deviceTypeId)->first();
                    $deviceType = $device->name;
                    $timeLine = [
                        'patientId' => $patientData->id, 'heading' => 'Device Assigned', 'title' => $deviceType . ' ' . ' Device Assigned to ' . ' ' . $patientData->lastName . ',' . ' ' . $patientData->firstName . ' ' . $patientData->middleName, 'type' => 3,
                        'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                    ];
                    $timeline = PatientTimeLine::create($timeLine);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patientTimelines', 'tableId' => $timeline->id, 'entityType' => $entityType,
                        'value' => json_encode($timeLine), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                    ];
                    ChangeLog::create($changeLog);
                    $getPatient = PatientInventory::where('id', $patient->id)->with('patient', 'inventory', 'deviceTypes')->first();
                    $userdata = fractal()->item($getPatient)->transformWith(new PatientInventoryTransformer())->toArray();
                    $message = ["message" => trans('messages.addedSuccesfully')];
                } else {
                    return response()->json(['message' => 'Device Already Assigned to Patient'], 409);
                }
            } else {
                $input = [
                    'isActive' => $request->input('status'), 'updatedBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                ];
                $patient = PatientInventory::where('udid', $inventoryId)->update($input);
                $patientInput = Helper::tableName('App\Models\Patient\PatientInventory', $inventoryId);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientInventories', 'tableId' => $patientInput, 'providerId' => $providerId, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                ];
                ChangeLog::create($changeLog);
                $getPatient = PatientInventory::where('udid', $inventoryId)->with('patient', 'inventory', 'deviceTypes')->first();
                $userdata = fractal()->item($getPatient)->transformWith(new PatientInventoryTransformer())->toArray();
                $message = ['message' => trans('messages.updatedSuccesfully')];
            }
            DB::commit();
            return array_merge($message, $userdata);
        } catch (Exception $e) {
            DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // List Patient Inventory
    public function patientInventoryList($request, $id, $inventoryId)
    {
        try {
            $patient = Helper::entity('patient', $id);
            $data = PatientInventory::where('patientInventories.patientId', $patient)->select('patientInventories.*')->with('patient', 'inventory', 'deviceTypes');

            // $data->leftJoin('providers', 'providers.id', '=', 'patientInventories.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'patientInventories.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('patientInventories.providerLocationId', '=', 'providerLocations.id')->where('patientInventories.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('patientInventories.providerLocationId', '=', 'providerLocationStates.id')->where('patientInventories.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('patientInventories.providerLocationId', '=', 'providerLocationCities.id')->where('patientInventories.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('patientInventories.providerLocationId', '=', 'subLocations.id')->where('patientInventories.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('patientInventories.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['patientInventories.providerLocationId', $providerLocation], ['patientInventories.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['patientInventories.providerLocationId', $providerLocation], ['patientInventories.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['patientInventories.providerLocationId', $providerLocation], ['patientInventories.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['patientInventories.providerLocationId', $providerLocation], ['patientInventories.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['patientInventories.programId', $program], ['patientInventories.entityType', $entityType]]);
            // }
            if ($inventoryId) {
                $getPatient = $data->where('patientInventories.udid', $inventoryId)->first();
                return fractal()->item($getPatient)->transformWith(new PatientInventoryTransformer())->toArray();
            } else {
                $notAccess = Helper::haveAccess($patient);
                if (!$notAccess) {
                    $getPatient = $data->latest()->get();
                    return fractal()->collection($getPatient)->transformWith(new PatientInventoryTransformer())->toArray();
                } else {
                    return $notAccess;
                }
            }
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Delete Patient Inventory
    public function patientInventoryDelete($request, $id, $inventoryId)
    {
        DB::beginTransaction();
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $patient = PatientInventory::where('udid', $inventoryId)->first();
            $patientData = Patient::where('udid', $id)->first();
            $inventory = Inventory::where('id', $patient->inventoryId)->first();
            $inventoryData = ['isAvailable' => 1, 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType];
            Inventory::where('id', $patient->inventoryId)->update($inventoryData);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'inventories', 'tableId' => $patient->inventoryId, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($inventoryData), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);
            $deviceModel = DeviceModel::where('id', $inventory->deviceModelId)->first();
            $device = GlobalCode::where('id', $deviceModel->deviceTypeId)->first();
            $deviceType = $device->name;
            $timeLine = [
                'patientId' => $patientData->id, 'heading' => 'Device Removed', 'title' => $deviceType . ' ' . ' Device Removed from ' . ' ' . $patientData->lastName . ',' . ' ' . $patientData->firstName . ' ' . $patientData->middleName, 'type' => 3,
                'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
            ];
            $timeline = PatientTimeLine::create($timeLine);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientTimelines', 'tableId' => $timeline->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($timeLine), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);
            $data = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType];
            PatientInventory::where('udid', $inventoryId)->update($data);
            PatientInventory::where('udid', $inventoryId)->delete();
            DB::commit();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Add And Update Patient Vitals
    public function patientVitalCreate($request, $id)
    {
        DB::beginTransaction();
        try {
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            if ($id) {
                $patientIdx = Helper::entity('patient', $id);
                $dataVital = $request->vital;
                $note = array();
                $vitalType = array();
                $vitalValue = array();
                $vitalUnits = array();
                $vitalFlag = array();
                $timeLine = array();
                foreach ($dataVital as $vital) {
                    if (!isset($vitalType[$vital['takeTime']])) {
                        $vitalType[$vital['takeTime']] = array();
                        $vitalValue[$vital['takeTime']] = array();
                        $vitalUnits[$vital['takeTime']] = array();
                    }
                    if ($vital['value'] == '') {
                        continue;
                    }
                    $vitalRecord = array();
                    if (!empty($vital['startTime'])) {
                        $vitalRecord['startTime'] = Helper::date($vital['startTime']);
                    };
                    if (!empty($vital['deviceType'])) {
                        $vitalRecord['deviceTypeId'] = $vital['deviceType'];
                    };
                    if (!empty($vital['units'])) {
                        $vitalRecord['units'] = $vital['units'];
                    }
                    if (!empty($vital['endTime'])) {
                        $vitalRecord['endTime'] = Helper::date($vital['endTime']);
                    }
                    if (!empty($id)) {
                        $vitalRecord['patientId'] = $patientIdx;
                    }
                    if (!empty($vital['takeTime'])) {
                        $vitalRecord['takeTime'] = Helper::date($vital['takeTime']);
                    }
                    if (!empty($vital['addType'])) {
                        $vitalRecord['addType'] = $vital['addType'];
                    }
                    if (!empty($vital['value'])) {
                        $vitalRecord['value'] = $vital['value'];
                    }
                    if (!empty($vital['createdType'])) {
                        $vitalRecord['createdType'] = $vital['createdType'];
                    }
                    if (!empty($vital['deviceInfo'])) {
                        $vitalRecord['deviceInfo'] = json_encode($vital['deviceInfo']);
                    }
                    if (!empty($vital['type'])) {
                        $vitalRecord['vitalFieldId'] = $vital['type'];
                        $vitalState = DB::select(
                            'CALL vitalRangeFlag("' . $vital['type'] . '","' . $vital['value'] . '")',
                        );

                        if ($vitalState) {
                            $vitalRecord['flagId'] = $vitalState[0]->flagId;
                            array_push($vitalFlag, $vitalState[0]->flagId);
                        }
                    } else {
                        return response()->json(['message' => "Vital Type Required."], 400);
                    }

                    $vitalRecord['createdBy'] = Auth::id();
                    $vitalRecord['providerId'] = $providerId;
                    $vitalRecord['providerLocationId'] = $providerLocation;
                    $vitalRecord['entityType'] = $entityType;
                    $vitalRecord['udid'] = Str::uuid()->toString();
                    $vitalData = PatientVital::create($vitalRecord);

                    $compliance = ['nonCompliance' => 1, 'updatedBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType];
                    Patient::where('id', $patientIdx)->update($compliance);
                    $nonCompliance = ['deletedBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType, 'isActive' => 0, 'isDelete' => 1];
                    NonCompliance::where('patientId', $patientIdx)->update($nonCompliance);
                    NonCompliance::where('patientId', $patientIdx)->delete();
                    $changeLogPatient = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patients', 'tableId' => $patientIdx, 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType,
                        'value' => json_encode($compliance), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLogPatient);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patientVitals', 'tableId' => $vitalData->id,
                        'value' => json_encode($vitalRecord), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                    ];
                    ChangeLog::create($changeLog);
                    if (auth()->user()->roleId == 4) {
                        $userInput = Patient::where('id', auth()->user()->patient->id)->first();
                    } else {
                        $userInput = Staff::where('id', auth()->user()->staff->id)->first();
                    }
                    if (!empty($vital['comment'])) {
                        $note = [
                            'createdBy' => Auth::id(), 'note' => $vital['comment'], 'udid' => Str::uuid()->toString(), 'entityType' => 'patientVital',
                            'referenceId' => $vitalData->id, 'flagId' => $vitalRecord['flagId'], 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                        ];
                        if (auth()->user()->roleId == 4) {
                            $userInput = Patient::where('id', auth()->user()->patient->id)->first();
                        } else {
                            $userInput = Staff::where('id', auth()->user()->staff->id)->first();
                        }
                        $timeLine = [
                            'patientId' => $patientIdx, 'heading' => 'Vital Note Added', 'title' => $vital['comment'] . ' ' . '<b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . '</b>', 'type' => 6,
                            'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType, 'refrenceId' => $vitalData->id
                        ];
                        // PatientTimeLine::create($timeLine);
                    }
                    $patientData = Patient::where('udid', $id)->first();
                    $vitalField = VitalField::where('id', $vitalData->vitalFieldId)->first();
                    $device = GlobalCode::where('id', $vital['deviceType'])->first();
                    if ($vital['deviceType'] == 99 || $vital['deviceType'] == 100 || $vital['deviceType'] == 101) {
                        $typeTimeline = 4;
                        $timeLineHeading = "Vital Uploaded";
                        array_push($vitalUnits[$vital['takeTime']], $vital['units']);
                    } else {
                        $typeTimeline = 10;
                        $timeLineHeading = "Health Data Added ";
                        array_push($vitalUnits[$vital['takeTime']], $vital['units']);
                    }
                    if ($vital['type'] == 8) {
                        $minutes = $vital['value'] % 60;
                        $hours = intval($vital['value'] / 60);
                        if ($minutes && $hours) {
                            $time = $hours . ' hrs' . ' ' . $minutes . ' mins';
                        } elseif ($hours) {
                            $time = $hours . ' hrs';
                        } else {
                            $time = $minutes . ' mins';
                        }
                        array_push($vitalValue[$vital['takeTime']], $time);
                    } else {
                        array_push($vitalValue[$vital['takeTime']], $vital['value']);
                    }
                    if (isset($vitalField->name)) {
                        array_push($vitalType[$vital['takeTime']], $vitalField->name);
                    }
                }
                if (!empty($timeLine)) {
                    PatientTimeLine::create($timeLine);
                }
                if (in_array("7", $vitalFlag)) {
                    $flagId = 7;
                    $flag1 = [8, 9];
                } elseif (in_array("8", $vitalFlag)) {
                    $flagId = 8;
                    $flag1 = [7, 9];
                } else {
                    $flagId = 9;
                    $flag1 = [7, 8];
                }
                //According to Priority Flag Assigned to patient

                $flagData = PatientFlag::whereIn('flagId', $flag1)->where('patientId', $patientIdx)->first();
                if ($flagData) {
                    $flagOld = Flag::where('id', $flagData->flagId)->first();
                    $flags = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1];
                    // PatientFlag::where('patientId', $patientIdx)->update($flags);
                    PatientFlag::whereIn('flagId', $flag1)->where('patientId', $patientIdx)->update($flags);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patientFlags', 'tableId' => $flagData->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType,
                        'value' => json_encode($flags), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                    PatientFlag::whereIn('flagId', $flag1)->where('patientId', $patientIdx)->delete();
                    $flagDataInput = ['udid' => Str::uuid()->toString(), 'patientId' => $patientIdx, 'flagId' => $flagId, 'icon' => '', 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType];
                    $flag = PatientFlag::create($flagDataInput);
                    $flagInput = Flag::where('id', $flagId)->first();
                    $userInput = Staff::where('id', auth()->user()->staff->id)->first();
                    $flagTimeline = [
                        'patientId' => Helper::entity('patient', $id), 'heading' => 'Patient Status Flag Assigned', 'title' => 'Flag Changed ' . $flagOld->name . ' -> ' . $flagInput->name . ' ' . '<b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . ' ' . $userInput->middleName . '</b>', 'type' => 7,
                        'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'refrenceId' => $flag->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                    ];
                    PatientTimeLine::create($flagTimeline);
                } else {
                    $flagData = ['udid' => Str::uuid()->toString(), 'patientId' => $patientIdx, 'flagId' => $flagId, 'icon' => '', 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType];
                    $flag = PatientFlag::create($flagData);
                    $flagInput = Flag::where('id', $flagId)->first();
                    $userInput = Staff::where('id', auth()->user()->staff->id)->first();
                    $flagTimeline = [
                        'patientId' => $patientIdx, 'heading' => 'Patient Status Flag Assigned', 'title' => 'Flag Changed ' . $flagInput->name . ' ' . '<b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . ' ' . $userInput->middleName . '</b>', 'type' => 7,
                        'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'refrenceId' => $flag->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                    ];
                    PatientTimeLine::create($flagTimeline);
                }
                foreach ($vitalType as $time => $val) {
                    $vitalStr = "";
                    foreach ($val as $index => $vital) {
                        $vitalStr .= $vitalType[$time][$index] . " " . $vitalValue[$time][$index] . " " . $vitalUnits[$time][$index] . " " . ", ";
                    }
                    $vitalStr = rtrim($vitalStr, ',');

                    $timeLine = [
                        'patientId' => $patientIdx, 'heading' => $timeLineHeading, 'title' => $device->name . ' ' . $vitalStr, 'type' => $typeTimeline,
                        'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType, 'refrenceId' => $vitalData->id
                    ];
                    $timeline = PatientTimeLine::create($timeLine);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patientTimelines', 'tableId' => $timeline->id,
                        'value' => json_encode($timeLine), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                    ];
                    ChangeLog::create($changeLog);
                }
                if (!empty($note)) {
                    $noteData = Note::create($note);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'notes', 'tableId' => $noteData->id,
                        'value' => json_encode($note), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                    ];
                    ChangeLog::create($changeLog);
                }
            } else {
                $patient = Patient::where('userId', Auth::user()->id)->first();
                $patientId = $patient->id;
                $dataVital = $request->vital;
                $flagArray = array();
                $deviceArray = array();
                $vitalType = array();
                $vitalValue = array();
                $vitalUnits = array();
                $vitalFlag = array();
                $timeLine = array();
                foreach ($dataVital as $vital) {
                    if (!isset($vitalType[$vital['takeTime']])) {
                        $vitalType[$vital['takeTime']] = array();
                        $vitalValue[$vital['takeTime']] = array();
                        $vitalUnits[$vital['takeTime']] = array();
                    }
                    if ($vital['value'] == '') {
                        continue;
                    }
                    $takeTime = Helper::date($vital['takeTime']);
                    $startTime = Helper::date($vital['startTime']);
                    $endTime = Helper::date($vital['endTime']);
                    $data = [
                        'vitalFieldId' => $vital['type'],
                        'deviceTypeId' => $vital['deviceType'],
                        'createdBy' => Auth::id(),
                        'udid' => Str::uuid()->toString(),
                        'value' => $vital['value'],
                        'patientId' => $patientId,
                        'units' => $vital['units'],
                        'takeTime' => $takeTime,
                        'startTime' => $startTime,
                        'endTime' => $endTime,
                        'providerId' => $providerId,
                        'providerLocationId' => $providerLocation,
                        'entityType' => $entityType,
                        'addType' => $vital['addType'],
                        'createdType' => $vital['createdType'],
                        'deviceInfo' => json_encode($vital['deviceInfo'])
                    ];
                    // $vitalState = DB::select(
                    //     'CALL patientVitalGoal("' . $data['patientId'] . '","' . $data['vitalFieldId'] . '")',
                    // );
                    $vitalState = DB::select(
                        'CALL vitalRangeFlag("' . $vital['type'] . '","' . $vital['value'] . '")',
                    );

                    if ($vitalState) {
                        $data['flagId'] = $vitalState[0]->flagId;
                        array_push($vitalFlag, $vitalState[0]->flagId);
                    }
                    if (!isset($flagArray[$takeTime])) {
                        $flagArray[$takeTime] = array();
                    }
                    if (!isset($deviceArray[$takeTime])) {
                        $deviceArray[$takeTime] = array();
                    }
                    array_push($flagArray[$takeTime], $vitalState[0]->flagId);
                    array_push($deviceArray[$takeTime], $vital['deviceType']);
                    sort($flagArray[$takeTime]);
                    $vitalData = PatientVital::create($data);

                    $compliance = ['nonCompliance' => 1, 'updatedBy' => Auth::id(), 'providerId' => $providerId];
                    Patient::where('id', $patientId)->update($compliance);
                    $nonCompliance = ['deletedBy' => Auth::id(), 'providerId' => $providerId, 'isActive' => 0, 'isDelete' => 1];
                    NonCompliance::where('patientId', $patientId)->update($nonCompliance);
                    NonCompliance::where('patientId', $patientId)->delete();

                    $changeLogPatient = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patients', 'tableId' => $patientId, 'providerId' => $providerId,
                        'value' => json_encode($compliance), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLogPatient);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patientVitals', 'tableId' => $vitalData->id,
                        'value' => json_encode($data), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId
                    ];
                    ChangeLog::create($changeLog);
                    if (auth()->user()->roleId == 4) {
                        $userInput = Patient::where('id', auth()->user()->patient->id)->first();
                    } else {
                        $userInput = Staff::where('id', auth()->user()->staff->id)->first();
                    }
                    if (!empty($vital['comment'])) {
                        $note = [
                            'createdBy' => Auth::id(), 'note' => $vital['comment'], 'udid' => Str::uuid()->toString(), 'entityType' => 'patientVital',
                            'referenceId' => $vitalData->id, 'flagId' => $vitalState[0]->flagId, 'providerId' => $providerId
                        ];
                        $noteInput = Note::create($note);
                        if (auth()->user()->roleId == 4) {
                            $userInput = Patient::where('id', auth()->user()->patient->id)->first();
                        } else {
                            $userInput = Staff::where('id', auth()->user()->staff->id)->first();
                        }
                        $timeLine = [
                            'patientId' => $patientId, 'heading' => 'Vital Note Added', 'title' => $vital['comment'] . ' ' . '<b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . ' ' . $userInput->middleName . '</b>', 'type' => 6,
                            'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'refrenceId' => $vitalData->id
                        ];
                        // PatientTimeLine::create($timeLine);
                    }
                    $patientData = Patient::where('id', $patientId)->first();
                    $vitalField = VitalField::where('id', $vitalData->vitalFieldId)->first();
                    $device = GlobalCode::where('id', $vital['deviceType'])->first();
                    if ($vital['deviceType'] == 99 || $vital['deviceType'] == 100 || $vital['deviceType'] == 101) {
                        $typeTimeline = 4;
                        $timeLineHeading = "Vital Uploaded";
                        array_push($vitalUnits[$vital['takeTime']], $vital['units']);
                    } else {
                        $typeTimeline = 10;
                        $timeLineHeading = "Health Data Added ";
                        array_push($vitalUnits[$vital['takeTime']], $vital['units']);
                    }
                    if ($vital['type'] == 8) {
                        $minutes = $vital['value'] % 60;
                        $hours = intval($vital['value'] / 60);
                        if ($minutes && $hours) {
                            $time = $hours . ' hrs' . ' ' . $minutes . ' mins';
                        } elseif ($hours) {
                            $time = $hours . ' hrs';
                        } else {
                            $time = $minutes . ' mins';
                        }
                        array_push($vitalValue[$vital['takeTime']], $time);
                    } else {
                        array_push($vitalValue[$vital['takeTime']], $vital['value']);
                    }

                    array_push($vitalType[$vital['takeTime']], $vitalField->name);
                }

                if (!empty($timeLine)) {
                    PatientTimeLine::create($timeLine);
                }

                if (in_array("7", $vitalFlag)) {
                    $flagId = 7;
                } elseif (in_array("8", $vitalFlag)) {
                    $flagId = 8;
                } else {
                    $flagId = 9;
                }
                //According to Priority Flag Assigned to patient
                $flagData = PatientFlag::where('patientId', $patientId)->first();
                if ($flagData) {
                    $flagOld = Flag::where('id', $flagData->flagId)->first();
                    $flags = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1];
                    PatientFlag::where('patientId', $patientId)->update($flags);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patientFlags', 'tableId' => $flagData->id,
                        'value' => json_encode($flags), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                    PatientFlag::where('patientId', $patientId)->delete();

                    $flagDataInput = ['udid' => Str::uuid()->toString(), 'patientId' => $patientId, 'flagId' => $flagId, 'icon' => '', 'providerId' => $providerId, 'providerLocationId' => $providerLocation];
                    $flag = PatientFlag::create($flagDataInput);
                    $flagInput = Flag::where('id', $flagId)->first();
                    $userInput = Patient::where('id', auth()->user()->patient->id)->first();

                    $flagTimeline = [
                        'patientId' => $patientId, 'heading' => 'Patient Status Flag Assigned', 'title' => 'Flag Changed ' . $flagOld->name . ' -> ' . $flagInput->name . ' ' . '<b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . ' ' . $userInput->middleName . '</b>', 'type' => 7,
                        'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'refrenceId' => $flag->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                    ];
                    PatientTimeLine::create($flagTimeline);
                } else {
                    $flagData = ['udid' => Str::uuid()->toString(), 'patientId' => $patientId, 'flagId' => $flagId, 'icon' => '', 'providerId' => $providerId, 'providerLocationId' => $providerLocation];
                    $flag = PatientFlag::create($flagData);
                    $flagInput = Flag::where('id', $flagId)->first();
                    $userInput = Patient::where('id', auth()->user()->patient->id)->first();
                    $flagTimeline = [
                        'patientId' => $patientId, 'heading' => 'Patient Status Flag Assigned', 'title' => 'Flag Added ' . $flagInput->name . ' ' . '<b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . ' ' . $userInput->middleName . '</b>', 'type' => 7,
                        'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'refrenceId' => $flag->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                    ];
                    PatientTimeLine::create($flagTimeline);
                }
                foreach ($vitalType as $time => $val) {
                    $vitalStr = "";
                    foreach ($val as $index => $vital) {
                        $vitalStr .= $vitalType[$time][$index] . " " . $vitalValue[$time][$index] . " " . $vitalUnits[$time][$index] . ", ";
                    }
                    $vitalStr = rtrim($vitalStr, ',');
                    $timeLine = [
                        'patientId' => $patientId, 'heading' => $timeLineHeading, 'title' => $device->name . ' ' . $vitalStr, 'type' => $typeTimeline,
                        'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'refrenceId' => $vitalData->id
                    ];
                    $timeline = PatientTimeLine::create($timeLine);
                    // $changeLog = [
                    //     'udid' => Str::uuid()->toString(), 'table' => 'patientTimelines', 'tableId' => $timeline->id,
                    //     'value' => json_encode($note), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId
                    // ];
                    // ChangeLog::create($changeLog);
                }
            }
            $message = ["message" => trans('messages.vitalsAddedSuccessfully')];
            DB::commit();
            return $message;
        } catch (Exception $e) {
            DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // List Patient Vitals
    public function patientVitalList($request, $id)
    {
        try {
            if ($id) {
                $patient = Helper::entity('patient', $id);
                $notAccess = Helper::haveAccess($patient);
                if (!$notAccess) {
                    $familyMember = PatientFamilyMember::where([['userId', auth()->user()->id], ['patientId', $patient]])->get();
                    if ($familyMember == true) {
                        $patientIdx = $patient;
                    } else {
                        return response()->json(['message' => trans('messages.unauthenticated')], 401);
                    }
                } elseif (!$id) {
                    $patientIdx = '';
                } else {
                    return $notAccess;
                }
                $type = '';
                $fromDate = '';
                $toDate = '';
                $deviceType = '';
                if (!empty($request->toDate)) {
                    $toDate = date("Y-m-d H:i:s", $request->toDate);
                }
                if (!empty($request->fromDate)) {
                    $fromDate = date("Y-m-d H:i:s", $request->fromDate);
                }
                if (!empty($request->type)) {
                    $type = $request->type;
                }
                if (!empty($request->deviceType)) {
                    $deviceType = $request->deviceType;
                }
                if (empty($patientIdx)) {
                    $patientIdx = auth()->user()->patient->id;
                } elseif (!empty($patientIdx)) {
                    $patientIdx = $patient;
                }
                $data = DB::select(
                    'CALL getPatientVital("' . $patientIdx . '","' . $fromDate . '","' . $toDate . '","' . $type . '","' . $deviceType . '")',
                );
                return fractal()->collection($data)->transformWith(new PatientVitalTransformer())->toArray();
            } else {
                $patient = auth()->user()->patient->id;
                $notAccess = Helper::haveAccess($patient);
                if (!$notAccess) {
                    $familyMember = PatientFamilyMember::where([['userId', auth()->user()->id], ['patientId', $patient]])->get();
                    if ($familyMember == true) {
                        $patientIdx = $patient;
                    } else {
                        return response()->json(['message' => trans('messages.unauthenticated')], 401);
                    }
                } elseif (!$id) {
                    $patientIdx = '';
                } else {
                    return $notAccess;
                }
                $type = '';
                $fromDate = '';
                $toDate = '';
                $deviceType = '';
                if (!empty($request->toDate)) {
                    $toDate = date("Y-m-d H:i:s", $request->toDate);
                }
                if (!empty($request->fromDate)) {
                    $fromDate = date("Y-m-d H:i:s", $request->fromDate);
                }
                if (!empty($request->type)) {
                    $type = $request->type;
                }
                if (!empty($request->deviceType)) {
                    $deviceType = $request->deviceType;
                }
                if (empty($patientIdx)) {
                    $patientIdx = auth()->user()->patient->id;
                } elseif (!empty($patientIdx)) {
                    $patientIdx = $patient;
                }
                $data = DB::select('CALL getPatientVital("' . $patientIdx . '","' . $fromDate . '","' . $toDate . '","' . $type . '","' . $deviceType . '")');
                return fractal()->collection($data)->transformWith(new PatientVitalTransformer())->toArray();
            }
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Vital List
    public function vitalList($request, $id)
    {
        try {
            $patientIdx = "";
            if ($id) {
                $patient = Helper::entity('patient', $id);
                $patientIdx = $patient;
            } elseif (isset(auth()->user()->patient->id)) {
                $patientIdx = auth()->user()->patient->id;
            }

            $notAccess = Helper::haveAccess($patientIdx);
            if (!$notAccess) {
                $result = DB::select(
                    "CALL getVitals('" . $patientIdx . "','" . $request->type . "')"
                );
                return fractal()->collection($result)->transformWith(new PatientVitalTransformer())->toArray();
            } else {
                return $notAccess;
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Latest Patient Vital
    public function latest($request, $id, $vitalType)
    {
        try {
            if (!$id) {
                $patientId = auth()->user()->patient->id;
            } elseif ($id) {
                $patient = Helper::entity('patient', $id);
                $patientId = $patient;
            } else {
                return response()->json(['message' => trans('messages.unauthenticated')], 401);
            }
            if ($request->deviceType) {
                $data = PatientVital::where([['patientId', $patientId], ['deviceTypeId', $request->deviceType]])->orderBy('takeTime', 'desc')->get()->unique('vitalFieldId');
            } else {
                $data = PatientVital::where('patientId', $patientId)->orderBy('takeTime', 'desc')->get()->unique('vitalFieldId');
            }
            return fractal()->collection($data)->transformWith(new PatientVitalTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Patient Vitals
    public function patientVitalDelete($request, $id, $vitalId)
    {
        DB::beginTransaction();
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $data = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            PatientVital::find($vitalId)->update($data);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientVitals', 'tableId' => $vitalId, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($data), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);
            PatientVital::find($vitalId)->delete();
            DB::commit();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Add And Update Patient Clinical Data
    public function patientMedicalHistoryCreate($request, $id, $medicalHistoryId)
    {
        DB::beginTransaction();
        try {
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            if (!$medicalHistoryId) {
                $patient = Helper::entity('patient', $id);
                $input = [
                    'history' => $request->input('history'), 'patientId' => $patient, 'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                ];
                $patient = PatientMedicalHistory::create($input);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientMedicalHistories', 'tableId' => $patient->id,
                    'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                ];
                ChangeLog::create($changeLog);
                $getPatient = PatientMedicalHistory::where('id', $patient->id)->with('patient')->first();
                $userdata = fractal()->item($getPatient)->transformWith(new PatientMedicalTransformer())->toArray();
                $message = ["message" => trans('messages.addedSuccesfully')];
            } else {
                $input = ['history' => $request->input('history'), 'updatedBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation];
                $patient = PatientMedicalHistory::where('udid', $medicalHistoryId)->update($input);
                $patientData = Helper::tableName('App\Models\Patient\PatientMedicalHistory', $medicalHistoryId);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientMedicalHistories', 'tableId' => $patientData, 'entityType' => $entityType,
                    'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);
                $getPatient = PatientMedicalHistory::where('udid', $medicalHistoryId)->with('patient')->first();
                $userdata = fractal()->item($getPatient)->transformWith(new PatientMedicalTransformer())->toArray();
                $message = ['message' => trans('messages.updatedSuccesfully')];
            }
            DB::commit();
            $endData = array_merge($message, $userdata);
            return $endData;
        } catch (Exception $e) {
            DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // List Patient Medical History
    public function patientMedicalHistoryList($request, $id, $medicalHistoryId)
    {
        try {
            $patient = Helper::entity('patient', $id);
            $data = PatientMedicalHistory::where('patientMedicalHistories.patientId', $patient)->select('patientMedicalHistories.*')->with('patient');

            // $data->leftJoin('providers', 'providers.id', '=', 'patientMedicalHistories.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'patientMedicalHistories.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('patientMedicalHistories.providerLocationId', '=', 'providerLocations.id')->where('patientMedicalHistories.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('patientMedicalHistories.providerLocationId', '=', 'providerLocationStates.id')->where('patientMedicalHistories.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('patientMedicalHistories.providerLocationId', '=', 'providerLocationCities.id')->where('patientMedicalHistories.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('patientMedicalHistories.providerLocationId', '=', 'subLocations.id')->where('patientMedicalHistories.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('patientMedicalHistories.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['patientMedicalHistories.providerLocationId', $providerLocation], ['patientMedicalHistories.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['patientMedicalHistories.providerLocationId', $providerLocation], ['patientMedicalHistories.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['patientMedicalHistories.providerLocationId', $providerLocation], ['patientMedicalHistories.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['patientMedicalHistories.providerLocationId', $providerLocation], ['patientMedicalHistories.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['patientMedicalHistories.programId', $program], ['patientMedicalHistories.entityType', $entityType]]);
            // }
            if ($medicalHistoryId) {
                $data = $data->where('patientMedicalHistories.udid', $medicalHistoryId)->first();
                return fractal()->item($data)->transformWith(new PatientMedicalTransformer())->toArray();
            } else {
                $notAccess = Helper::haveAccess($patient);
                if (!$notAccess) {
                    $data = $data->get();
                    return fractal()->collection($data)->transformWith(new PatientMedicalTransformer())->toArray();
                } else {
                    return $notAccess;
                }
            }
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Delete Patient History
    public function patientMedicalHistoryDelete($request, $id, $medicalHistoryId)
    {
        DB::beginTransaction();
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $data = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            PatientMedicalHistory::where('udid', $medicalHistoryId)->update($data);
            $patientData = Helper::tableName('App\Models\Patient\PatientMedicalHistory', $medicalHistoryId);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientMedicalHistories', 'tableId' => $patientData, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($data), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);
            PatientMedicalHistory::where('udid', $medicalHistoryId)->delete();
            DB::commit();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Add And Update Patient Medical Routine
    public function patientMedicalRoutineCreate($request, $id, $medicalRoutineId)
    {
        DB::beginTransaction();
        try {
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $startDate = Helper::date($request->input('startDate'));
            if ($request->input('endDate')) {
                $endDate = Helper::date($request->input('endDate'));
            } else {
                $endDate = NULL;
            }
            if (!$medicalRoutineId) {
                $patient = Helper::entity('patient', $id);
                $input = [
                    'medicine' => $request->input('medicine'), 'frequency' => $request->input('frequency'), 'createdBy' => Auth::id(), 'entityType' => $entityType,
                    'startDate' => $startDate, 'endDate' => $endDate, 'patientId' => $patient, 'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                $patient = PatientMedicalRoutine::create($input);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patentMedicineRoutines', 'tableId' => $patient->id, 'entityType' => $entityType,
                    'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);
                $getPatient = PatientMedicalRoutine::where('id', $patient->id)->with('patient')->first();
                $userdata = fractal()->item($getPatient)->transformWith(new PatientMedicalRoutineTransformer())->toArray();
                $message = ["message" => trans('messages.addedSuccesfully')];
            } else {
                $input = [
                    'medicine' => $request->input('medicine'), 'frequency' => $request->input('frequency'), 'updatedBy' => Auth::id(),
                    'startDate' => $startDate, 'endDate' => $endDate, 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                $patient = PatientMedicalRoutine::where('udid', $medicalRoutineId)->update($input);
                $patientData = Helper::tableName('App\Models\Patient\PatientMedicalRoutine', $medicalRoutineId);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patentMedicineRoutines', 'tableId' => $patientData, 'entityType' => $entityType,
                    'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);
                $getPatient = PatientMedicalRoutine::where('udid', $medicalRoutineId)->with('patient')->first();
                $userdata = fractal()->item($getPatient)->transformWith(new PatientMedicalRoutineTransformer())->toArray();
                $message = ['message' => trans('messages.updatedSuccesfully')];
            }
            DB::commit();
            return array_merge($message, $userdata);
        } catch (Exception $e) {
            DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // List Patient Medical Routine
    public function patientMedicalRoutineList($request, $id, $medicalRoutineId)
    {
        try {
            $patient = Helper::entity('patient', $id);
            $data = PatientMedicalRoutine::where('patentMedicineRoutines.patientId', $patient)->select('patentMedicineRoutines.*')->with('patient', 'medicalFrequency');

            // $data->leftJoin('providers', 'providers.id', '=', 'patentMedicineRoutines.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'patentMedicineRoutines.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('patentMedicineRoutines.providerLocationId', '=', 'providerLocations.id')->where('patentMedicineRoutines.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('patentMedicineRoutines.providerLocationId', '=', 'providerLocationStates.id')->where('patentMedicineRoutines.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('patentMedicineRoutines.providerLocationId', '=', 'providerLocationCities.id')->where('patentMedicineRoutines.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('patentMedicineRoutines.providerLocationId', '=', 'subLocations.id')->where('patentMedicineRoutines.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('patentMedicineRoutines.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['patentMedicineRoutines.providerLocationId', $providerLocation], ['patentMedicineRoutines.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['patentMedicineRoutines.providerLocationId', $providerLocation], ['patentMedicineRoutines.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['patentMedicineRoutines.providerLocationId', $providerLocation], ['patentMedicineRoutines.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['patentMedicineRoutines.providerLocationId', $providerLocation], ['patentMedicineRoutines.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['patentMedicineRoutines.programId', $program], ['patentMedicineRoutines.entityType', $entityType]]);
            // }
            if ($medicalRoutineId) {
                $data = $data->where('patentMedicineRoutines.udid', $medicalRoutineId)->first();
                return fractal()->item($data)->transformWith(new PatientMedicalRoutineTransformer())->toArray();
            } else {
                $notAccess = Helper::haveAccess($patient);
                if (!$notAccess) {
                    $data = $data->orderBy('patentMedicineRoutines.createdAt', 'DESC')->get();
                    return fractal()->collection($data)->transformWith(new PatientMedicalRoutineTransformer())->toArray();
                } else {
                    return $notAccess;
                }
            }
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Delete Patient Medical Routine
    public function patientMedicalRoutineDelete($request, $id, $medicalRoutineId)
    {
        DB::beginTransaction();
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $data = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            PatientMedicalRoutine::where('udid', $medicalRoutineId)->update($data);
            $input = Helper::tableName('App\Models\Patient\PatientMedicalRoutine', $medicalRoutineId);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patentMedicineRoutines', 'tableId' => $input, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($data), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);
            PatientMedicalRoutine::where('udid', $medicalRoutineId)->delete();
            DB::commit();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    public function getCreateInsurance($name)
    {
        try {
            $insurance = GlobalCode::where('name', $name)->where('globalCodeCategoryId', 18)->first();
            if (isset($insurance->id) && !empty($insurance->id)) {
                return $insurance->id;
            } else {
                $provider = Helper::providerId();
                $input = [
                    'globalCodeCategoryId' => 18, 'createdBy' => Auth::id(),
                    'udid' => Str::uuid()->toString(), 'isActive' => 1, 'name' => $name,
                    'description' => $name, 'providerId' => $provider
                ];
                $global = GlobalCode::create($input);
                return $global->id;
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add And Update Patient Insurance
    public function patientInsuranceCreate($request, $id, $insuranceId)
    {
        DB::beginTransaction();
        try {
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $patient = Helper::entity('patient', $id);
            PatientInsurance::where('patientId', $patient)->delete();
            $insurance = $request->input('insurance');
            foreach ($insurance as $value) {
                if (!isset($value['expirationDate'])) {
                    $expirationDate = null;
                } else {
                    $expirationDate = $value['expirationDate'];
                }
                $insuranceId = $value['insuranceName'];
                if ($request->betrix == true) {
                    $insuranceId = $this->getCreateInsurance($value['insuranceName']);
                }
                $input = [
                    'insuranceNumber' => $value['insuranceNumber'], 'expirationDate' => $expirationDate, 'createdBy' => Auth::id(),
                    'insuranceNameId' => $insuranceId, 'insuranceTypeId' => $value['insuranceType'], 'patientId' => $patient,
                    'udid' => Str::uuid()->toString(), 'providerId' => $providerId
                ];
                // print_r($input);die;
                $insuranceData = PatientInsurance::create($input);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientInsurances', 'tableId' => $insuranceData->id, 'entityType' => $entityType,
                    'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);
            }
            DB::commit();
            $getPatient = PatientInsurance::where('patientId', $patient)->with('patient')->get();
            $userdata = fractal()->collection($getPatient)->transformWith(new PatientInsuranceTransformer())->toArray();
            $message = ["message" => trans('messages.addedSuccesfully')];
            return array_merge($message, $userdata);
        } catch (Exception $e) {
            DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // List Patient Insurance
    public function patientInsuranceList($request, $id, $insuranceId)
    {
        try {
            $patient = Helper::entity('patient', $id);
            $data = PatientInsurance::where('patientInsurances.patientId', $patient)->select('patientInsurances.*')->with('patient', 'insuranceName', 'insuranceType');

            // $data->leftJoin('providers', 'providers.id', '=', 'patientInsurances.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'patientInsurances.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('patientInsurances.providerLocationId', '=', 'providerLocations.id')->where('patientInsurances.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('patientInsurances.providerLocationId', '=', 'providerLocationStates.id')->where('patientInsurances.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('patientInsurances.providerLocationId', '=', 'providerLocationCities.id')->where('patientInsurances.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('patientInsurances.providerLocationId', '=', 'subLocations.id')->where('patientInsurances.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('patientInsurances.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['patientInsurances.providerLocationId', $providerLocation], ['patientInsurances.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['patientInsurances.providerLocationId', $providerLocation], ['patientInsurances.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['patientInsurances.providerLocationId', $providerLocation], ['patientInsurances.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['patientInsurances.providerLocationId', $providerLocation], ['patientInsurances.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['patientInsurances.programId', $program], ['patientInsurances.entityType', $entityType]]);
            // }
            if ($insuranceId) {
                $data = $data->where('patientInsurances.udid', $insuranceId)->first();
                return fractal()->item($data)->transformWith(new PatientInsuranceTransformer())->toArray();
            } else {
                $notAccess = Helper::haveAccess($patient);
                if (!$notAccess) {
                    $data = $data->get();
                    return fractal()->collection($data)->transformWith(new PatientInsuranceTransformer())->toArray();
                } else {
                    return $notAccess;
                }
            }
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Delete Patient Insurance
    public function patientInsuranceDelete($request, $id, $insuranceId)
    {
        DB::beginTransaction();
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $data = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            PatientInsurance::where('udid', $insuranceId)->update($data);
            $input = Helper::tableName('App\Models\Patient\PatientInsurance', $insuranceId);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientInsurances', 'tableId' => $input, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($data), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);
            PatientInsurance::where('udid', $insuranceId)->delete();
            DB::commit();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // List Patient Inventory With Login
    public function patientInventoryListing($request)
    {
        try {
            $patient = Patient::where('userId', Auth::id())->first();
            $patientId = $patient->id;
            $data = PatientInventory::where('patientInventories.patientId', $patientId)->select('patientInventories.*')->with('patient', 'inventory', 'deviceTypes');

            // $data->leftJoin('providers', 'providers.id', '=', 'patientInventories.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'patientInventories.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('patientInventories.providerLocationId', '=', 'providerLocations.id')->where('patientInventories.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('patientInventories.providerLocationId', '=', 'providerLocationStates.id')->where('patientInventories.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('patientInventories.providerLocationId', '=', 'providerLocationCities.id')->where('patientInventories.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('patientInventories.providerLocationId', '=', 'subLocations.id')->where('patientInventories.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('patientInventories.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['patientInventories.providerLocationId', $providerLocation], ['patientInventories.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['patientInventories.providerLocationId', $providerLocation], ['patientInventories.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['patientInventories.providerLocationId', $providerLocation], ['patientInventories.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['patientInventories.providerLocationId', $providerLocation], ['patientInventories.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['patientInventories.programId', $program], ['patientInventories.entityType', $entityType]]);
            // }
            $notAccess = Helper::haveAccess($patientId);
            if (!$notAccess) {
                $getPatient = $data->where('patientInventories.isActive', '1')->get();
                return fractal()->collection($getPatient)->transformWith(new PatientInventoryTransformer())->toArray();
            } else {
                return $notAccess;
            }
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Update Patient Inventory IsAdded
    public function inventoryUpdate($request, $id)
    {
        DB::beginTransaction();
        try {
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $inventory = ['isAdded' => 1, 'updatedBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation];
            PatientInventory::where('udid', $id)->update($inventory);
            $data = Helper::entity('patientInventory', $id);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientInventories', 'tableId' => $data, 'entityType' => $entityType,
                'value' => json_encode($inventory), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
            ];
            ChangeLog::create($changeLog);
            $patient = PatientInventory::where('udid', $id)->first();
            $deviceType = $patient->inventory->model->deviceType->id;
            $cptService = CPTCodeService::where('patientId', $patient->patientId)->where('referenceId', $deviceType)->where('entity', 'device')->first();
            if (!$cptService) {
                $cpt = new CptCodeServiceDetailService;
                $inputData = [
                    'referenceId' => $patient->inventory->model->deviceType->id, 'patientId' => $patient->patientId,
                    'serviceId' => $patient->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'placeOfService' => $patient->patient->placeOfServiceId
                ];
                $cpt->cptCode($inputData);
                CPTCodeServiceClass::processNextBillingDetail($request);
                CPTCodeServiceClass::insertNextBillingServiceDetail($request);
            }
            $user = User::where('id', Auth::id())->first();
            $userId = $user->roleId;
            if ($userId == 4) {
                $patientData = Patient::where('userId', $user->id)->first();
                $inventory = Inventory::where('id', $patient->inventoryId)->first();
                $deviceModel = DeviceModel::where('id', $inventory->deviceModelId)->first();
                $device = GlobalCode::where('id', $deviceModel->deviceTypeId)->first();
                $deviceType = $device->name;
                $timeLine = [
                    'patientId' => $patientData->id, 'heading' => 'Device Assigned', 'title' => $deviceType . ' ' . ' Device Assigned to ' . ' ' . $patientData->lastName . ',' . ' ' . $patientData->firstName . ' ' . $patientData->middleName, 'type' => 3,
                    'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                ];
                $timeline = PatientTimeLine::create($timeLine);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientTimelines', 'tableId' => $timeline->id, 'entityType' => $entityType,
                    'value' => json_encode($timeLine), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);
                $patient = ['isDeviceAdded' => 1, 'updatedBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation];
                Patient::where('id', $patientData->id)->update($patient);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patients', 'tableId' => $patientData->id, 'entityType' => $entityType,
                    'value' => json_encode($patient), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);
            } elseif ($userId == 3) {
                $patientData = PatientInventory::where('udid', $id)->first();
                $inventory = Inventory::where('id', $patient->inventoryId)->first();
                $deviceModel = DeviceModel::where('id', $inventory->deviceModelId)->first();
                $device = GlobalCode::where('id', $deviceModel->deviceTypeId)->first();
                $deviceType = $device->name;
                $timeLine = [
                    'patientId' => $patientData->patientId, 'heading' => 'Inventory Assigned', 'title' => $deviceType . ' ' . 'Linked to' . ' ' . $patientData->lastName . ',' . ' ' . $patientData->firstName . ' ' . $patientData->middleName, 'type' => 3,
                    'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                ];
                $timelineData = PatientTimeLine::create($timeLine);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientTimelines', 'tableId' => $timelineData->id, 'entityType' => $entityType,
                    'value' => json_encode($timeLine), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);
                $patient = ['isDeviceAdded' => 1, 'updatedBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation];
                Patient::where('id', $patientData->id)->update($patient);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patients', 'tableId' => $patientData->id, 'entityType' => $entityType,
                    'value' => json_encode($patient), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);
            }
            $getPatient = PatientInventory::where('udid', $id)->with('patient', 'inventory', 'deviceTypes')->first();
            $userdata = fractal()->item($getPatient)->transformWith(new PatientInventoryTransformer())->toArray();
            $message = ['message' => trans('messages.updatedSuccesfully')];
            DB::commit();
            return array_merge($message, $userdata);
        } catch (Exception $e) {
            DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Add Patient Device
    public function patientDeviceCreate($request, $id, $deviceId)
    {
        DB::beginTransaction();
        try {
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            if (!$id) {
                $userId = Auth::id();
                $patient = Patient::where('userId', $userId)->first();
                $patientId = $patient->id;
                if (!$deviceId) {
                    $device = [
                        'otherDeviceId' => $request->input('otherDevice'), 'status' => $request->status, 'udid' => Str::uuid()->toString(), 'patientId' => $patientId,
                        'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                    ];
                    $patient = PatientDevice::create($device);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patientDevices', 'tableId' => $patient->id, 'entityType' => $entityType,
                        'value' => json_encode($device), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                    ];
                    ChangeLog::create($changeLog);
                    $getPatient = PatientDevice::where('id', $patient->id)->with('patient')->first();
                    $userdata = fractal()->item($getPatient)->transformWith(new PatientDeviceTransformer())->toArray();
                    $message = ["message" => trans('messages.addedSuccesfully')];
                } else {
                    $device = ['otherDeviceId' => $request->input('otherDevice'), 'status' => $request->input('status'), 'updatedBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation];
                    $patient = PatientDevice::where('udid', $deviceId)->update($device);
                    $deviceData = Helper::entity('patientDevice', $deviceId);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patientDevices', 'tableId' => $deviceData, 'providerId' => $providerId, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($device), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                    ];
                    ChangeLog::create($changeLog);
                    $getPatient = PatientDevice::where('udid', $deviceId)->with('patient', 'otherDevice')->first();
                    $userdata = fractal()->item($getPatient)->transformWith(new PatientDeviceTransformer())->toArray();
                    $message = ['message' => trans('messages.updatedSuccesfully')];
                }
            } else {
                if (!$deviceId) {
                    $udid = Str::uuid()->toString();
                    $device = [
                        'otherDeviceId' => $request->input('otherDevice'), 'status' => $request->status, 'udid' => $udid, 'patientId' => $id,
                        'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                    ];
                    $patient = PatientDevice::create($device);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patientDevices', 'tableId' => $patient->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($device), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                    ];
                    ChangeLog::create($changeLog);
                    $getPatient = PatientDevice::where('id', $patient->id)->with('patient')->first();
                    $userdata = fractal()->item($getPatient)->transformWith(new PatientDeviceTransformer())->toArray();
                    $message = ["message" => trans('messages.addedSuccesfully')];
                } else {
                    $device = ['otherDeviceId' => $request->input('otherDevice'), 'status' => $request->input('status'), 'updatedBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation];
                    $patient = PatientDevice::where('udid', $deviceId)->update($device);
                    $deviceData = Helper::entity('patientDevice', $deviceId);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patientDevices', 'tableId' => $deviceData, 'providerId' => $providerId, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($device), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                    ];
                    ChangeLog::create($changeLog);
                    $getPatient = PatientDevice::where('udid', $deviceId)->with('patient', 'otherDevice')->first();
                    $userdata = fractal()->item($getPatient)->transformWith(new PatientDeviceTransformer())->toArray();
                    $message = ['message' => trans('messages.updatedSuccesfully')];
                }
            }
            DB::commit();
            return array_merge($message, $userdata);
        } catch (Exception $e) {
            DB::rollback();
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // List Patient Device
    public function patientDeviceList($request, $id)
    {
        try {
            $data = PatientDevice::select('patientDevices.*')->with('patient');

            // $data->leftJoin('providers', 'providers.id', '=', 'patientDevices.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'patientDevices.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('patientDevices.providerLocationId', '=', 'providerLocations.id')->where('patientDevices.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('patientDevices.providerLocationId', '=', 'providerLocationStates.id')->where('patientDevices.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('patientDevices.providerLocationId', '=', 'providerLocationCities.id')->where('patientDevices.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('patientDevices.providerLocationId', '=', 'subLocations.id')->where('patientDevices.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('patientDevices.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['patientDevices.providerLocationId', $providerLocation], ['patientDevices.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['patientDevices.providerLocationId', $providerLocation], ['patientDevices.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['patientDevices.providerLocationId', $providerLocation], ['patientDevices.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['patientDevices.providerLocationId', $providerLocation], ['patientDevices.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['patientDevices.programId', $program], ['patientDevices.entityType', $entityType]]);
            // }
            if (!$id) {
                $patient = Patient::where('userId', Auth::id())->first();
                $data = $data->where('patientDevices.patientId', $patient->id)->get();
            } else {
                $patient = Helper::entity('patient', $id);
                $notAccess = Helper::haveAccess($patient);
                if (!$notAccess) {
                    $data = $data->where('patientDevices.patientId', $patient)->get();
                } else {
                    return $notAccess;
                }
            }
            return fractal()->collection($data)->transformWith(new PatientDeviceTransformer())->toArray();
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // List Patient Timeline
    public function patientTimelineList($request, $id)
    {
        try {
            $patient = Helper::entity('patient', $id);
            $data = PatientTimeLine::where('patientTimelines.patientId', $patient)->select('patientTimelines.*')->with('patient');

            // $data->leftJoin('providers', 'providers.id', '=', 'patientTimelines.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'patientTimelines.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('patientTimelines.providerLocationId', '=', 'providerLocations.id')->where('patientTimelines.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('patientTimelines.providerLocationId', '=', 'providerLocationStates.id')->where('patientTimelines.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('patientTimelines.providerLocationId', '=', 'providerLocationCities.id')->where('patientTimelines.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('patientTimelines.providerLocationId', '=', 'subLocations.id')->where('patientTimelines.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('patientTimelines.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['patientTimelines.providerLocationId', $providerLocation], ['patientTimelines.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['patientTimelines.providerLocationId', $providerLocation], ['patientTimelines.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['patientTimelines.providerLocationId', $providerLocation], ['patientTimelines.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['patientTimelines.providerLocationId', $providerLocation], ['patientTimelines.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['patientTimelines.programId', $program], ['patientTimelines.entityType', $entityType]]);
            // }
            $notAccess = Helper::haveAccess($patient);
            if (!$notAccess) {
                if (!empty($request->type)) {
                    $type = explode(',', $request->type);
                    if (!empty($type)) {
                        //if (is_array($type) && in_array('6', $type)) {
                        if (!empty($request->search)) {
                            $data->where('patientTimelines.title', 'LIKE', '%' . $request->search . '%');
                        }
                    }
                    $data = $data->whereIn('patientTimelines.type', $type)->orderBy('patientTimelines.id', 'DESC')->paginate(env('PER_PAGE', 20));
                } else {
                    $data = $data->orderBy('patientTimelines.id', 'DESC')->paginate(env('PER_PAGE', 20));
                }
                return fractal()->collection($data)->transformWith(new PatientTimelineTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
            } else {
                return $notAccess;
            }
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // Add Patient Flags
    public function patientFlagAdd($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $userInput = Staff::where('id', auth()->user()->staff->id)->first();
            $patientId = Patient::where('udid', $id)->first();
            $udid = Str::uuid()->toString();
            $flag = Flag::where('udid', $request->input('flag'))->first();
            // $patientFlag = PatientFlag::where([['flagId', $flag->id], ['patientId', $patientId->id]])->first();
            // if (!$patientFlag) {
            $input = ['udid' => $udid, 'patientId' => $patientId->id, 'flagId' => $flag->id, 'icon' => '', 'providerId' => $provider];
            $array2 = array(7, 8, 9);
            $patientFlagNew = PatientFlag::whereIn('flagId', $array2)->where('patientId', $patientId->id)->first();
            if ($patientFlagNew) {
                if (in_array($flag->id, $array2)) {
                    $this->flagDelete($patientId, $patientFlagNew->flagId);
                }
            }
            $flag = PatientFlag::create($input);
            $getFlag = in_array($flag->flagId, $array2);
            if ($request->reason) {
                $note = ['udid' => $udid, 'note' => $request->reason, 'referenceId' => $flag->id, 'entitytype' => 'patientFlag', 'createdBy' => Auth::id(), 'providerId' => $provider];
                $noteData = Note::create($note);
                $heading = $getFlag ? 'Patient Status Flag Note ' : 'Work Status Flag Note ';
                $timeLine = [
                    'patientId' => $patientId->id, 'heading' => $heading, 'title' => $request->reason . ' ' . '<b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . ' ' . $userInput->middleName . '</b>', 'type' => 6,
                    'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'refrenceId' => $flag->id, 'providerId' => $provider
                ];
                PatientTimeLine::create($timeLine);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'notes', 'tableId' => $noteData->id, 'providerId' => $provider,
                    'value' => json_encode($note), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
            }
            $flagInput = Flag::where('id', $flag->flagId)->first();
            if ($flagInput) {
                $heading = $getFlag ? 'Patient Status Flag Assigned' : 'Work Status Flag Assigned';
                $timeLine = [
                    'patientId' => $patientId->id, 'heading' => $heading, 'title' => $flagInput->name . ' ' . 'Flag Added <b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . ' ' . $userInput->middleName . '</b>', 'type' => 7,
                    'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'refrenceId' => $flag->id, 'providerId' => $provider
                ];
                PatientTimeLine::create($timeLine);
            }
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientFlags', 'tableId' => $flag->id, 'providerId' => $provider,
                'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            $data = PatientFlag::where('id', $flag->id)->first();
            $userdata = fractal()->item($data)->transformWith(new PatientFlagTransformer())->toArray();
            $message = ["message" => trans('messages.addedSuccesfully')];
            return array_merge($message, $userdata);
            // } else {
            //     return response()->json(['flagId' => array(trans('messages.patientFlag'))], 422);
            // }
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // List Patient TimeLog
    public function patientFlagList($request, $id, $flagId)
    {
        try {
            $patient = Helper::entity('patient', $id);
            $data = PatientFlag::where('patientFlags.patientId', $patient)
                ->leftJoin('flags', 'flags.id', '=', 'patientFlags.flagId')->select('patientFlags.*')->with('flag');

            // $data->leftJoin('providers', 'providers.id', '=', 'patientFlags.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'patientFlags.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('patientFlags.providerLocationId', '=', 'providerLocations.id')->where('patientFlags.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('patientFlags.providerLocationId', '=', 'providerLocationStates.id')->where('patientFlags.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('patientFlags.providerLocationId', '=', 'providerLocationCities.id')->where('patientFlags.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('patientFlags.providerLocationId', '=', 'subLocations.id')->where('patientFlags.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('patientFlags.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['patientFlags.providerLocationId', $providerLocation], ['patientFlags.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['patientFlags.providerLocationId', $providerLocation], ['patientFlags.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['patientFlags.providerLocationId', $providerLocation], ['patientFlags.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['patientFlags.providerLocationId', $providerLocation], ['patientFlags.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['patientFlags.programId', $program], ['patientFlags.entityType', $entityType]]);
            // }
            if (!$flagId) {
                $notAccess = Helper::haveAccess($patient);
                if (!$notAccess) {
                    $getPatient = PatientFlag::select('patientFlags.*')->where('patientId', $patient)->leftJoin('flags', 'flags.id', '=', 'patientFlags.flagId');

                    if ($request->category) {
                        // Category = 1 (Critical/Moderate/WNL)
                        // Category = 2 (Escalation/Watchlist/Communication/Trendind)
                        $getPatient->where('flags.category', $request->category);
                    }
                    if (isset($request->fromDate) && isset($request->toDate)) {
                        $fromDateStr = Helper::date($request->fromDate);
                        $toDateStr = Helper::date($request->toDate);
                        $getPatient->where(function ($query) use ($fromDateStr, $toDateStr) {
                            $query->whereBetween('patientFlags.createdAt', [$fromDateStr, $toDateStr]);
                            $query->WhereNull('patientFlags.deletedAt');
                        })->withTrashed();
                    }
                    $getPatient = $getPatient->with('flag')->orderBy('patientFlags.id', 'DESC')->get();
                    return fractal()->collection($getPatient)->transformWith(new PatientFlagTransformer())->toArray();
                } else {
                    return $notAccess;
                }
            } else {
                $getPatient = PatientFlag::where('patientFlags.udid', $flagId)->with('flag')->first();
                return fractal()->item($getPatient)->transformWith(new PatientFlagTransformer())->toArray();
            }
        } catch (Exception $e) {
            if (isset(auth()->user()->id)) {
                $userId = auth()->user()->id;
            } else {
                $userId = "";
            }
            ErrorLogGenerator::createLog($request, $e, $userId);
            throw new \RuntimeException($e);
        }
    }

    // List Patient Critical Note
    public function listPatientCriticalNote($request, $id, $noteId)
    {
        try {
            $patient = Helper::entity('patient', $id);
            $data = PatientCriticalNote::where('patientCriticalNotes.patientId', $patient)->select('patientCriticalNotes.*');

            // $data->leftJoin('providers', 'providers.id', '=', 'patientCriticalNotes.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'patientCriticalNotes.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('patientCriticalNotes.providerLocationId', '=', 'providerLocations.id')->where('patientCriticalNotes.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('patientCriticalNotes.providerLocationId', '=', 'providerLocationStates.id')->where('patientCriticalNotes.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('patientCriticalNotes.providerLocationId', '=', 'providerLocationCities.id')->where('patientCriticalNotes.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('patientCriticalNotes.providerLocationId', '=', 'subLocations.id')->where('patientCriticalNotes.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('patientCriticalNotes.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['patientCriticalNotes.providerLocationId', $providerLocation], ['patientCriticalNotes.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['patientCriticalNotes.providerLocationId', $providerLocation], ['patientCriticalNotes.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['patientCriticalNotes.providerLocationId', $providerLocation], ['patientCriticalNotes.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['patientCriticalNotes.providerLocationId', $providerLocation], ['patientCriticalNotes.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['patientCriticalNotes.programId', $program], ['patientCriticalNotes.entityType', $entityType]]);
            // }
            if (!$noteId) {
                if (!is_null($request->isRead)) {
                    $data->where('patientCriticalNotes.isRead', $request->isRead);
                } else {
                    $data->where('patientCriticalNotes.patientId', $patient);
                }
                $data = $data->orderBy('patientCriticalNotes.id', 'DESC')->get();
                return fractal()->collection($data)->transformWith(new PatientPatientCriticalNoteTransformer())->toArray();
            } else {
                $data = $data->where('patientCriticalNotes.udid', $noteId)->orderBy('patientCriticalNotes.id', 'DESC')->first();
                return fractal()->item($data)->transformWith(new PatientPatientCriticalNoteTransformer())->toArray();
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Patient Critical Note
    public function createPatientCriticalNote($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $patient = Helper::entity('patient', $id);
            $udid = Str::uuid()->toString();
            $criticalNote = $request->input('criticalNote');
            $patientId = $patient;
            DB::select('CALL createPatientCriticalNote("' . $provider . '","' . $udid . '","' . $patientId . '","' . $criticalNote . '")');
            $userInput = Staff::where('id', auth()->user()->staff->id)->first();
            $timeLine = [
                'patientId' => $patient, 'heading' => 'Pin Added', 'title' => $request->input("criticalNote") . ' ' . '<b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . '</b>', 'type' => 8,
                'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
            ];
            PatientTimeLine::create($timeLine);
            return response()->json(["message" => trans('messages.addedSuccesfully')]);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Patient Critical Note
    public function updatePatientCriticalNote($request, $id, $noteId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $patient = Helper::entity('patient', $id);
            $note = PatientCriticalNote::where('udid', $noteId)->first();
            if (!empty($note)) {
                $noteId = $note->id;
                $criticalNote = array();
                if (!empty($request->input('criticalNote'))) {
                    $criticalNote['criticalNote'] = $request->input('criticalNote');
                }
                if (empty($request->input('isRead'))) {
                    $criticalNote['isRead'] = 0;
                } else {
                    $criticalNote['isRead'] = $request->input('isRead');
                }
                $criticalNote['updatedBy'] = Auth::id();
                $criticalNote['providerId'] = $provider;
                $criticalNote['providerLocationId'] = $providerLocation;
                if (!empty($criticalNote)) {
                    PatientCriticalNote::where([['patientId', $patient], ['id', $noteId]])->update($criticalNote);
                    $userInput = Staff::where('id', auth()->user()->staff->id)->first();
                    if ($request->input('criticalNote')) {
                        $timeLine = [
                            'patientId' => $patient, 'heading' => 'Pin Updated', 'title' => $request->input("criticalNote") . ' ' . '<b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . '</b>', 'type' => 8,
                            'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                        ];
                        PatientTimeLine::create($timeLine);
                    } elseif ($request->input('isRead') == 1) {
                        $timeLine = [
                            'patientId' => $patient, 'heading' => 'Pin Deleted', 'title' => $note->criticalNote . ' ' . '<b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . '</b>', 'type' => 8,
                            'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                        ];
                        PatientTimeLine::create($timeLine);
                    } elseif ($request->input('isRead') == 0) {
                        $timeLine = [
                            'patientId' => $patient, 'heading' => 'Pin Restore', 'title' => $note->criticalNote . ' ' . '<b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . '</b>', 'type' => 8,
                            'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                        ];
                        PatientTimeLine::create($timeLine);
                    }
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patientCriticalNotes', 'tableId' => $noteId,
                        'value' => json_encode($note), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                }
            }
            return response()->json(['message' => trans('messages.updatedSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Patient Critical Note
    public function deletePatientCriticalNote($request, $id, $noteId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $patient = Helper::entity('patient', $id);
            $note = PatientCriticalNote::where('udid', $noteId)->first();
            if (!empty($note)) {
                $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                PatientCriticalNote::where([['patientId', $patient], ['id', $note->id]])->update($input);
                $userInput = Staff::where('id', auth()->user()->staff->id)->first();
                $timeLine = [
                    'patientId' => $patient, 'heading' => 'Pin Deleted', 'title' => $note->criticalNote . ' ' . '<b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . '</b>', 'type' => 8,
                    'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                ];
                PatientTimeLine::create($timeLine);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientCriticalNotes', 'tableId' => $note->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($input), 'type' => 'Deleted', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                ];
                ChangeLog::create($changeLog);
                PatientCriticalNote::where([['patientId', $patient], ['id', $note->id]])->delete();
            }
            return response()->json(['message' => trans('messages.deletedSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // add patient family member
    public function patientFamilyAdd($request, $id, $familyId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $postData = $request->all();
            $patient = Helper::entity('patient', $id);
            if ($request->input('isPrimary') == true) {
                $data = PatientFamilyMember::where('patientId', $patient)->first();
                if ($data) {
                    $isPrimary = ['isPrimary' => 0, 'updatedBy' => Auth::id()];
                    PatientFamilyMember::where('patientId', $patient)->update($isPrimary);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patientFamilyMembers', 'tableId' => $data->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($isPrimary), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                }
                $isPrimaryInput = 1;
            } else {
                $isPrimaryInput = 0;
            }
            if (!$familyId) {
                $userData = User::where([['email', $request->input('familyEmail')], ['roleId', 6]])->first();
                if ($userData) {
                    $userEmail = $userData->id;
                    $familyMember = [
                        'firstName' => $request->input('firstName'), 'middleName' => $request->input('middleName'), 'lastName' => $request->input('lastName'),
                        'phoneNumber' => $request->input('familyPhoneNumber'), 'contactTypeId' => json_encode($request->input('familyContactType')),
                        'contactTimeId' => json_encode($request->input('familyContactTime')), 'genderId' => $request->input('familyGender'),
                        'relationId' => $request->input('relation'), 'patientId' => $patient, 'vital' => $request->input('vitalAuthorization'),
                        'messages' => $request->input('messageAuthorization'), 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'createdBy' => Auth::id(), 'userId' => $userEmail, 'udid' => Str::uuid()->toString(), 'isPrimary' => $isPrimaryInput
                    ];
                    $data = PatientFamilyMember::create($familyMember);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patientFamilyMembers', 'tableId' => $data->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($familyMember), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                    ];
                    ChangeLog::create($changeLog);
                } else {
                    // Added family in user Table
                    $password = Str::random("10");
                    $familyMemberUser = [
                        'password' => Hash::make($password), 'udid' => Str::uuid()->toString(), 'email' => $request->input('familyEmail'), 'entityType' => $entityType,
                        'emailVerify' => 1, 'createdBy' => Auth::id(), 'roleId' => 6, 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'profilePhoto' => $request->input('profilePhoto')
                    ];
                    $fam = User::create($familyMemberUser);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => $fam->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($familyMemberUser), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                    ];
                    ChangeLog::create($changeLog);
                    if (isset($postData["familyPhoneNumber"]) && !empty($postData["familyPhoneNumber"])) {
                        $msgSMSObj = ConfigMessage::where("type", "patientFamilyAdd")
                            ->where("entityType", "sendSMS")
                            ->first();
                        $variablesArr = array(
                            "password" => $password
                        );

                        $message = '';
                        $message .= "<p>Hi " . $request->input('firstName') . '' . $request->input('lastName') . ",</p>";
                        $message .= "<p>Your account was successfully created with Virtare Health. Your password is " . $password . "</p>";
                        $message .= "<p>Thanks</p>";
                        $message .= "<p>Virtare Health</p>";
                        if (isset($msgSMSObj->messageBody)) {
                            $messageBody = $msgSMSObj->messageBody;
                            $message = Helper::getMessageBody($messageBody, $variablesArr);
                        }
                        $responseAPi = Helper::sendBandwidthMessage($message, $postData["familyPhoneNumber"]);
                    }

                    if (isset($request->familyEmail)) {
                        $emailData = [
                            'email' => $request->familyEmail,
                            'firstName' => $request->firstName,
                            'template_name' => 'welcome_email'
                        ];
                        event(new SetUpPasswordEvent($emailData));
                    }

                    //Added Family in patientFamilyMember Table

                    $familyMember = [
                        'firstName' => $request->input('firstName'), 'middleName' => $request->input('middleName'), 'lastName' => $request->input('lastName'),
                        'phoneNumber' => $request->input('familyPhoneNumber'),
                        'contactTypeId' => json_encode($request->input('familyContactType')), 'contactTimeId' => json_encode($request->input('familyContactTime')),
                        'genderId' => $request->input('familyGender'), 'relationId' => $request->input('relation'), 'patientId' => $patient,
                        'createdBy' => Auth::id(), 'userId' => $fam->id, 'udid' => Str::uuid()->toString(), 'vital' => 1,
                        'messages' => 1, 'isPrimary' => $isPrimaryInput, 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                    ];
                    $data = PatientFamilyMember::create($familyMember);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patientFamilyMembers', 'tableId' => $data->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($familyMember), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                    ];
                    ChangeLog::create($changeLog);
                }
                $userdata = fractal()->item($data)->transformWith(new PatientFamilyMemberTransformer())->toArray();
                $message = ["message" => trans('messages.addedSuccesfully')];
            } else {
                $family = PatientFamilyMember::where('udid', $familyId)->first();
                $userData = User::where([['email', $request->input('familyEmail')], ['roleId', 6]])->first();
                if ($userData) {
                    //Updated Family in patientFamilyMember Table
                    $familyMember = [
                        'firstName' => $request->input('firstName'), 'middleName' => $request->input('middleName'), 'lastName' => $request->input('lastName'), 'phoneNumber' => $request->input('familyPhoneNumber'),
                        'contactTypeId' => json_encode($request->input('familyContactType')), 'contactTimeId' => json_encode($request->input('familyContactTime')),
                        'genderId' => $request->input('familyGender'), 'relationId' => $request->input('relation'), 'userId' => $userData->id,
                        'updatedBy' => Auth::id(), 'vital' => $request->input('vitalAuthorization'), 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'messages' => $request->input('messageAuthorization'), 'isPrimary' => $isPrimaryInput, 'entityType' => $entityType
                    ];
                    PatientFamilyMember::where('udid', $familyId)->update($familyMember);
                    $familyMemberData = Helper::entity('familyMember', $familyId);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patientFamilyMembers', 'tableId' => $familyMemberData, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($familyMember), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                    ];
                    ChangeLog::create($changeLog);
                    $userId = $userData->id;
                } else {
                    $familyMemberUser = [
                        'email' => $request->input('familyEmail'), 'updatedBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation
                    ];
                    $fam = User::where('id', $family->userId)->update($familyMemberUser);
                    $userId = $family->userId;
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => $family->userId, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($familyMemberUser), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                    ];
                    ChangeLog::create($changeLog);
                    $familyMember = [
                        'firstName' => $request->input('firstName'), 'middleName' => $request->input('middleName'), 'lastName' => $request->input('lastName'), 'phoneNumber' => $request->input('familyPhoneNumber'),
                        'contactTypeId' => json_encode($request->input('familyContactType')), 'contactTimeId' => json_encode($request->input('familyContactTime')),
                        'genderId' => $request->input('familyGender'), 'relationId' => $request->input('relation'),
                        'updatedBy' => Auth::id(), 'vital' => $request->input('vitalAuthorization'), 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'messages' => $request->input('messageAuthorization'), 'isPrimary' => $isPrimaryInput
                    ];
                    PatientFamilyMember::where('udid', $familyId)->update($familyMember);
                    $familyMemberInput = Helper::entity('familyMember', $familyId);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patientFamilyMembers', 'tableId' => $familyMemberInput, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($familyMember), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                    ];
                    ChangeLog::create($changeLog);
                }
                $familyMember = array();
                if (!empty($request->input('firstName'))) {
                    $familyMember['firstName'] = $request->input('firstName');
                }
                if (!empty($request->input('middleName'))) {
                    $familyMember['middleName'] = $request->input('middleName');
                }
                if (!empty($request->input('lastName'))) {
                    $familyMember['lastName'] = $request->input('lastName');
                }
                if (!empty($request->input('familyPhoneNumber'))) {
                    $familyMember['phoneNumber'] = $request->input('familyPhoneNumber');
                }
                if (!empty($request->input('familyContactType'))) {
                    $familyMember['contactTypeId'] = $request->input('familyContactType');
                }
                if (!empty($request->input('familyContactTime'))) {
                    $familyMember['contactTimeId'] = $request->input('familyContactTime');
                }
                if (!empty($request->input('familyGender'))) {
                    $familyMember['genderId'] = $request->input('familyGender');
                }
                if (!empty($request->input('relation'))) {
                    $familyMember['relationId'] = $request->input('relation');
                }
                if (!empty($request->input('vitalAuthorization'))) {
                    $familyMember['vital'] = $request->input('vitalAuthorization');
                }
                if (!empty($request->input('messageAuthorization'))) {
                    $familyMember['message'] = $request->input('messageAuthorization');
                }
                $familyMember['userId'] = $userId;
                $familyMember['isPrimary'] = $isPrimaryInput;
                $familyMember['updatedBy'] = Auth::id();
                PatientFamilyMember::where('udid', $familyId)->update($familyMember);
                $familyMemberInput = Helper::entity('familyMember', $familyId);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientFamilyMembers', 'tableId' => $familyMemberInput, 'providerId' => $provider,
                    'value' => json_encode($familyMember), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
                $data = PatientFamilyMember::where('udid', $familyId)->first();
                $userdata = fractal()->item($data)->transformWith(new PatientFamilyMemberTransformer())->toArray();
                $message = ['message' => trans('messages.updatedSuccesfully')];
            }
            return array_merge($message, $userdata);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // list patient family member
    public function patientFamilyList($request, $id, $familyId)
    {
        try {
            $patient = Helper::entity('patient', $id);
            $data = PatientFamilyMember::where('patientFamilyMembers.patientId', $patient)->select('patientFamilyMembers.*');

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
            if (!$familyId) {
                $data = $data->orderBy('patientFamilyMembers.createdAt', 'DESC')->get();
                return fractal()->collection($data)->transformWith(new PatientFamilyMemberTransformer())->toArray();
            } else {
                $data = $data->where('patientFamilyMembers.udid', $familyId)->first();
                return fractal()->item($data)->transformWith(new PatientFamilyMemberTransformer())->toArray();
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // delete patient family member
    public function patientFamilyDelete($request, $id, $familyId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $data = PatientFamilyMember::where('udid', $familyId)->first();
            $patientId = Helper::tableName('App\Models\Patient\Patient', $id);
            $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            PatientFamilyMember::where([['udid', $familyId], ['patientId', $patientId]])->update($input);
            $userId = PatientFamilyMember::where([['userId', $data->userId], ['patientId', '!=', $patientId]])->exists();
            if (empty($userId)) {
                User::where('id', $data->userId)->update($input);
                $changeLogUser = [
                    'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => $data->userId, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                ];
                ChangeLog::create($changeLogUser);
                User::where('id', $data->userId)->delete();
            }
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientFamilyMembers', 'tableId' => $data->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);
            PatientFamilyMember::where([['udid', $familyId], ['patientId', $patientId]])->delete();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Patient Family Member
    public function phycisianPatient($request, $id)
    {
        try {
            $patient = Helper::entity('patient', $id);
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $data = PatientFamilyMember::where('patientFamilyMembers.patientId', $patient)->select('patientFamilyMembers.*')
                ->leftJoin('providerLocations', 'providerLocations.id', '=', 'patientFamilyMembers.providerLocationId');
            if ($provider) {
                $data->where('patientFamilyMembers.providerId', $provider);
            }
            if ($providerLocation) {
                $data->where(function ($query) use ($providerLocation) {
                    $query->where('patientFamilyMembers.providerLocationId', $providerLocation)->orWhere('providerLocations.parent', $providerLocation);
                });
            }
            $data = $data->orderBy('patientFamilyMembers.isPrimary', 'DESC')->get();
            return fractal()->collection($data)->transformWith(new PatientFamilyMemberTransformer())->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Patient Emergency
    public function patientEmergencyAdd($request, $id, $emergencyId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            if (!$emergencyId) {
                $patient = Helper::entity('patient', $id);
                $emergencyContact = [
                    'firstName' => $request->input('firstName'), 'middleName' => $request->input('middleName'), 'lastName' => $request->input('lastName'),
                    'phoneNumber' => $request->input('phoneNumber'), 'contactTypeId' => json_encode($request->input('contactType')), 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'contactTimeId' => json_encode($request->input('contactTime')), 'genderId' => $request->input('gender'), 'patientId' => $patient, 'entityType' => $entityType,
                    'createdBy' => Auth::id(), 'email' => $request->input('emergencyEmail'), 'udid' => Str::uuid()->toString(), 'sameAsPrimary' => $request->input('sameAsPrimary')
                ];
                $emergency = PatientEmergencyContact::create($emergencyContact);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientEmergencyContacts', 'tableId' => $emergency->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($emergencyContact), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                ];
                ChangeLog::create($changeLog);

                $data = PatientEmergencyContact::where('id', $emergency->id)->first();
                $userdata = fractal()->item($data)->transformWith(new PatientFamilyMemberTransformer(false))->toArray();
                $message = ["message" => trans('messages.addedSuccesfully')];
            } else {
                $emergencyContact = [
                    'firstName' => $request->input('firstName'), 'middleName' => $request->input('middleName'), 'lastName' => $request->input('lastName'),
                    'phoneNumber' => $request->input('phoneNumber'), 'contactTypeId' => json_encode($request->input('contactType')),
                    'contactTimeId' => json_encode($request->input('contactTime')), 'genderId' => $request->input('gender'), 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'updatedBy' => Auth::id(), 'email' => $request->input('emergencyEmail'), 'sameAsPrimary' => $request->input('sameAsPrimary')
                ];
                PatientEmergencyContact::where('udid', $emergencyId)->update($emergencyContact);
                $emergencyData = Helper::entity('emergency', $emergencyId);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientEmergencyContacts', 'tableId' => $emergencyData, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($emergencyContact), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                ];
                ChangeLog::create($changeLog);
                $data = PatientEmergencyContact::where('udid', $emergencyId)->first();
                $userdata = fractal()->item($data)->transformWith(new PatientFamilyMemberTransformer(false))->toArray();
                $message = ['message' => trans('messages.updatedSuccesfully')];
            }
            return array_merge($message, $userdata);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Patient Emergency
    public function patientEmergencyList($request, $id, $emergencyId)
    {
        try {
            $patient = Helper::entity('patient', $id);
            $data = PatientEmergencyContact::where('patientEmergencyContacts.patientId', $patient)->select('patientEmergencyContacts.*');

            // $data->leftJoin('providers', 'providers.id', '=', 'patientEmergencyContacts.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'patientEmergencyContacts.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('patientEmergencyContacts.providerLocationId', '=', 'providerLocations.id')->where('patientEmergencyContacts.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('patientEmergencyContacts.providerLocationId', '=', 'providerLocationStates.id')->where('patientEmergencyContacts.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('patientEmergencyContacts.providerLocationId', '=', 'providerLocationCities.id')->where('patientEmergencyContacts.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('patientEmergencyContacts.providerLocationId', '=', 'subLocations.id')->where('patientEmergencyContacts.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('patientEmergencyContacts.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['patientEmergencyContacts.providerLocationId', $providerLocation], ['patientEmergencyContacts.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['patientEmergencyContacts.providerLocationId', $providerLocation], ['patientEmergencyContacts.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['patientEmergencyContacts.providerLocationId', $providerLocation], ['patientEmergencyContacts.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['patientEmergencyContacts.providerLocationId', $providerLocation], ['patientEmergencyContacts.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['patientEmergencyContacts.programId', $program], ['patientEmergencyContacts.entityType', $entityType]]);
            // }
            if (!$emergencyId) {
                $data = $data->orderBy('patientEmergencyContacts.createdAt', 'DESC')->get();
                return fractal()->collection($data)->transformWith(new PatientFamilyMemberTransformer(false))->toArray();
            } else {
                $data = $data->where('patientEmergencyContacts.udid', $emergencyId)->first();
                return fractal()->item($data)->transformWith(new PatientFamilyMemberTransformer(false))->toArray();
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Patient Emergency
    public function patientEmergencyDelete($request, $id, $emergencyId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            PatientEmergencyContact::where('udid', $emergencyId)->update($input);
            $data = Helper::entity('emergency', $emergencyId);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientEmergencyContacts', 'tableId' => $data, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);
            PatientEmergencyContact::where('udid', $emergencyId)->delete();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Patient Timeline Type
    public function patientTimeLineTypeList()
    {
        try {
            $data = TimeLineType::select('timeLineTypes.*');

            // $data->leftJoin('providers', 'providers.id', '=', 'timeLineTypes.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'timeLineTypes.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('timeLineTypes.providerLocationId', '=', 'providerLocations.id')->where('timeLineTypes.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('timeLineTypes.providerLocationId', '=', 'providerLocationStates.id')->where('timeLineTypes.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('timeLineTypes.providerLocationId', '=', 'providerLocationCities.id')->where('timeLineTypes.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('timeLineTypes.providerLocationId', '=', 'subLocations.id')->where('timeLineTypes.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('timeLineTypes.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['timeLineTypes.providerLocationId', $providerLocation], ['timeLineTypes.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['timeLineTypes.providerLocationId', $providerLocation], ['timeLineTypes.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['timeLineTypes.providerLocationId', $providerLocation], ['timeLineTypes.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['timeLineTypes.providerLocationId', $providerLocation], ['timeLineTypes.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['timeLineTypes.programId', $program], ['timeLineTypes.entityType', $entityType]]);
            // }
            $data = $data->get();
            return fractal()->collection($data)->transformWith(new PatientTimeLineTypeTransformer())->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Patient Profile
    public function profileUpdate($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $input = ['profilePhoto' => $request->input('profilePhoto'), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            $patient = Patient::where('udid', $id)->first();
            $user = User::where('id', $patient->userId)->update($input);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => $patient->userId, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
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

    // Patient Responsible
    public function responsiblePatient($request, $id, $responsibleId)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            if (!$responsibleId) {
                $patientId = Helper::tableName('App\Models\Patient\Patient', $id);
                $responsible = PatientResponsible::where('patientId', $patientId)->first();
                if ($responsible) {
                    $input = ['isActive' => 0, 'isDelete' => 1, 'deletedBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                    PatientResponsible::where('patientId', $patientId)->update($input);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'patientResponsibles', 'tableId' => $responsible->patientResponsibleId, 'entityType' => $entityType,
                        'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation
                    ];
                    ChangeLog::create($changeLog);
                    PatientResponsible::where('patientId', $patientId)->delete();
                }
                $self = $request->self == true ? 1 : 0;
                $input = [
                    'udid' => Str::uuid()->toString(), 'self' => $self, 'firstName' => $request->input('firstName'), 'middleName' => $request->input('middleName'), 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'lastName' => $request->input('lastName'), 'phoneNumber' => $request->input('phoneNumber'), 'contactType' => json_encode($request->input('contactType')), 'entityType' => $entityType,
                    'contactTime' => json_encode($request->input('contactTime')), 'genderId' => $request->input('gender'), 'patientId' => $patientId, 'email' => $request->input('email'), 'relationId' => $request->input('relation')
                ];
                $patient = PatientResponsible::create($input);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientResponsibles', 'tableId' => $patient->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                ];
                ChangeLog::create($changeLog);
                $data = PatientResponsible::where('patientResponsibleId', $patient->id)->first();
                $userdata = fractal()->item($data)->transformWith(new PatientResponsibleTransformer(false))->toArray();
                $message = ["message" => trans('messages.addedSuccesfully')];
            } else {
                $self = $request->self == true ? 1 : 0;
                $input = [
                    'self' => $request->input('self'), 'firstName' => $request->input('firstName'), 'middleName' => $request->input('middleName'), 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'lastName' => $request->input('lastName'), 'phoneNumber' => $request->input('phoneNumber'), 'contactType' => json_encode($request->input('contactType')),
                    'contactTime' => json_encode('contactTime'), 'genderId' => $request->input('gender'), 'email' => $request->input('email'), 'relationId' => $request->input('relation')
                ];
                PatientResponsible::where('udid', $responsibleId)->update($input);
                $data = PatientResponsible::where('udid', $responsibleId)->first();
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientResponsibles', 'tableId' => $data->patientResponsibleId, 'entityType' => $entityType,
                    'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation
                ];
                ChangeLog::create($changeLog);
                $userdata = fractal()->item($data)->transformWith(new PatientResponsibleTransformer(false))->toArray();
                $message = ['message' => trans('messages.updatedSuccesfully')];
            }
            $endData = array_merge($message, $userdata);
            return $endData;
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Patient Responsible
    public function listResponsiblePatient($request, $id, $responsibleId)
    {
        try {
            $patientId = Helper::tableName('App\Models\Patient\Patient', $id);
            $data = PatientResponsible::where('patientResponsibles.patientId', $patientId)->select('patientResponsibles.*');

            // $data->leftJoin('providers', 'providers.id', '=', 'patientResponsibles.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'patientResponsibles.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('patientResponsibles.providerLocationId', '=', 'providerLocations.id')->where('patientResponsibles.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('patientResponsibles.providerLocationId', '=', 'providerLocationStates.id')->where('patientResponsibles.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('patientResponsibles.providerLocationId', '=', 'providerLocationCities.id')->where('patientResponsibles.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('patientResponsibles.providerLocationId', '=', 'subLocations.id')->where('patientResponsibles.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('patientResponsibles.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['patientResponsibles.providerLocationId', $providerLocation], ['patientResponsibles.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['patientResponsibles.providerLocationId', $providerLocation], ['patientResponsibles.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['patientResponsibles.providerLocationId', $providerLocation], ['patientResponsibles.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['patientResponsibles.providerLocationId', $providerLocation], ['patientResponsibles.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['patientResponsibles.programId', $program], ['patientResponsibles.entityType', $entityType]]);
            // }
            if ($responsibleId) {
                $data = $data->where('patientResponsibles.udid', $responsibleId)->first();
                return fractal()->item($data)->transformWith(new PatientResponsibleTransformer())->toArray();
            } else {
                $data = $data->get();
                return fractal()->collection($data)->transformWith(new PatientResponsibleTransformer())->toArray();
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Patient Status
    public function patientUpdateStatus($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $input = ['isActive' => $request->input('isActive'), 'updatedBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            Patient::where('udid', $id)->withTrashed()->update($input);
            $patient = Patient::where('udid', $id)->withTrashed()->first();
            User::where('id', $patient->userId)->withTrashed()->update($input);
            if ($patient) {
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patients', 'tableId' => $patient->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                ];
                ChangeLog::create($changeLog);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => $patient->userId, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                ];
                ChangeLog::create($changeLog);
            }
            return response()->json(['message' => trans('messages.updatedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Reset Password
    public function passwordReset($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $password = Str::random("10");
            $patient = Patient::where('udid', $id)->first();
            $input = ['password' => Hash::make($password), 'updatedBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
            User::where('id', $patient->userId)->update($input);
            $userIds = array();
            if (isset($patient->phoneNumber) && !empty($patient->phoneNumber)) {
                $msgSMSObj = ConfigMessage::where("type", "passwordReset")
                    ->where("entityType", "sendSMS")
                    ->first();
                $variablesArr = array(
                    "password" => $password
                );
                $message = "Successfully Reset Password. Your New Password is " . $password;
                if (isset($msgSMSObj->messageBody)) {
                    $messageBody = $msgSMSObj->messageBody;
                    $message = Helper::getMessageBody($messageBody, $variablesArr);
                }
                $responseApi = Helper::sendBandwidthMessage($message, $patient->phoneNumber);
            }
            $user = User::where('id', $patient->userId)->first();
            if (isset($user->email)) {
                $to = $user->email;
                $userIds[$to] = $user->id;
                $msgObj = ConfigMessage::where("type", "passwordReset")
                    ->where("entityType", "sendMail")
                    ->first();
                $msgHeaderObj = ConfigMessage::where("type", "header")
                    ->where("entityType", "sendMail")
                    ->first();
                $msgFooterObj = ConfigMessage::where("type", "footer")
                    ->where("entityType", "sendMail")
                    ->first();
                $variablesArr = array(
                    "password" => $password
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
                    $message = "Your New password is " . $password;
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
                    $subject = "Create new Account";
                }

                Helper::commonMailjet($to, $fromName, $message, $subject, '', $userIds, 'Password Reset', '');
            }
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => $patient->userId, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);
            return response()->json(['message' => trans('messages.passwordReset')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Patient Flag
    public function addPatientFlag($request)
    {
        try {
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $udid = Str::uuid()->toString();
            $flags = $request->input('flag');
            $reason = $request->input('deleteReason') ? $request->input('deleteReason') : NULL;
            foreach ($flags as $flag) {
                $Patientflag = PatientFlag::where('udid', $flag)->first();
                $flags = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, 'removalReasonId' => $reason, 'providerId' => $providerId, 'providerLocationId' => $providerLocation];
                PatientFlag::where('id', $Patientflag->id)->update($flags);
                PatientFlag::where('id', $Patientflag->id)->delete();
                $note = [
                    'udid' => $udid, 'note' => $request->reason, 'referenceId' => $Patientflag->flagId, 'entitytype' => 'patientFlag',
                    'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
                ];
                $noteData = Note::create($note);
            }
            if (auth()->user()->roleId == 4) {
                $userInput = Patient::where('id', auth()->user()->patient->id)->first();
            } else {
                $userInput = Staff::where('id', auth()->user()->staff->id)->first();
            }
            $timeLine = [
                'patientId' => $Patientflag->patientId, 'heading' => 'Vital Note Added', 'title' => $request->reason . ' ' . '<b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . '</b>', 'type' => 6,
                'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
            ];
            PatientTimeLine::create($timeLine);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'notes', 'tableId' => $noteData->id, 'entityType' => $entityType,
                'value' => json_encode($note), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation
            ];
            ChangeLog::create($changeLog);
            return response()->json(['message' => trans('messages.createdSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Patientg Provider
    public function patientProviderUpdate($request, $id)
    {
        try {
            $input = ['providerId' => $request->provider, 'updatedBy' => Auth::id()];
            Patient::where('udid', $id)->update($input);
            return response()->json(['message' => trans('messages.createdSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Patient Group
    public function patientGroupAdd($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $patient = Helper::tableName('App\Models\Patient\Patient', $id);
            foreach ($request->groups as $value) {
                $group = Group::where('udid', $value)->first();
                if ($group->patientCount != $group->patientAdd) {
                    $groupData = PatientGroup::where([['patientId', $patient], 'groupId' => $group->groupId])->first();
                    if (!$groupData) {
                        $input = ['udid' => Str::uuid()->toString(), 'patientId' => $patient, 'groupId' => $group->groupId, 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType];
                        $patientData = PatientGroup::create($input);
                        $count = $group->patientAdd;
                        $total = $count + 1;
                        Group::where('udid', $value)->update(['patientAdd' => $total]);
                        $changeLog = [
                            'udid' => Str::uuid()->toString(), 'table' => 'patientGroups', 'tableId' => $patientData->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                            'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                        ];
                        ChangeLog::create($changeLog);
                    } else {
                        return response()->json(['groupId' => array(trans('messages.patientGroup'))], 422);
                    }
                } else {
                    return response()->json(['groupId' => array(trans('messages.patientCount'))], 422);
                }
            }
            $data = PatientGroup::where('patientId', $patient)->get();
            $userdata = fractal()->collection($data)->transformWith(new PatientGroupTransformer())->toArray();
            $message = ['message' => trans('messages.createdSuccesfully')];
            return array_merge($message, $userdata);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Patient Group
    public function patientGroupList($request, $id)
    {
        try {
            $patient = Helper::tableName('App\Models\Patient\Patient', $id);
            $data = PatientGroup::select('patientGroups.*')->with('group');

            // $data->leftJoin('providers', 'providers.id', '=', 'patientGroups.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'patientGroups.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('patientGroups.providerLocationId', '=', 'providerLocations.id')->where('patientGroups.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('patientGroups.providerLocationId', '=', 'providerLocationStates.id')->where('patientGroups.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('patientGroups.providerLocationId', '=', 'providerLocationCities.id')->where('patientGroups.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('patientGroups.providerLocationId', '=', 'subLocations.id')->where('patientGroups.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('patientGroups.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['patientGroups.providerLocationId', $providerLocation], ['patientGroups.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['patientGroups.providerLocationId', $providerLocation], ['patientGroups.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['patientGroups.providerLocationId', $providerLocation], ['patientGroups.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['patientGroups.providerLocationId', $providerLocation], ['patientGroups.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['patientGroups.programId', $program], ['patientGroups.entityType', $entityType]]);
            // }
            $data->orderBy('patientGroups.createdAt', 'DESC');
            $data = $data->where('patientGroups.patientId', $patient)->get();
            return fractal()->collection($data)->transformWith(new PatientGroupTransformer())->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // delete patient flag, while add new flag to patient
    public function flagDelete($patientId, $flagId)
    {
        try {
            $flags = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1];
            PatientFlag::where('patientId', $patientId->id)->update($flags);
            $flagData = PatientFlag::where('patientId', $patientId->id)->first();
            if ($flagData) {
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientFlags', 'tableId' => $flagData->id,
                    'value' => json_encode($flags), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
            }
            PatientFlag::where([['patientId', $patientId->id], ['flagId', $flagId]])->delete();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function patientFlagDelete($request, $patientId, $flagId)
    {
        try {
            $provider = Helper::providerId();
            $patient = Helper::tableName('App\Models\Patient\Patient', $patientId);
            $flags = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1];
            PatientFlag::where([['patientId', $patient], ['udid', $flagId]])->update($flags);
            $flagData = PatientFlag::where([['patientId', $patient], ['udid', $flagId]])->first();
            if ($flagData) {
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'patientFlags', 'tableId' => $flagData->id,
                    'value' => json_encode($flags), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
            }
            $flag = Flag::where('id', $flagData->flagId)->first();
            $userInput = Staff::where('id', auth()->user()->staff->id)->first();
            $array2 = array(7, 8, 9);
            $patientFlagNew = in_array($flagData->flagId, $array2);
            $heading = $patientFlagNew ? 'Patient Status Flag Remove' : 'Work Status Flag Remove';
            $timeLine = [
                'patientId' => $patient, 'heading' => $heading, 'title' => $flag->name . ' Flag Remove <b>By' . ' ' . $userInput->lastName . ',' . ' ' . $userInput->firstName . ' ' . $userInput->middleName . '</b> Reason: ' . $request->reason,
                'type' => 7, 'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'refrenceId' => $flag->id, 'providerId' => $provider
            ];
            PatientTimeLine::create($timeLine);
            PatientFlag::where([['patientId', $patient], ['udid', $flagId]])->delete();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
