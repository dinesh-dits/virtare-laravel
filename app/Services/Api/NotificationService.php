<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Carbon\Carbon;
use App\Models\Staff\Staff;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\Patient\Patient;
use App\Models\Setting\Setting;
use Illuminate\Support\Facades\DB;
use App\Models\Patient\PatientFlag;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment\Appointment;
use App\Models\Communication\CallRecord;
use App\Models\Patient\PatientInventory;
use App\Models\Notification\Notification;
use App\Models\Communication\Communication;
use App\Models\ConfigMessage\ConfigMessage;
use App\Models\NonCompliance\NonCompliance;
use App\Services\Api\PushNotificationService;
use App\Models\Appointment\AppointmentNotification;
use App\Models\Communication\CommunicationCallRecord;
use App\Models\Conversation\ConversationMessage;
use App\Models\Patient\PatientProgram;
use App\Models\Patient\PatientStaff;
use App\Transformers\Notification\NotificationTransformer;
use App\Events\NotificationEvent;

class NotificationService
{
    // Appointment Notification
    public function appointmentNotification()
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $appointments = DB::select(
                'CALL appointmentListNotification("' . date("Y-m-d H:i:s", time()) . '","' . date("Y-m-d H:i:s", strtotime('+30 minutes')) . '")',
            );
            if (!empty($appointments)) {
                foreach ($appointments as $appointment) {
                    if ($appointment->statusId == 155) {
                        $to_time = strtotime($appointment->startTime);
                        $from_time = time();
                        $minutes = (int)round(abs($to_time - $from_time) / 60, 0);
                        $patient = Patient::where('id', $appointment->patientId)->first();
                        $userId = $patient->userId;
                        $notificationData = [
                            'body' => 'You have a appointment in ' . $minutes . ' minutes.',
                            'title' => 'Appointment Reminder',
                            'userId' => $appointment->patientUserId,
                            'isSent' => 0,
                            'entity' => 'Confrence',
                            'referenceId' => 'CONF' . $appointment->id,
                            'createdBy' => $appointment->staffUserId,
                            'providerId' => $provider,
                            'providerLocationId' => $providerLocation
                        ];
                        $notification = Notification::create($notificationData);

                        event(new NotificationEvent($notification));
                        $notificationUpdate = ['isSent' => '1', 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'updatedBy' => Auth::id()];
                        Notification::where('id', $notification->id)->update($notificationUpdate);

                        $changeLog = [
                            'udid' => Str::uuid()->toString(), 'table' => 'notifications', 'tableId' => $notification->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                            'value' => json_encode($notificationData), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                        ];
                        ChangeLog::create($changeLog);
                        $appointmentNotification = [
                            'udid' => Str::random(10),
                            'appointmentId' => $appointment->id,
                            'lastNotification' => 1,
                            'createdBy' => $appointment->staffUserId,
                            'providerId' => $provider,
                            'providerLocationId' => $providerLocation
                        ];
                        $appointmentData = AppointmentNotification::create($appointmentNotification);
                        $changeLog = [
                            'udid' => Str::uuid()->toString(), 'table' => 'appointmentNotification', 'tableId' => $appointmentData->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                            'value' => json_encode($appointmentNotification), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                        ];
                        ChangeLog::create($changeLog);
                    }
                }
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Sent Appointment Notification
    public function appointmentNotificationSend()
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $notifications = DB::select(
                'CALL notificationList("0","")',
            );
            if (!empty($notifications)) {
                foreach ($notifications as $notification) {
                    $data = ['isSent' => '1', 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                    Notification::where('id', $notification->id)->update($data);
                    $pushnotification = new PushNotificationService();
                    $notificationData = array(
                        "body" => $notification->body,
                        "title" => $notification->title,
                        "type" => $notification->entity,
                        "typeId" => $notification->referenceId,
                    );
                    $pushnotification->sendNotification([$notification->userId], $notificationData);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'notifications', 'tableId' => $notification->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($data), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                }
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Conference Appointment
    public function appointmentConfrence()
    {
        try {
            if (request()->header('providerId')) {
                $provider = Helper::providerId();
            } else {
                $provider = '';
            }
            if (request()->header('providerLocationId')) {
                $providerLocation = Helper::providerLocationId();
            } else {
                $providerLocation = '';
            }
            $statusId = '';
            $staffIdx = '';
            $toDate = Helper::date(strtotime('+5 minutes'));
            $fromDate = Helper::date(time());
            $appointments = DB::select(
                "CALL appointmentList('" . $fromDate . "','" . $toDate . "','" . $staffIdx . "','" . $statusId . "','" . $provider . "','" . $providerLocation . "')",
            );
            if (!empty($appointments)) {
                foreach ($appointments as $appointment) {
                    if ((empty($appointment->conferenceId) || is_null($appointment->conferenceId)) && $appointment->statusId == 155) {
                        $staffId = Helper::entity('staff', $appointment->staff_id);
                        $patentId = Helper::entity('patient', $appointment->patient_id);
                        $patient = Patient::where('id', $patentId)->first();
                        $userId = $patient->userId;
                        $staff = Staff::where('id', $staffId)->first();
                        $staffUserId = $staff->userId;
                        $notificationData = [
                            'body' => 'Your appointment going to start please join.',
                            'title' => 'Appointment Reminder',
                            'userId' => $userId,
                            'isSent' => 0,
                            'entity' => 'Confrence',
                            'referenceId' => 'CONF' . $appointment->id,
                            'createdBy' => $staffUserId,
                            'providerId' => $provider,
                            'providerLocationId' => $providerLocation
                        ];
                        $notification = Notification::create($notificationData);

                        event(new NotificationEvent($notification));
                        $notificationUpdate = ['isSent' => '1', 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'updatedBy' => Auth::id()];
                        Notification::where('id', $notification->id)->update($notificationUpdate);

                        if ($notification) {
                            $changeLog = [
                                'udid' => Str::uuid()->toString(), 'table' => 'notifications', 'tableId' => $notification->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                                'value' => json_encode($notificationData), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                            ];
                            ChangeLog::create($changeLog);
                        }
                        $appointmentData = ['conferenceId' => 'CONF' . $appointment->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                        Appointment::where('id', $appointment->id)->update($appointmentData);
                        // $changeLog = [
                        //     'udid' => Str::uuid()->toString(), 'table' => 'appointments', 'tableId' => $appointment->id,
                        //     'value' => json_encode($appointmentData), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                        // ];
                        // ChangeLog::create($changeLog);

                        $commData = [
                            'from' => $staff->userId,
                            'referenceId' => $patient->userId,
                            'messageTypeId' => 104,
                            'subject' => 'App Call',
                            'priorityId' => 72,
                            'messageCategoryId' => 40,
                            'createdBy' => $staff->userId,
                            'entityType' => 'appCall',
                            'udid' => Str::uuid()->toString(),
                            'providerId' => $provider,
                            'providerLocationId' => $providerLocation
                        ];
                        $dataComm = Communication::create($commData);
                        if ($dataComm) {
                            $changeLog = [
                                'udid' => Str::uuid()->toString(), 'table' => 'communications', 'tableId' => $dataComm->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                                'value' => json_encode($commData), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                            ];
                            ChangeLog::create($changeLog);
                        }
                        $input = [
                            'patientId' => $patient->id,
                            'callStatusId' => 47,
                            'udid' => Str::uuid()->toString(),
                            'referenceId' => 'CONF' . $appointment->id,
                            'entityType' => 'conferenceCall',
                            'communicationId' => 'conferenceCall',
                            'providerId' => $provider,
                            'providerLocationId' => $providerLocation
                        ];
                        $comm = CommunicationCallRecord::create($input);
                        if ($comm) {
                            $changeLog = [
                                'udid' => Str::uuid()->toString(), 'table' => 'communicationCallRecords', 'tableId' => $comm->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                                'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                            ];
                            ChangeLog::create($changeLog);
                        }
                        $call = ['udid' => Str::uuid()->toString(), 'createdBy' => $patient->userId, 'communicationCallRecordId' => $comm->id, 'staffId' => $staffId, 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                        $callRecord = CallRecord::create($call);
                        if ($callRecord) {
                            $changeLog = [
                                'udid' => Str::uuid()->toString(), 'table' => 'callRecords', 'tableId' => $callRecord->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                                'value' => json_encode($call), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                            ];
                            ChangeLog::create($changeLog);
                        }
                        /*$callTime=['udid' => Str::uuid()->toString(), 'createdBy' => $patient->userId,'callRecordId'=>$callRecord->id];
                        $timeCall=CallRecordTime::create($callTime);*/
                        // $changeLog = [
                        //     'udid' => Str::uuid()->toString(), 'table' => 'callRecordTimes', 'tableId' => $timeCall->id,
                        //     'value' => json_encode($callTime), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                        // ];
                        // ChangeLog::create($changeLog);
                    }
                }
            }
            $confrence = Appointment::whereNotNull('conferenceId')->get();
            // Helper::updateFreeswitchConfrence($confrence);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Appointment Conference
    public function appointmentConfrenceIdUpdate()
    {
        try {
            $fromDate = date('Y-m-d H:i:s', strtotime('-1 hours'));
            DB::select(
                'CALL appointmentConferenceIdUpdate()',
            );
            DB::statement("UPDATE `communicationCallRecords` SET `callStatusId`='49' WHERE `referenceId` IN ( SELECT concat('CONF',id) FROM appointments where conferenceId IS NULL) AND `entityType` = 'conferenceCall'");
            DB::statement("UPDATE `notifications` SET `isDelete`='1',`isSent`='1' , `deletedAt`=now() WHERE `referenceId` IN ( SELECT concat('CONF',id) FROM appointments where conferenceId IS NULL) AND `entity` = 'Confrence'");
            DB::statement("UPDATE `guests` SET `isDelete`='1', `deletedAt`=now() WHERE `conferenceId` IN ( SELECT concat('CONF',id) FROM appointments where conferenceId IS NULL)");
            return Helper::updateFreeswitchUser();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Remove New Patient Flag
    public function removeNewPatientFlag()
    {
        try {
            PatientFlag::whereHas('patient', function ($query) {
                $query->where('createdAt', '>=', Carbon::now()->subDay());
            })->delete();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update isRead Notification
    public function isReadUpdate($request, $id)
    {
        try {
            if ($id) {
                $idx = $id;
            } else {
                $idx = Auth()->user()->id;
            }
            $data = DB::select(
                'CALL notificationIsReadUpdate("' . $idx . '")',
            );

            // $changeLog = [
            //     'udid' => Str::uuid()->toString(), 'table' => 'nitifications', 'tableId' => $timeCall->id,
            //     'value' => json_encode($callTime), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            // ];
            // ChangeLog::create($changeLog);

            return response()->json(['message' => trans('messages.updatedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List isRead Notification
    public function isReadList($request)
    {
        try {
            $userId = Auth::id();
            $data = DB::select(
                'CALL notificationIsReadList("' . $userId . '")',
            );
            return fractal()->collection($data)->transformWith(new NotificationTransformer(false))->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Non Compliance Patients
    public function nonCompliance()
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $inventory = PatientInventory::select('patientInventories.*')->leftJoin('inventories', 'inventories.id', '=', 'patientInventories.inventoryId')
                ->leftJoin('deviceModels', 'deviceModels.id', '=', 'inventories.deviceModelId')
                ->leftJoin('globalCodes', 'globalCodes.id', '=', 'deviceModels.deviceTypeId')->groupBy('deviceModels.deviceTypeId')->get();
            if (!empty($inventory)) {
                foreach ($inventory as $value) {

                    $date = Setting::where('key', 'nonComplianceTime')->first();

                    if (isset($date->value)) {
                        $fromDate = date("Y-m-d", strtotime($date->value));
                    } else {
                        $fromDate = "";
                    }

                    if (!empty($fromDate)) {
                        $patients = DB::select(
                            "CALL nonCompliance('" . $fromDate . "','" . $value->deviceTypeId . "')"
                        );
                    } else {
                        $patients = "";
                    }

                    if (!empty($patients)) {
                        foreach ($patients as $patient) {
                            $patientId = Patient::where([['id', $patient->id], ['nonCompliance', 1]])->first();
                            if ($patientId == true) {
                                $patientInventory = PatientInventory::where('patientId', $patientId->id)->first();
                                if ($patientInventory) {
                                    $nonCompliance = ['patientId' => $patientId->id, 'createdBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'udid' => Str::uuid()->toString(), 'patientInventoryId' => $patientInventory->id];
                                    NonCompliance::create($nonCompliance);
                                    $compliance = ['nonCompliance' => 0, 'updatedBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                                    Patient::where('id', $patient->id)->update($compliance);
                                    $patientData = Patient::where('id', $patient->id)->first();
                                    $changeLog = [
                                        'udid' => Str::uuid()->toString(), 'table' => 'patients', 'tableId' => $patientData->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                                        'value' => json_encode($compliance), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                                    ];
                                    ChangeLog::create($changeLog);

                                    // email body message
                                    $msgObj = ConfigMessage::where("type", "nonCompliance")
                                        ->where("entityType", "sendMail")
                                        ->first();
                                    // email header
                                    $msgHeaderObj = ConfigMessage::where("type", "header")
                                        ->where("entityType", "sendMail")
                                        ->first();
                                    // email footer
                                    $msgFooterObj = ConfigMessage::where("type", "footer")
                                        ->where("entityType", "sendMail")
                                        ->first();
                                    $messageObj = array();
                                    $userIds = array();
                                    $fullName = "";

                                    $userEmailDefined = 0;
                                    $patient = Patient::with("user")->where('id', $patientId->id)->first();
                                    if (isset($patient->user->userDefined) && $patient->user->userDefined == 1) {
                                        $userEmailDefined = 1;
                                    }
                                    $userEmail = $patient->user->email;
                                    $fullName = $patient->firstName . " " . $patient->lastName;
                                    $userIds[$userEmail] = $patient->user->id;
                                    $variablesArr = array(
                                        "fullName" => $fullName,
                                        "vitalName" => $value->inventory->model->deviceType->name
                                    );

                                    if (isset($msgObj->messageBody)) {
                                        $messageBody = $msgObj->messageBody;
                                        if (isset($msgHeaderObj->messageBody) && !empty($msgHeaderObj->messageBody)) {
                                            $messageBody = $msgHeaderObj->messageBody . $messageBody;
                                        }
                                        if (isset($msgFooterObj->messageBody) && !empty($msgFooterObj->messageBody)) {
                                            $messageBody = $messageBody . $msgFooterObj->messageBody;
                                        }
                                        $messageObj = Helper::getMessageBody($messageBody, $variablesArr);
                                    }
                                    if (isset($userEmail) && !empty($userEmail) && $userEmailDefined == 1) {
                                        $to = $userEmail;
                                        if (isset($msgObj->otherParameter)) {
                                            $otherParameter = json_decode($msgObj->otherParameter);
                                            if (isset($otherParameter->fromName)) {
                                                $fromName = $otherParameter->fromName;
                                            } else {
                                                $fromName = "Virtare Health";
                                            }
                                        } else {
                                            $fromName = "Virtare Health";
                                        }
                                        $subject = 'Non Compliance';
                                        Helper::commonMailjet($to, $fromName, $messageObj, $subject, '', $userIds, 'Non Complience', '');

                                        $notificationData = [
                                            'body' => 'You have Non Compliance for' . ' ' . $value->inventory->model->deviceType->name . ' ' . 'Please Upload Reading As Soon As Possible.',
                                            'title' => 'Non Compliance Reminder',
                                            'userId' => $patientData->userId,
                                            'isSent' => 0,
                                            'entity' => 'nonCompliance',
                                            'referenceId' => 'CONF' . $value->deviceTypeId,
                                            'createdBy' => Auth::id(),
                                            'providerId' => $provider,
                                            'providerLocationId' => $providerLocation
                                        ];
                                        $notification = Notification::create($notificationData);

                                        event(new NotificationEvent($notification));
                                        $notificationUpdate = ['isSent' => '1', 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'updatedBy' => Auth::id()];
                                        Notification::where('id', $notification->id)->update($notificationUpdate);

                                        if ($notification) {
                                            $changeLog = [
                                                'udid' => Str::uuid()->toString(), 'table' => 'notifications', 'tableId' => $notification->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                                                'value' => json_encode($notificationData), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                                            ];
                                            ChangeLog::create($changeLog);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    //Patient Program Renew Reminder
    public function patientProgramReminder()
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            //$data = PatientProgram::whereBetween('renewalDate', [Carbon::now()->subDays(10)->toDateTimeString(),Carbon::now()] );
            $data = PatientProgram::whereDate('renewalDate', '<', Carbon::now()->addDay(10)->toDateTimeString());
            $data = $data->get();
            foreach ($data as $date) {
                $renewlDate = $date->renewalDate;
                if (!is_null($renewlDate)) {
                    $to = Carbon::parse($renewlDate);
                    $from = Carbon::now();
                    $days = $from->diffInDays($to, false);
                    if ($days > 0) {
                        $days = $days + 1;
                    }
                    if ($days == 10 || $days < 10) {
                        $patient = Patient::where('id', $date->patientId)->first();
                        $primaryCare = PatientStaff::where([['patientId', $patient->id], ['isPrimary', 1]])->first();
                        if (!is_null($primaryCare)) {
                            $staff = Staff::where('id', $primaryCare->staffId)->first();
                            if ($days > 0) {
                                $message = ucfirst($patient->lastName) . ' ' . ucfirst($patient->firstName) . ' ' . "Program will expire within $days days please renew ";
                            } elseif ($days == 0) {
                                $message = ucfirst($patient->lastName) . ' ' . ucfirst($patient->firstName) . ' ' . "Program will expire today please renew ";
                            } else {
                                $message = ucfirst($patient->lastName) . ' ' . ucfirst($patient->firstName) . ' ' . "Program has expired please renew ";
                            }
                            $notificationData = [
                                'body' => $message,
                                'title' => 'Program Renewal',
                                'userId' => $staff->userId,
                                'isSent' => 0,
                                'entity' => 'Program',
                                'referenceId' => Auth::id(),
                                'createdBy' => Auth::id(),
                                'providerId' => $provider,
                                'providerLocationId' => $providerLocation
                            ];
                            $notification = Notification::create($notificationData);
                            
                            event(new NotificationEvent($notification));
                            $notificationUpdate = ['isSent' => '1', 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'updatedBy' => Auth::id()];
                            Notification::where('id', $notification->id)->update($notificationUpdate);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    //Conversation message read reminder
    public function messageReadReminder()
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $messages = ConversationMessage::where([['createdAt', '<', Carbon::now()->addMinutes(10)->toDateTimeString()], ['isRead', 0]])->get();
            foreach ($messages as $message) {
                $refrenceId = $message->senderId;
                $communicationId = $message->communicationId;
                $commData = Communication::where([['id', $communicationId], ['referenceId', $refrenceId]])->first();
                if (!is_null($commData)) {
                    $userId = $commData->from;
                } else {
                    $commData = Communication::where([['id', $communicationId], ['from', $refrenceId]])->first();
                    if (!is_null($commData)) {
                        $userId = $commData->referenceId;
                    }
                }
                $data = Patient::where('userId', $refrenceId)->first();
                if (!is_null($data)) {
                    $name = ucfirst($data->lastName) . ' ' . ucfirst($data->firstName);
                } else {
                    $data = Staff::where('userId', $refrenceId)->first();
                    $name = ucfirst($data->lastName) . ' ' . ucfirst($data->firstName);
                }
                if ($userId) {
                    $notificationData = [
                        'body' => "You have received message from $name",
                        'title' => "Conversation Message",
                        'userId' => $userId,
                        'isSent' => 0,
                        'entity' => 'Message',
                        'referenceId' => $refrenceId,
                        'createdBy' => Auth::id(),
                        'providerId' => $provider,
                        'providerLocationId' => $providerLocation
                    ];
                    $notification = Notification::create($notificationData);

                    event(new NotificationEvent($notification));
                    $notificationUpdate = ['isSent' => '1', 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'updatedBy' => Auth::id()];
                    Notification::where('id', $notification->id)->update($notificationUpdate);
                }

            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    //Patient Program Discharge date update
    public function patientProgramDischargeDateUpdate()
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $input = [
                'updatedBy' => Auth::id(),
                'isActive' => 0,
            ];
            $data = PatientProgram::whereDate('dischargeDate', '<', Carbon::now()->toDateTimeString())->where('isActive',1);
            $data = $data->update($input);

        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
