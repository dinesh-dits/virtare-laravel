<?php

namespace App;

use Exception;
use Carbon\Carbon;
use \Mailjet\Resources;
use \Mailjet\Client;
use App\Models\User\User;
use App\Jobs\SendEmailJob;
use App\Models\EmailStats;
use App\Models\Guest\Guest;
use App\Models\Staff\Staff;
use Illuminate\Support\Str;

#use App\Models\Client\Client;
use App\Models\Log\ChangeLog;
use BandwidthLib\APIException;
use App\Models\Patient\Patient;
use App\Models\Program\Program;
use BandwidthLib\Configuration;
use App\Models\Client\Site\Site;
use App\Models\Provider\Provider;
use App\Models\Relation\Relation;
use App\Models\UserRole\UserRole;
use BandwidthLib\BandwidthClient;
use Illuminate\Support\Facades\DB;
use App\Models\Widget\WidgetAccess;
use App\Models\Patient\PatientStaff;
use App\Models\Patient\PatientVital;
use Illuminate\Support\Facades\Mail;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Patient\PatientDevice;
use App\Models\Patient\PatientTimeLog;
use App\Models\Appointment\Appointment;
use App\Services\Api\CustomFormService;
use Illuminate\Support\Facades\Storage;
use App\Models\ConfigMessage\MessageLog;
use App\Models\Patient\PatientInventory;
use App\Models\Patient\PatientPhysician;
use App\Models\Provider\ProviderLocation;

// use App\Jobs\SendEmailJob;
// use App\Models\EmailStats;
// use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\BugReport\BugReportDocument;
use App\Models\Patient\PatientFamilyMember;
use App\Models\Client\Client as ClientClient;
use App\Models\Patient\PatientEmergencyContact;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\GeneralParameter\GeneralParameter;
use App\Models\Staff\StaffProvider\StaffProvider;

// use App\Jobs\SendEmailJob;
// use App\Models\EmailStats;
// use Illuminate\Pagination\LengthAwarePaginator;
use BandwidthLib\Messaging\Models\MessageRequest;
use App\Models\StaffAvailability\StaffAvailability;
use App\Models\GeneralParameter\GeneralParameterGroup;
use BandwidthLib\PhoneNumberLookup\Models\OrderRequest;

class Helper
{

    public static function email($email)
    {
        return $email;
    }

    // Grouping Date
    public static function dateGroup($data, $date_field)
    {
        $res = $data->sortBy($date_field)->sortByDesc('createdAt')->groupBy(function ($result, $key) use ($date_field) {
            $dt = Carbon::parse($result->{$date_field});
            return $dt->format('Y-m-d');
        });
        $patientData = array();
        foreach ($res as $key => $value) {
            $patient = array();
            $patient['year'] = $key;
            $patient['data'] = $value;
            array_push($patientData, $patient);
        }
        return $patientData;
    }

    // Convert Timestamp into Date
    public static function date($date)
    {
        $date = Carbon::createFromTimestamp($date)->format('Y-m-d H:i:s');

        return $date;
    }

    // Convert Timestamp into Date Formate (Jan 12, 05:12 AM)
    public static function dateFormat($date)
    {
        $date = Carbon::createFromTimestamp($date)->format('M d, Y h:i A');
        return $date;
    }

    // Convert Timestamp into Date Only
    public static function dateOnly($date, $timeZone = 'UTC')
    {
        $date = Carbon::createFromTimestamp($date, $timeZone)->format('Y-m-d');

        return $date;
    }

    // Realtion with Patient
    public static function relation($value, $gender)
    {
        $data = Relation::where([['relationId', $value], ['genderId', $gender]])->with('relation')->first();
        if (!empty($data)) {
            $newData = [
                'relationId' => @$data->reverseRelationId,
                'relation' => @$data->relation->name,
            ];
        } else {
            $newData = [
                'relationId' => "",
                'relation' => "",
            ];
        }
        return $newData;
    }

    // Convert Timestamp into Time
    public static function time($date)
    {
        $date = Carbon::createFromTimestamp($date)->format('H:i:s');

        return $date;
    }

    // Convert udid into id
    public static function entity($entity, $id)
    {
        if ($entity == 'patient') {
            $data = Patient::where('udid', $id)->first();
        } elseif ($entity == 'staff') {
            $data = Staff::where('udid', $id)->first();
        } elseif ($entity == 'auditlog') {
            $data = PatientTimeLog::where('udid', $id)->first();
        } elseif ($entity == 'patientVital') {
            $data = PatientVital::where('udid', $id)->first();
        } elseif ($entity == 'appointment') {
            $data = Appointment::where('udid', $id)->first();
        } elseif ($entity == 'familyMember') {
            $data = PatientFamilyMember::where('udid', $id)->first();
        } elseif ($entity == 'generalParameter') {
            $data = GeneralParameter::where('udid', $id)->first();
        } elseif ($entity == 'generalParameterGroup') {
            $data = GeneralParameterGroup::where('udid', $id)->first();
        } elseif ($entity == 'patientGoals') {
            $data = GeneralParameterGroup::where('udid', $id)->first();
        } elseif ($entity == 'patientStaff') {
            $data = PatientStaff::where('udid', $id)->first();
        } elseif ($entity == 'program') {
            $data = Program::where('udid', $id)->first();
        } elseif ($entity == 'provider') {
            $data = Provider::where('udid', $id)->first();
        } elseif ($entity == 'providerLocation') {
            $data = ProviderLocation::where('udid', $id)->first();
        } elseif ($entity == 'staffAvailability') {
            $data = StaffAvailability::where('udid', $id)->first();
        } elseif ($entity == 'userRole') {
            $data = UserRole::where('udid', $id)->first();
        } elseif ($entity == 'satffProvider') {
            $data = StaffProvider::where('udid', $id)->first();
        } elseif ($entity == 'timeLog') {
            $data = PatientTimeLog::where('udid', $id)->first();
        } elseif ($entity == 'widgetAccess') {
            $data = WidgetAccess::where('udid', $id)->first();
        } elseif ($entity == 'emergency') {
            $data = PatientEmergencyContact::where('udid', $id)->first();
        } elseif ($entity == 'patientDevice') {
            $data = PatientDevice::where('udid', $id)->first();
        } elseif ($entity == 'patientInventory') {
            $data = PatientInventory::where('udid', $id)->first();
        } elseif ($entity == 'client') {
            $data = ClientClient::where('udid', $id)->first();
        } elseif ($entity == 'site') {
            $data = Site::where('udid', $id)->first();
        }
        if (isset($data->id)) {
            return $data->id;
        } else {
            return '';
        }
    }

    // Convert udid into id with Table Name
    public static function tableName($table, $id)
    {
        try {
            $data = $table::where('udid', $id)->first();
            if (isset($data->id) && !empty($data->id)) {
                return $data->id;
            }
            return NULL;
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Convert  iid nto udid with Table Name
    public static function tableNameIdToUdid($table, $id)
    {
        try {
            $data = $table::where('id', $id)->first();
            if (isset($data->udid) && !empty($data->udid)) {
                return $data->udid;
            }
            return NULL;
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Freeswitch Update User
    public static function updateFreeswitchUser()
    {
        $users = User::all();
        $guests = Guest::all();
        ob_start();

        ?>
        <document type="freeswitch/xml">
            <section name="directory">
                <domain name="<?php echo env('SIP_DOMAIN', ''); ?>">
                    <params>
                        <param name="dial-string"
                               value="{presence_id=${dialed_user}@${dialed_domain}}${sofia_contact(${dialed_user}@${dialed_domain})}"/>
                    </params>
                    <groups>
                        <group name="default">
                            <users>
                                <?php
                                foreach ($users as $user) {
                                    if ($user->staff) {
                                        $name = $user->staff->firstName . " " . $user->staff->lastName;
                                    } elseif ($user->patient) {
                                        $name = $user->patient->firstName . " " . $user->patient->lastName;
                                    } else {
                                        $name = '';
                                    }
                                    ?>
                                    <user id="UR<?php echo $user->id; ?>">
                                        <params>
                                            <param name="password" value="123456"/>
                                        </params>
                                        <variables>
                                            <variable name="accountcode" value="UR<?php echo $user->id; ?>"/>
                                            <variable name="user_context" value="default"/>
                                            <variable name="effective_caller_id_name" value="<?php echo $name; ?>"/>
                                            <variable name="effective_caller_id_number"
                                                      value="UR<?php echo $user->id; ?>"/>
                                            <variable name="outbound_caller_id_name" value="<?php echo $name; ?>"/>
                                            <variable name="outbound_caller_id_number"
                                                      value="UR<?php echo $user->id; ?>"/>
                                        </variables>
                                    </user>
                                <?php }
                                foreach ($guests as $guest) {

                                    ?>
                                    <user id="UR0<?php echo $guest->id; ?>">
                                        <params>
                                            <param name="password" value="123456"/>
                                        </params>
                                        <variables>
                                            <variable name="accountcode" value="UR<?php echo $guest->id; ?>"/>
                                            <variable name="user_context" value="default"/>
                                            <variable name="effective_caller_id_name"
                                                      value="<?php echo $guest->name; ?>"/>
                                            <variable name="effective_caller_id_number"
                                                      value="UR<?php echo $guest->id; ?>"/>
                                            <variable name="outbound_caller_id_name"
                                                      value="<?php echo $guest->name; ?>"/>
                                            <variable name="outbound_caller_id_number"
                                                      value="UR<?php echo $guest->id; ?>"/>
                                        </variables>
                                    </user>
                                <?php } ?>
                            </users>
                        </group>
                    </groups>
                </domain>
            </section>
        </document>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();
        $directory = fopen(env('FILE_STORAGE') . "/public/directory.xml", "w") or die("Unable to open file!");
        fwrite($directory, $contents);
        fclose($directory);
    }

    public static function updateFreeswitchConfrence($confrence)
    {
        ob_start();

        ?>
        <document type="freeswitch/xml">
            <section name="dialplan" description="RE Dial Plan For FreeSwitch">
                <context name="default">
                    <extension name="Local_Extension">
                        <condition field="destination_number" expression="^(UR\d{1,20})$">
                            <action application="export" data="dialed_extension=$1"/>
                            <!-- bind_meta_app can have these args <key> [a|b|ab] [a|b|o|s] <app> -->
                            <action application="bind_meta_app" data="1 b s execute_extension::dx XML features"/>
                            <action application="bind_meta_app"
                                    data="2 b s record_session::$${recordings_dir}/${caller_id_number}.${strftime(%Y-%m-%d-%H-%M-%S)}.wav"/>
                            <action application="bind_meta_app" data="3 b s execute_extension::cf XML features"/>
                            <action application="bind_meta_app" data="4 b s execute_extension::att_xfer XML features"/>
                            <action application="set" data="ringback=${us-ring}"/>
                            <action application="set" data="transfer_ringback=$${hold_music}"/>
                            <action application="set" data="call_timeout=30"/>
                            <!-- <action application="set" data="sip_exclude_contact=${network_addr}"/> -->
                            <action application="set" data="hangup_after_bridge=true"/>
                            <!--<action application="set" data="continue_on_fail=NORMAL_TEMPORARY_FAILURE,USER_BUSY,NO_ANSWER,TIMEOUT,NO_ROUTE_DESTINATION"/> -->
                            <action application="set" data="continue_on_fail=true"/>
                            <action application="hash"
                                    data="insert/${domain_name}-call_return/${dialed_extension}/${caller_id_number}"/>
                            <action application="hash"
                                    data="insert/${domain_name}-last_dial_ext/${dialed_extension}/${uuid}"/>
                            <action application="set"
                                    data="called_party_callgroup=${user_data(${dialed_extension}@${domain_name} var callgroup)}"/>
                            <action application="hash"
                                    data="insert/${domain_name}-last_dial_ext/${called_party_callgroup}/${uuid}"/>
                            <action application="hash" data="insert/${domain_name}-last_dial_ext/global/${uuid}"/>
                            <!--<action application="export" data="nolocal:rtp_secure_media=${user_data(${dialed_extension}@${domain_name} var rtp_secure_media)}"/>-->
                            <action application="hash"
                                    data="insert/${domain_name}-last_dial/${called_party_callgroup}/${uuid}"/>
                            <action application="bridge" data="user/${dialed_extension}@${domain_name}"/>
                            <action application="answer"/>
                            <action application="sleep" data="1000"/>
                            <action application="bridge"
                                    data="loopback/app=voicemail:default ${domain_name} ${dialed_extension}"/>
                        </condition>
                    </extension>
                    <?php
                    foreach ($confrence as $conf) {
                        ?>
                        <extension name="Media Server">
                            <condition field="destination_number" expression="^(<?php echo $conf['conferenceId']; ?>)$">
                                <action application="answer"/>
                                <action application="conference" data="internal@myprofile"/>
                            </condition>
                        </extension>
                        <?php
                    }
                    ?>
                </context>
            </section>
        </document>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();
        $directory = fopen(env('FILE_STORAGE') . "/public/dialplan.xml", "w") or die("Unable to open file!");
        fwrite($directory, $contents);
        fclose($directory);
    }

    // Check Login User have Access of Patient
    public static function haveAccess($id)
    {
        $role = auth()->user()->roleId;
        if ($role == 3) {
            $staff = PatientStaff::where([['staffId', auth()->user()->staff->id], ['patientId', $id]])->first();
            if (empty($staff)) {
                if (!Helper::haveAccessAction(null, 490)) {
                    return response()->json(['message' => trans('messages.unauthorized')], 401);
                }
            }
        } elseif ($role == 6) {
            $family = PatientFamilyMember::where([['userId', auth()->user()->id], ['patientId', $id]])->first();
            if (empty($family)) {
                return response()->json(['message' => trans('messages.unauthorized')], 401);
            }
        } elseif ($role == 4) {
            $patient = Patient::where('id', auth()->user()->patient->id)->first();
            if (empty($patient)) {
                return response()->json(['message' => trans('messages.unauthorized')], 401);
            }
        } elseif ($role == 5) {
            $physician = 1;
            if (empty($physician)) {
                return response()->json(['message' => trans('messages.unauthorized')], 401);
            }
        } elseif ($role == 1) {
            $patient = Patient::where('id', $id)->first();
            if (empty($patient)) {
                return response()->json(['message' => trans('messages.unauthorized')], 401);
            }
        }
    }

    // Access of Patient
    public static function haveAccessPatient($id)
    {
        $redirect = "";
        $role = auth()->user()->roleId;
        $patient = Patient::where('udid', $id)->first();
        $id = @$patient->id;
        if ($role == 3) {
            $staff = PatientStaff::where([['staffId', auth()->user()->staff->id], ['patientId', $id]])->first();
            if (empty($staff)) {
                if (!Helper::haveAccessAction(null, 490)) {
                    if ($redirect) {
                        return false;
                    } else {
                        return false;
                    }
                } else {
                    return true;
                }
            }
        } elseif ($role == 6) {
            $family = PatientFamilyMember::where([['userId', auth()->user()->id], ['patientId', $id]])->first();
            if (empty($family)) {
                if ($redirect) {
                    return false;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } elseif ($role == 4) {
            $patient = Patient::where('id', auth()->user()->patient->id)->first();
            if (empty($patient)) {
                if ($redirect) {
                    return false;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } elseif ($role == 5) {
            $physician = PatientPhysician::where([['id', auth()->user()->physician->id], ['patientId', $id]])->first();
            if (empty($physician)) {
                if ($redirect) {
                    return false;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } elseif ($role == 1) {
            $patient = Patient::where('id', $id)->first();
            if (empty($patient)) {
                if ($redirect) {
                    return false;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
    }

    // Patient Last Message
    public static function lastMessage($id)
    {
        $patient = Patient::where('id', $id)->first();
        $data = DB::select(
            'CALL lastConversationMessage("' . $patient->userId . '")',
        );
        if (!empty($data)) {
            return $data[0]->message;
        } else {
            return '';
        }
    }

    // Patient Last Reading of Vitals
    public static function lastReading($id)
    {
        $vital = PatientVital::where('patientId', $id)->latest()->first();

        if (!empty($vital)) {
            return $vital->takeTime;
        } else {
            return '';
        }
    }

    // Send Email
    public static function sendEmail($message = "Test Message", $subject = "Test Mail", $sendTo, $senderName = "test")
    {
        try {
            $data = array(
                'message' => $message,
                'subject' => $subject,
                'sendTo' => $sendTo,
                'senderName' => $senderName,
            );

            Mail::send('emails.common_mail', $data, function ($message) use ($subject, $sendTo, $senderName) {
                $message->from('info@dev.icc-health.com', "Virtare Care");

                $message->to($sendTo, $senderName)->subject($subject);
            });
            return true;
        } catch (APIException $e) {
            return $e->getMessage();
        }
    }

    // Convert Date of Birth into Age
    public static function age($dob)
    {
        $age = Carbon::parse($dob)->diff(Carbon::now())->y;
        return $age;
    }

    public static function sendBandwidthMessage($message = "Test Message", $sendTo)
    {
        $from = env('bandwidth_from_no', null);
        $country_code = env('bandwidth_country_code', null);
        $to = $country_code . $sendTo;
        $applicationId = env('bandwidth_app_id', null);
        $BW_ACCOUNT_ID = env('bandwidth_account_id', null);
        $BW_basicAuthUserName = env('bandwidth_basicAuthUserName', null);
        $BW_basicAuthPassword = env('bandwidth_basicAuthPassword', null);

        $config = new Configuration(
            array(
                'messagingBasicAuthUserName' => $BW_basicAuthUserName,
                'messagingBasicAuthPassword' => $BW_basicAuthPassword,
            )
        );

        $client = new BandwidthClient($config);
        $messagingClient = $client->getMessaging()->getClient();
        $body = new MessageRequest();
        $body->from = $from;
        $body->to = array($to);
        $body->applicationId = $applicationId;
        $body->text = $message;
        $body->priority = "default";
        $body->tag = "Virtare Care";

        try {
            $response = $messagingClient->createMessage($BW_ACCOUNT_ID, $body);
            return $response->getResult();
        } catch (APIException $e) {
            return $e->getMessage();
        }
    }

    public static function haveAccessAction($id, $actionId)
    {
        if ($id) {
            $staff = Helper::entity('staff', $id);
        } else {

            if (isset(auth()->user()->staff->id)) {
                $staff = auth()->user()->staff->id;
            } else {
                $staff = "";
            }
        }
        if (!empty($staff)) {
            $actions = DB::select(
                "CALL assignedRolesActionsList(" . $staff . ",'')",
            );
        }

        $found = false;
        foreach ($actions as $action) {
            if ($action->id == $actionId) {
                $found = true;
            }
        }
        return $found;
    }

    public static function haveAccessActionForExcelExport($id, $actionId)
    {
        if ($id) {
            $staff = Helper::entity('staff', $id);
        } else {

            if (isset(auth()->user()->staff->id)) {
                $staff = auth()->user()->staff->id;
            } else {
                $staff = "";
            }
        }
        if (!empty($staff)) {
            $actions = DB::select(
                "CALL assignedRolesActionsList(" . $staff . ",'')",
            );
        }
        $found = false;
        foreach ($actions as $action) {
            if ($action->id == $actionId) {
                $found = true;
            }
        }
        return $found;
    }

    public static function secondTotimeConvert($seconds)
    {
        $hours = floor($seconds / (60 * 60));
        $mins = floor($seconds / 60 % 60);
        $secs = floor($seconds % 60);

        return sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    }

    public static function currentPatient($table)
    {
        if (isset(auth()->user()->id)) {
            $userId = auth()->user()->id;
        } else {
            $userId = "";
        }

        $data = $table::where('userId', $userId)->first();
        if ($data) {
            return $data->id;
        } else {
            return false;
        }
    }
    // public static function changeLog($data)
    // {
    //     try {
    //         $input=['udid'=>Str::uuid()->toString(),'table'=>$data->table,'tableId'=>$data->tableId,
    //         'value'=>$data->value,'type'=>$data->type,'ip'=>request()->ip()];
    //         dd($input);
    //         $changeLog=ChangeLog::create($input);
    //         dd($changeLog);
    //     }catch (Exception $e) {
    //         throw new \RuntimeException($e);
    //     }
    // }

    // Provider Id from Heading
    public static function providerId()
    {
        $data = request()->header('providerId');
        if ($data && $data > 0) {
            return $data;
        } else {
            return 1;
        }
    }

    // Provider Id from Heading
    public static function programId()
    {
        $data = request()->header('programId');
        if ($data && $data > 0) {
            return $data;
        } else {
            return 5;
        }
    }

    // Provider Location Id from Heading
    public static function providerLocationId()
    {
        $data = request()->header('providerLocationId');
        if ($data && $data > 0) {
            return $data;
        } else {
            return 1;
        }
    }

    // Provider Location entityType from Heading
    public static function entityType()
    {
        $data = request()->header('entityType');
        if ($data && $data != 'undefined') {
            return $data;
        } else {
            return 'Country';
        }
    }

    public static function subLocationId()
    {
        $data = request()->header('subLocationId');
        if ($data && $data > 0) {
            return $data;
        } else {
            return 1;
        }
    }

    // Function to Send Mail
    public function CommonEmailFunction($to, $fromName, $messageText, $subject, $emailFrom = '', $userIds, $type, $refrenceId)
    {
        try {
            $fromEmail = env('MAILJET_FROM_ADDRESS', null);
            $apiKey = env('MAILJET_USERNAME', null);
            $secretKey = env('MAILJET_PASSWORD', null);

            if (!empty($emailFrom)) {
                $fromEmail = $emailFrom;
            }

            $mj = new Client($apiKey, $secretKey, true, ['version' => 'v3.1']);
            $body = [
                'Messages' => [
                    [
                        'From' => [
                            'Email' => $fromEmail,
                            'Name' => $fromName
                        ],
                        'To' => [
                            [
                                'Email' => $to,
                                'Name' => "You"
                            ]
                        ],
                        'Subject' => $subject,
                        'TextPart' => $subject,
                        'HTMLPart' => $messageText
                    ]
                ]
            ];

            // All resources are located in the Resources class
            $response = $mj->post(Resources::$Email, ['body' => $body]);
            // Read the response

            if ($response->success()) {
                $status = "complete";
                $result = $response->getData();
                self::SaveEmailStats($result, $userIds, $type, $refrenceId);


                $res = $result;
                $input = [
                    'udid' => Str::uuid()->toString(), 'table' => $subject, 'tableId' => $to,
                    'value' => json_encode($result), 'type' => "email", 'ip' => request()->ip()
                ];

                $changeLog = ChangeLog::create($input);
            } else {
                $res = $response;
                $status = "pending";
            }

            $programId = Helper::programId();
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $user = User::where("email", $to)->first();
            if (isset($user->id)) {
                $userId = $user->id;
            } else {
                $userId = 0;
            }

            $logArr = [
                'udid' => Str::uuid()->toString(),
                'providerId' => $provider,
                'programId' => $programId,
                'providerLocationId' => $providerLocation,
                'userId' => $userId,
                'status' => $status,
                'subject' => $subject,
                'from' => $fromEmail,
                'to' => $to,
                'body' => $messageText,
                'response' => json_encode($res),
                'type' => "sendEmail",
                'ip' => request()->ip()
            ];

            if ($status == "complete") {
                return true;
            } else {
                return false;
            }

            MessageLog::create($logArr);
        } catch (Exception $e) {
            echo "Error Message:- " . $e->getMessage();
        }
    }

    // Function to Add mail in Queue
    public static function commonMailjet($to, $fromName, $messageText, $subject, $emailFrom = '', $userIds = array(), $type = '', $refrenceId = '')
    {
        try {
            dispatch(new SendEmailJob($to, $fromName, $messageText, $subject, $emailFrom, $userIds, $type, $refrenceId));
        } catch (Exception $e) {
            echo "Error Message:- " . $e->getMessage();
        }
    }

    public static function SaveEmailStats($result, $userIds, $type, $refrence_id = '')
    {
        try {
            if (isset($result) && 0 < count($result)) {
                $emailStats = array();
                foreach ($result['Messages'] as $key => $msg) {
                    if (isset($msg['To']) && 0 < count($msg['To'])) {
                        $emailStats[$key]['email'] = $msg['To'][0]['Email'];
                        $emailStats[$key]['message_id'] = $msg['To'][0]['MessageID'];
                        $emailStats[$key]['user_id'] = isset($userIds[$msg['To'][0]['Email']]) ? $userIds[$msg['To'][0]['Email']] : '';
                        $emailStats[$key]['entity_type'] = $type;
                        $emailStats[$key]['refrence_id'] = $refrence_id;
                        $emailStats[$key]['status'] = $msg['Status'];
                        $emailStats[$key]['sent_on'] = date('Y-m-d H:i:s', time());
                    }
                }
                if (!empty($emailStats)) {
                    EmailStats::insert($emailStats);
                }
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // Send Email to Multiple User's
    public static function sendInBulkMail($toArr, $fromName, $messageArr, $subject, $pdffiles = array(), $userIds = array(), $type = NULL, $refrenceId = '')
    {
        try {

            // print_r($pdffiles);
            $fromEmail = env('MAILJET_FROM_ADDRESS', null);
            $apiKey = env('MAILJET_USERNAME', null);
            $secretKey = env('MAILJET_PASSWORD', null);
            $mj = new Client($apiKey, $secretKey, true, ['version' => 'v3.1']);
            $bodyObj = array();
            foreach ($toArr as $k => $v) {
                $attachment = array();
                $user_id = '';
                if (0 < count($pdffiles) && isset($pdffiles[$v])) {
                    $file = file_get_contents(Storage::disk('public')->path($pdffiles[$v]));
                    $attachment['ContentType'] = 'application/pdf';
                    $attachment['Filename'] = $pdffiles[$v];
                    $attachment['Base64Content'] = base64_encode($file);
                    Storage::disk('public')->delete($pdffiles[$v]);
                }
                $bodyObj[$k] = array(
                    'From' => [
                        'Email' => $fromEmail,
                        'Name' => $fromName
                    ],
                    'To' => [
                        [
                            'Email' => $v,
                            'Name' => "You"
                        ]
                    ],
                    'Subject' => $subject,
                    'TextPart' => $subject,
                    'HTMLPart' => $messageArr[$k],


                );
                if (isset($attachment) && !empty($attachment)) {
                    $bodyObj[$k]['Attachments'] = array($attachment);
                }

                //print_r($bodyObj); die;
                $programId = Helper::programId();
                $provider = Helper::providerId();
                $providerLocation = Helper::providerLocationId();
                $user = User::where("email", $v)->first();
                if (isset($user->id)) {
                    $userId = $user->id;
                } else {
                    $userId = 0;
                }
                $logArr = [
                    'udid' => Str::uuid()->toString(),
                    'providerId' => $provider,
                    'programId' => $programId,
                    'providerLocationId' => $providerLocation,
                    'userId' => $userId,
                    'subject' => $subject,
                    'from' => $fromEmail,
                    'to' => $v,
                    'body' => $messageArr[$k],
                    'type' => "sendEmail",
                    'ip' => request()->ip()
                ];

                MessageLog::create($logArr);
            }
            $body = array(
                'Messages' => $bodyObj
            );
            // All resources are located in the Resources class
            $response = $mj->post(Resources::$Email, ['body' => $body]);
            // Read the response
            if ($response->success()) {
                $result = $response->getData();
                self::SaveEmailStats($result, $userIds, $type, $refrenceId);
                $input = [
                    'udid' => Str::uuid()->toString(), 'table' => $subject,
                    'value' => json_encode($result), 'type' => "email", 'ip' => request()->ip()
                ];
                $changeLog = ChangeLog::create($input);
                $logArr = [
                    'udid' => Str::uuid()->toString(),
                    'providerId' => $provider,
                    'programId' => $programId,
                    'providerLocationId' => $providerLocation,
                    'subject' => $subject,
                    'from' => $fromEmail,
                    'type' => "sendEmail",
                    'ip' => request()->ip(),
                    "status" => "complete",
                    'response' => json_encode($result)
                ];

                MessageLog::create($logArr);
                return true;
            } else {

                $logArr = [
                    'udid' => Str::uuid()->toString(),
                    'providerId' => $provider,
                    'programId' => $programId,
                    'providerLocationId' => $providerLocation,
                    'subject' => $subject,
                    'from' => $fromEmail,
                    'type' => "sendEmail",
                    'ip' => request()->ip(),
                    "status" => "complete",
                    'response' => json_encode($response)
                ];

                MessageLog::create($logArr);
                return false;
            }
        } catch (Exception $e) {
            //  echo $e->getLine().$e->getFile();
            echo "Error Message:- " . $e->getMessage();
        }
    }

    // Get Message
    public static function getMessageBody($messageBody, $variablesArr)
    {
        $i = 0;
        foreach ($variablesArr as $key => $value) {
            $k[] = "{" . $key . "}";
            $v[] = $value;
            $i++;
        }

        $result = str_replace(
            $k,
            $v,
            $messageBody
        );
        return $result;
    }

    public static function patientInventry($id)
    {
        $data = PatientInventory::with('inventory')->where('id', $id)->first();
        return $data;
    }

    public static function getQrCode($url)
    {
        $ch = curl_init();


        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 0);                //0 for a get request

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $response = curl_exec($ch);
        return $response;
    }

    public static function getPagination($stats, $total, $perPage)
    {
        $paginator = new LengthAwarePaginator($stats, $total, $perPage);
        $paginator = $paginator->toArray();
        $response['pagination']['total'] = $paginator['data']['total'];
        $response['pagination']['count'] = $paginator['total'];
        $response['pagination']['per_page'] = $perPage;
        $response['pagination']['current_page'] = $paginator['data']['current_page'];
        $response['pagination']['total_pages'] = $paginator['data']['last_page'];
        $response['pagination']['links']['next'] = ($paginator['data']['next_page_url']) ? $paginator['data']['next_page_url'] : '';
        return $response;
    }

    public static function Globalnames($findkeys)
    {
        $GlobalNames = array();
        $codes = GlobalCode::whereIn('id', $findkeys)->get();
        foreach ($codes as $code) {
            $GlobalNames[$code->id] = $code->name;
        }
        return $GlobalNames;
    }

    public static function createSubTaskonJira($desc = NULL, $bugId = NULL, $description = NULL, $severity = NULL, $deviceType = NULL, $reportType = NULL)
    {

        try {
            $issueId = env('JIRA_ISSUE_ID', '');
            $jira_key = env('JIRA_KEY', '');
            $jira_email = env('JIRA_EMAIL', '');
            $project = env('JIRA_PROJECT', '');
            $jiraName = env('JIRA_NAME', '');
            if ($reportType && strtolower($reportType) == 'feedback') {
                if ($deviceType && strtolower($deviceType) == 'web') {
                    $taskId = env('JIRA_FEEDBACK_WEB', '');
                } elseif ($deviceType && strtolower($deviceType) == 'mobile') {
                    $taskId = env('JIRA_FEEDBACK_MOB', '');
                }
            } elseif ($reportType && strtolower($reportType) == 'bug') {
                if ($deviceType && strtolower($deviceType) == 'web') {
                    $taskId = env('JIRA_BUG_WEB', '');
                } elseif ($deviceType && strtolower($deviceType) == 'mobile') {
                    $taskId = env('JIRA_BUG_MOB', '');
                }
            }
            $preority = array('Highest' => 1, 'High' => 2, 'Medium' => 3, 'Low' => 4, 'Lowest' => 5);
            $data["fields"]["summary"] = $desc;
            $data["fields"]["project"]["key"] = $project;
            $data["fields"]["issuetype"]['id'] = "$issueId";
            if (isset($taskId) && !empty($taskId)) {
                $data["fields"]["parent"]["key"] = $taskId;
            }
            if ($description) {
                $data["fields"]["description"]['type'] = 'doc';
                $data["fields"]["description"]['version'] = 1;
                $paragarphs = explode('@@', $description);
                foreach ($paragarphs as $key => $para) {
                    $data["fields"]["description"]['content'][$key]['type'] = 'paragraph';
                    $data["fields"]["description"]['content'][$key]['content'][0]['text'] = $para;
                    $data["fields"]["description"]['content'][$key]['content'][0]['type'] = 'text';
                }
            }
            if (isset($preority[$severity])) {
                $data["fields"]["priority"]['id'] = "$preority[$severity]";
            }
            // print_r(json_encode($data));   die;
            $SubtaskId = '';
            if (!empty($jira_key) && !empty($jira_email) && !empty($jiraName)) { // Create task on Jira
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json", "Content-Type: application/json"));
                curl_setopt($ch, CURLOPT_URL, "https://$jiraName.atlassian.net/rest/api/3/issue");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_USERPWD, "$jira_email:$jira_key");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_POST, 1);
                $response = curl_exec($ch);
                $response = json_decode($response);
                curl_close($ch);
                //print_r($response);
                if (isset($response->key)) {
                    $SubtaskId = $response->key;
                }
            }
            if ($bugId && $SubtaskId) { // Upload Attachment to task
                $docs = BugReportDocument::where('bugReportId', $bugId)->get();
                if ($docs->count() > 0) {
                    foreach ($docs as $doc) {
                        if (Storage::disk('s3')->has($doc->filePath)) {
                            $file = Storage::disk('s3')->get($doc->filePath);
                            $getMimeType = Storage::disk('s3')->getMimetype($doc->filePath);
                            $extension = pathinfo(storage_path($doc->filePath), PATHINFO_EXTENSION);
                            $newFileName = time() . '.' . $extension;
                            $BOUNDARY = md5(time());
                            $data = "--" . $BOUNDARY . "\r\n";
                            $data .= "Content-Disposition: form-data; name=\"file\"; filename=\"" . basename($newFileName) . "\"\r\n";
                            $data .= "Content-Type: $getMimeType \r\n";
                            $data .= "\r\n";
                            $data .= $file . "\r\n";
                            $data .= "--" . $BOUNDARY . "--";
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Atlassian-Token: no-check", "Content-Type: multipart/form-data;boundary=$BOUNDARY"));
                            curl_setopt($ch, CURLOPT_URL, "https://$jiraName.atlassian.net/rest/api/3/issue/$SubtaskId/attachments");
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_USERPWD, "$jira_email:$jira_key");
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            $result = curl_exec($ch);
                            //print_r($result);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            //  echo $e->getLine().$e->getFile();
            echo "Error Message:- " . $e->getMessage();
        }
    }

    public static function assignCustomForm($formUdId, $customerId)
    {

        $CustomFormService = new CustomFormService;
        return $CustomFormService->assignWorkflowForm($formUdId, $customerId);
    }

    public static function sendTemplate()
    {

        $fromEmail = env('MAILJET_FROM_ADDRESS', null);
        $apiKey = env('MAILJET_USERNAME', null);
        $secretKey = env('MAILJET_PASSWORD', null);

        $mj = new Client($apiKey, $secretKey, true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [[
                'From' => [
                    'Email' => $fromEmail,
                    'Name' => "Virtare Health"
                ],
                'To' => [
                    [
                        'Email' => "sanjeev.saini@ditstek.com",
                        'Name' => "Sanjiv"
                    ]
                ],
                'TemplateID' => 4548989,
                'TemplateLanguage' => true,
                //  'Subject' => "Welcome to Virtare Health Care",
                'Variables' => json_decode('{
                    "name": "SANJIV",
                    "url": "google.com"
                    }', true)
            ]]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success() && var_dump($response->getData());
    }

    public static function sendMailTemplate($template = null, $variable = NULL, $userDetail)
    {

        $templtes['forgot_password'] = 4558639;
        $templtes['welcome_email'] = 4548989;
        $template_id = isset($templtes[$template]) ? $templtes[$template] : '';
        if (!empty($template_id)) {
            $fromEmail = env('MAILJET_FROM_ADDRESS', null);
            $apiKey = env('MAILJET_USERNAME', null);
            $secretKey = env('MAILJET_PASSWORD', null);
            //echo $apiKey . '+++++' . $secretKey;
            $mj = new Client($apiKey, $secretKey, true, ['version' => 'v3.1']);
            $body = [
                'Messages' => [[
                    'From' => [
                        'Email' => $fromEmail,
                        'Name' => "Virtare Health"
                    ],
                    'To' => [
                        [
                            'Email' => $userDetail['email'],
                            'Name' => $userDetail['name']
                        ]
                    ],
                    'TemplateID' => $template_id,
                    'TemplateLanguage' => true,
                    'Variables' => json_decode($variable, true)
                ]]
            ];
            return $mj->post(Resources::$Email, ['body' => $body]);
        }
    }

    public static function validateNumber($sendTo)
    {
        $from = env('bandwidth_from_no', null);
        $country_code = env('bandwidth_country_code', null);
        $to = $country_code . $sendTo;
        $applicationId = env('bandwidth_app_id', null);
        $BW_ACCOUNT_ID = env('bandwidth_account_id', null);
        $BW_basicAuthUserName = env('bandwidth_basicAuthUserName', null);
        $BW_basicAuthPassword = env('bandwidth_basicAuthPassword', null);

        $config = new Configuration(
            array(
                'messagingBasicAuthUserName' => $BW_basicAuthUserName,
                'messagingBasicAuthPassword' => $BW_basicAuthPassword,
            )
        );

        $client = new BandwidthClient($config);
        $messagingClient = $client->getPhoneNumberLookup()->getClient();
        $body = new OrderRequest();
        $body->tns = array("+15554443333");

        try {
            $response = $messagingClient->createLookupRequest($BW_ACCOUNT_ID, $body);
            //return $response->getResult();
        } catch (APIException $e) {
            return $e->getMessage();
        }
    }

    public static function randomString($strLength)
    {
        // String of all alphanumeric character
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        // Shufle the $str_result and returns substring of specified length
        return substr(str_shuffle($str_result), 0, $strLength);
    }
}
