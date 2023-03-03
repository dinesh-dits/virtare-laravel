<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Carbon\Carbon;
use App\Models\User\User;
use App\Models\Staff\Staff;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\CPTCode\CPTCode;
use App\Models\Patient\Patient;
use App\Events\NotificationEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment\Appointment;
use App\Models\Patient\PatientTimeLine;
use App\Models\Communication\CallRecord;
use App\Models\Notification\Notification;
use App\Models\TimeApproval\TimeApproval;
use App\Models\Communication\Communication;
use App\Models\ConfigMessage\ConfigMessage;
use App\Models\Communication\CallRecordTime;
use App\Transformers\Email\EmailTransformer;
use App\Services\Api\PushNotificationService;
use App\Models\Conversation\ConversationMessage;
use App\Models\Communication\CommunicationInbound;
use App\Models\Communication\CommunicationMessage;
use App\Models\CPTCode\CptCodeNextBillingServices;
use App\Models\Communication\CommunicationCallRecord;
use App\Transformers\Patient\PatientCountTransformer;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Transformers\Communication\MessageTypeTransformer;
use App\Transformers\Communication\CommunicationTransformer;
use App\Transformers\Communication\CommunicationCallTransformer;
use App\Transformers\Appointment\AppoinmentCallStatusTransformer;
use App\Transformers\Communication\CommunicationCountTransformer;
use App\Transformers\Communication\CommunicationSearchTransformer;
use App\Transformers\Communication\CommunicationInboundTransformer;

class CommunicationService
{
    //  Add Communication
    public function addCommunication($request)
    {
        try {
            $providerId = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityTypeLocation = Helper::entityType();
            $refrenceData = Staff::where('udid', $request->referenceId)->first();
            if (!is_null($refrenceData)) {
                $newReference = $refrenceData;
                $entityType = "staff";
            } else {
                $entityType = "patient";
                $reference = Helper::entity($entityType, $request->referenceId);
                $newReference = Patient::where('id', $reference)->first();
            }
            $staffFrom = Staff::where('udid', $request->from)->first();
            if (!is_null($staffFrom)) {
                $staffFromId = $staffFrom->userId;
            } else {
                $patientFrom = Patient::where('udid', $request->from)->first();
                $staffFromId = $patientFrom->userId;
            }
            $newReferenceId = $newReference->userId;
            $input = [
                'from' => $staffFromId,
                'referenceId' => $newReferenceId,
                'messageTypeId' => $request->messageTypeId,
                'subject' => $request->subject,
                'priorityId' => $request->priorityId,
                'messageCategoryId' => $request->messageCategoryId,
                'createdBy' => Auth::id(),
                'entityType' => $entityType,
                'udid' => Str::uuid()->toString(),
                'providerId' => $providerId,
                'providerLocationId' => $providerLocation,
                'locationEntityType' => $entityTypeLocation,
            ];
            if ($request->messageTypeId == '102') {
                $exdata = DB::table('communications')->where([['from', '=', $staffFromId], ['referenceId', '=', $newReferenceId], ['messageTypeId', '=', 102]])->orWhere(function ($query) use ($staffFromId, $newReferenceId) {
                    $query->where([['from', '=', $newReferenceId], ['referenceId', $staffFromId]])->where('messageTypeId', '=', 102);
                })->exists();
                if ($exdata == false) {
                    $data = Communication::create($input);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'communications', 'tableId' => $data->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityTypeLocation
                    ];
                    ChangeLog::create($changeLog);
                    if ($data->messageTypeId == 102) {
                        $conversation = [
                            'communicationId' => $data->id,
                            'message' => $request->message,
                            'senderId' => $staffFromId,
                            'createdBy' => $data->createdBy,
                            'type' => 'text',
                            'udid' => Str::uuid()->toString(),
                            'isRead' => 0,
                            'providerId' => $providerId,
                            'providerLocationId' => $providerLocation,
                            'entityType' => $entityTypeLocation
                        ];
                        $conversationData = ConversationMessage::create($conversation);
                        $communicationId = Communication::where('communications.id', $data->id)
                            ->selectRaw('communications.*,if(p1.firstName IS NULL, CONCAT(s1.lastName,","," ",s1.firstName," ",s1.middleName)
                            , CONCAT(p1.lastName,","," ",p1.firstName," ",p1.middleName)) as fromName
                            ,if(p2.firstName IS NULL, CONCAT(s1.lastName,","," ",s1.firstName," ",s1.middleName)
                            , CONCAT(p1.lastName,","," ",p1.firstName," ",p1.middleName)) as toName
                            ,if(p1.id IS NULL, p2.id , p1.id) as patientId
                            ,if(u1.id IS NULL, u2.id , u1.id) as udid')
                            ->join('users as u1', 'u1.id', '=', 'communications.from')
                            ->join('staffs as s1', 's1.userId', '=', 'u1.id', 'LEFT')
                            ->join('patients as p1', 'p1.userId', '=', 'u1.id', 'LEFT')
                            ->join('users as u2', 'u2.id', '=', 'communications.referenceId')
                            ->join('staffs as s2', 's2.userId', '=', 'u2.id', 'LEFT')
                            ->join('patients as p2', 'p2.userId', '=', 'u2.id', 'LEFT')->first();
                        if (@$communicationId->patientId) {
                            if (@$communicationId->udid == auth()->user()->id) {
                                $timeLine = [
                                    'patientId' => @$communicationId->patientId, 'heading' => 'Message Received', 'title' => $request->message . ' ' . '<b>From' . ' ' . $communicationId->fromName . ' ' . '</b>', 'type' => 9,
                                    'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityTypeLocation
                                ];
                            } else {
                                $timeLine = [
                                    'patientId' => @$communicationId->patientId, 'heading' => 'Message Sent', 'title' => $request->message . ' ' . '<b>to' . ' ' . $communicationId->fromName . ' ' . '</b>', 'type' => 9,
                                    'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityTypeLocation
                                ];
                            }
                            PatientTimeLine::create($timeLine);
                        }
                        $changeLog = [
                            'udid' => Str::uuid()->toString(), 'table' => 'messages', 'tableId' => $conversationData->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation,
                            'value' => json_encode($conversation), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityTypeLocation
                        ];
                        ChangeLog::create($changeLog);
                    } else {
                        $communicationInput = [
                            'communicationId' => $data->id,
                            'message' => $request->message,
                            'createdBy' => $data->createdBy,
                            'udid' => Str::uuid()->toString(),
                            'providerId' => $providerId,
                            'providerLocationId' => $providerLocation,
                            'entityType' => $entityTypeLocation
                        ];
                        $comm = CommunicationMessage::create($communicationInput);
                        $changeLog = [
                            'udid' => Str::uuid()->toString(), 'table' => 'communicationMessages', 'tableId' => $comm->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation,
                            'value' => json_encode($communicationInput), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityTypeLocation
                        ];
                        ChangeLog::create($changeLog);
                    }
                    $pushnotification = new PushNotificationService();
                    $notificationData = array(
                        "body" => "You have received new message from" . ' ' . ucfirst($staffFrom->lastName) . ' ' . ucfirst($staffFrom->firstName),
                        "title" => "New message",
                        "type" => "Communication",
                        "typeId" => $data->id,
                    );
                    $pushnotification->sendNotification([$newReferenceId], $notificationData);
                    $userdata = fractal()->item($data)->transformWith(new CommunicationTransformer())->toArray();
                    $message = ['message' => trans('messages.createdSuccesfully')];
                    DB::commit();
                    $endData = array_merge($message, $userdata);
                    return $endData;
                } elseif ($exdata == true) {
                    $data = Communication::where([['from', '=', $staffFromId], ['referenceId', '=', $newReferenceId], ['messageTypeId', '=', 102]])->orWhere(function ($query) use ($staffFromId, $newReferenceId) {
                        $query->where([['from', '=', $newReferenceId], ['referenceId', $staffFromId]])->where('messageTypeId', '=', 102);
                    })->first();
                    $commInput = ['updatedBy' => Auth::id()];
                    Communication::where('id', $data->id)->update($commInput);
                    $conversation = [
                        'communicationId' => $data->id,
                        'message' => $request->message,
                        'senderId' => $staffFromId,
                        'createdBy' => $data->createdBy,
                        'type' => 'text',
                        'udid' => Str::uuid()->toString(),
                        'isRead' => 0,
                        'providerId' => $providerId,
                        'providerLocationId' => $providerLocation,
                        'entityType' => $entityTypeLocation,
                    ];
                    $conversationData = ConversationMessage::create($conversation);
                    $communicationId = Communication::where('communications.id', $data->id)->selectRaw('communications.*,if(p1.firstName IS NULL, s1.firstName , p1.firstName) as fromName
                    ,if(p2.firstName IS NULL, s2.firstName , p2.firstName) as toName
                    ,if(p1.id IS NULL, p2.id , p1.id) as patientId
                    ,if(u1.id IS NULL, u2.id , u1.id) as udid')
                        ->join('users as u1', 'u1.id', '=', 'communications.from')
                        ->join('staffs as s1', 's1.userId', '=', 'u1.id', 'LEFT')
                        ->join('patients as p1', 'p1.userId', '=', 'u1.id', 'LEFT')
                        ->join('users as u2', 'u2.id', '=', 'communications.referenceId')
                        ->join('staffs as s2', 's2.userId', '=', 'u2.id', 'LEFT')
                        ->join('patients as p2', 'p2.userId', '=', 'u2.id', 'LEFT')->first();
                    if ($communicationId->patientId) {
                        $providerId = Helper::providerId();
                        $providerLocation = Helper::providerLocationId();
                        if ($communicationId->udid == auth()->user()->id) {
                            $timeLine = [
                                'patientId' => $communicationId->patientId, 'heading' => 'Message Received', 'title' => $request->message . ' ' . '<b>From' . ' ' . $communicationId->fromName . ' ' . '</b>', 'type' => 9,
                                'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityTypeLocation
                            ];
                        } else {
                            $timeLine = [
                                'patientId' => $communicationId->patientId, 'heading' => 'Message Sent', 'title' => $request->message . ' ' . '<b>to' . ' ' . $communicationId->fromName . ' ' . '</b>', 'type' => 9,
                                'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityTypeLocation
                            ];
                        }
                        PatientTimeLine::create($timeLine);
                    }
                    $pushnotification = new PushNotificationService();
                    $notificationData = array(
                        "body" => "You have received new message from" . ' ' . ucfirst($staffFrom->lastName) . ' ' . ucfirst($staffFrom->firstName),
                        "title" => "New message",
                        "type" => "Communication",
                        "typeId" => $data->id,
                    );
                    $pushnotification->sendNotification([$newReferenceId], $notificationData);
                    $userdata = fractal()->item($data)->transformWith(new CommunicationTransformer())->toArray();
                    $message = ['message' => trans('messages.createdSuccesfully')];
                    DB::commit();
                    $endData = array_merge($message, $userdata);
                    return $endData;
                } else {
                    return response()->json(['message' => trans('messages.unauthenticated')], 401);
                }
            } else {
                $data = Communication::create($input);
                //send sms
                if ($request->messageTypeId == '327') {
                    Helper::sendBandwidthMessage($request->message, $newReference->phoneNumber);
                }
                if ($entityType == 'staff' && $request->messageTypeId != '327') {

                    // email body message
                    $msgObj = ConfigMessage::where("type", "communicationAdd")
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
                    $fullName = "";
                    $userEmailDefined = 1;
                    $staff = Staff::with("user")->where('udid', $newReference->udid)->first();
                    $userEmail = $staff->user->email;
                    $fullName = $staff->firstName . " " . $staff->lastName;
                    $UserId = $staff->userId;
                    $variablesArr = array(
                        "communicationId" => $data->id,
                        "fullName" => $fullName,
                        "heading" => $request->subject,
                        "message" => $request->message,
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
                        $subject = $request->subject;
                        Helper::commonMailjet($to, $fromName, $messageObj, $subject);
                        $notificationData = [
                            'body' => 'New communication added.',
                            'title' => 'Communication Added',
                            'userId' => $UserId,
                            'isSent' => 0,
                            'entity' => 'Communication',
                            'referenceId' => $data->id,
                            'providerId' => $providerId,
                            'createdBy' => Auth::id(),

                        ];
                        $notification = Notification::create($notificationData);

                        event(new NotificationEvent($notification));
                        $notificationUpdate = ['isSent' => '1', 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'updatedBy' => Auth::id()];
                        Notification::where('id', $notification->id)->update($notificationUpdate);

                        $changeLog = [
                            'udid' => Str::uuid()->toString(), 'table' => 'notifications', 'tableId' => $notification->id, 'providerId' => $providerId,
                            'value' => json_encode($notificationData), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                        ];
                        ChangeLog::create($changeLog);
                    }
                }

                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'communications', 'tableId' => $data->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityTypeLocation
                ];
                ChangeLog::create($changeLog);
                $communicationMessageInput = [
                    'communicationId' => $data->id,
                    'message' => $request->message,
                    'createdBy' => $data->createdBy,
                    'udid' => Str::uuid()->toString(),
                    'providerId' => $providerId,
                    'providerLocationId' => $providerLocation,
                    'entityType' => $entityTypeLocation
                ];
                $communicationMessage = CommunicationMessage::create($communicationMessageInput);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'communicationMessages', 'tableId' => $communicationMessage->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($communicationMessageInput), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityTypeLocation
                ];
                ChangeLog::create($changeLog);
                $userdata = fractal()->item($data)->transformWith(new CommunicationTransformer())->toArray();
                $message = ['message' => trans('messages.createdSuccesfully')];
                DB::commit();
                $endData = array_merge($message, $userdata);
                return $endData;
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Communication
    public function getCommunication($request)
    {
        try {
            $data = Communication::sms()->selectRaw('communications.*,if(patients.firstName IS NULL, staffs.firstName , patients.firstName) as fromName')
                ->join('users as u1', 'u1.id', '=', 'communications.from')
                ->join('staffs', 'staffs.userId', '=', 'u1.id', 'LEFT')
                ->join('globalCodes as g1', 'g1.id', '=', 'communications.messageCategoryId')
                ->join('globalCodes as g2', 'g2.id', '=', 'communications.messageTypeId')
                ->leftJoin('communicationCallRecords', 'communicationCallRecords.communicationId', '=', 'communications.id')
                ->leftJoin('callRecords', 'callRecords.communicationCallRecordId', '=', 'communicationCallRecords.id')
                ->leftJoin('staffs as s1', 's1.id', '=', 'callRecords.staffId')
                ->leftJoin('globalCodes as g3', 'g3.id', '=', 'communicationCallRecords.callStatusId')
                ->join('patients', 'patients.userId', '=', 'u1.id', 'LEFT');

            // $data->leftJoin('providers', 'providers.id', '=', 'communications.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'communications.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('communications.providerLocationId', '=', 'providerLocations.id')->where('communications.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('communications.providerLocationId', '=', 'providerLocationStates.id')->where('communications.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('communications.providerLocationId', '=', 'providerLocationCities.id')->where('communications.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('communications.providerLocationId', '=', 'subLocations.id')->where('communications.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            if (auth()->user()->roleId == 3) {
                if (Helper::haveAccessAction(null, 490) && Helper::haveAccessAction(null, 37)) {
                    $data;
                } else {
                    $data->where('communications.from', auth()->user()->id)->orWhere('referenceId', auth()->user()->id);
                }
            }

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('communications.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['communications.providerLocationId', $providerLocation], ['communications.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['communications.providerLocationId', $providerLocation], ['communications.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['communications.providerLocationId', $providerLocation], ['communications.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['communications.providerLocationId', $providerLocation], ['communications.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['communications.programId', $program], ['communications.entityType', $entityType]]);
            // }
            if ($request->filter) {
                $staff = Staff::where('udid', $request->filter)->first();
                if ($staff) {
                    $data->where(function ($query) use ($staff) {
                        $query->where('callRecords.staffId', $staff->id);
                    });
                } else {
                    $data->where(function ($query) use ($request) {
                        $query->where('g2.name', $request->filter)
                            ->orWhere('g3.name', $request->filter);
                    });
                }
            }
            if ($request->fromDate && $request->toDate) {
                $fromDateStr = Helper::date($request->input('fromDate'));
                $toDateStr = Helper::date($request->input('toDate'));
                if ($request->filter == 'Completed' || $request->filter == 'Waiting' || $request->filter == 'In Progress') {
                    $data->where([['communicationCallRecords.createdAt', '>=', $fromDateStr], ['communicationCallRecords.createdAt', '<=', $toDateStr]]);
                } elseif ($request->filter == 'App Message' || $request->filter == 'Reminder' || $request->filter == 'App Call' || $request->filter == 'Email') {
                    $data->where([['communications.createdAt', '>=', $fromDateStr], ['communicationCallRecords.createdAt', '<=', $toDateStr]]);
                } else {
                    $data->where([['callRecords.createdAt', '>=', $fromDateStr], ['callRecords.createdAt', '<=', $toDateStr]]);
                }
            }

            if ($request->search) {
                $data->where(function ($query) use ($request) {
                    $query->where('communications.updatedAt', 'LIKE', "%" . $request->search . "%");
                    $query->orWhereHas('sender', function ($que) use ($request) {
                        $que->whereHas('patient', function ($q) use ($request) {
                            $q->where(DB::raw("CONCAT(trim(`firstName`), ' ', trim(`lastName`))"), 'LIKE', "%" . $request->search . "%");
                        });
                        $que->orWhereHas('staff', function ($q) use ($request) {
                            $q->where(DB::raw("CONCAT(trim(`firstName`), ' ', trim(`lastName`))"), 'LIKE', "%" . $request->search . "%");
                        });
                    });
                    $query->orWhereHas('receiver', function ($que) use ($request) {
                        $que->whereHas('patient', function ($q) use ($request) {
                            $q->where(DB::raw("CONCAT(trim(`firstName`), ' ', trim(`lastName`))"), 'LIKE', "%" . $request->search . "%");
                        });
                        $que->orWhereHas('staff', function ($q) use ($request) {
                            $q->where(DB::raw("CONCAT(trim(`firstName`), ' ', trim(`lastName`))"), 'LIKE', "%" . $request->search . "%");
                        });
                    });
                    $query->orWhereHas('globalCode', function ($que) use ($request) {
                        $que->where('name', 'LIKE', "%" . $request->search . "%");
                    });
                });
            }

            if ($request->orderField == 'from') {
                $data->orderBy('communications.fromName', $request->orderBy);
            } elseif ($request->orderField == 'category') {
                $data->orderBy('g1.name', $request->orderBy);
            } elseif ($request->orderField == 'to') {
                $data->orderBy('communications.fromName', $request->orderBy);
            } elseif ($request->orderField == 'createdAt') {
                $data->orderBy('updatedAt', $request->orderBy);
            } else {
                $data->orderBy('communications.updatedAt', 'DESC');
            }
            $data = $data->groupBy('communications.id')->paginate(env('PER_PAGE', 20));
            return fractal()->collection($data)->transformWith(new CommunicationTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // calls Per Staff API
    public function callCountPerStaff($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $fromDate = Helper::date($request->input('fromDate'));
            $toDate = Helper::date($request->input('toDate'));
            $data = DB::select(
                "CALL callsPerStaff('" . $fromDate . "','" . $toDate . "','" . $provider . "','" . $providerLocation . "')",
            );
            return fractal()->item($data)->transformWith(new PatientCountTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Communication Message Type Count
    public function messageType()
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $date = Carbon::today()->format('Y-m-d');
            $result = DB::select(
                "CALL communicationTypeCount('" . $date . "','" . $provider . "','" . $providerLocation . "')",
            );
            return fractal()->collection($result)->transformWith(new MessageTypeTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Communication Count
    public function communicationCount($request)
    {
        try {
            $todayFrom = $request->fromDate;
            $todayTo = $request->toDate;
            $yesterdayFrom = strtotime("- 24 hours", $todayFrom);
            $yesterdayTo = strtotime("- 24 hours", $todayTo);
            $tommorrowFrom = strtotime("+ 24 hours", $todayFrom);
            $tommorrowTo = strtotime("+ 24 hours", $todayTo);
            $weekFrom = strtotime("- 7 days", $todayFrom);

            $today = Appointment::whereRaw('UNIX_TIMESTAMP(startDateTime) between ' . $todayFrom . ' AND ' . $todayTo)->count();
            $yesterday = Appointment::whereRaw('UNIX_TIMESTAMP(startDateTime) between ' . $yesterdayFrom . ' AND ' . $yesterdayTo)->count();
            $tomorrow = Appointment::whereRaw('UNIX_TIMESTAMP(startDateTime) between ' . $tommorrowFrom . ' AND ' . $tommorrowTo)->count();
            $week = Appointment::whereRaw('UNIX_TIMESTAMP(startDateTime) between ' . $weekFrom . ' AND ' . $todayTo)->count();
            $Today = ['text' => 'Today', 'count' => $today, 'backgroundColor' => '#91BDFF', 'textColor' => '#FFFFFF'];
            $Yesterday = ['text' => 'Yesterday', 'count' => $yesterday, 'backgroundColor' => '#8E60FF', 'textColor' => '#FFFFFF'];
            $Tomorrow = ['text' => 'Tomorrow', 'count' => $tomorrow, 'backgroundColor' => '#90EEF5', 'textColor' => '#FFFFFF'];
            $Week = ['text' => 'Week', 'count' => $week, 'backgroundColor' => '#FFA800', 'textColor' => '#FFFFFF'];
            $result = [
                $Today, $Yesterday, $Tomorrow, $Week
            ];
            return fractal()->collection($result)->transformWith(new CommunicationCountTransformer())->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Search Communication
    public function communicationSearch($request)
    {
        try {
            $paginate = 1;
            $value = explode(',', $request->search);
            foreach ($value as $search) {
                $data = DB::select(
                    "CALL patientSearch('" . $search . "','" . $paginate . "')",
                );
                $page = $request->page;
                $offSet = ($page * $paginate) - $paginate;
                $currentPage = array_slice($data, $offSet, $paginate, true);
                $paginator = new \Illuminate\Pagination\LengthAwarePaginator($currentPage, count($data), $paginate, $page);
                $route = URL::current();
                $paginator->setPath($route);
                return fractal()->collection($data)->transformWith(new CommunicationSearchTransformer())->paginateWith(new IlluminatePaginatorAdapter($paginator))->toArray();
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Call Update
    public function updateCall($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $start = '';
            $end = '';
            if ($request->status == 'start') {
                $start = Carbon::now();
            } elseif ($request->status == 'end') {
                $end = Carbon::now();
            }
            $comm = array();
            $callRecord = CommunicationCallRecord::where([['referenceId', $id], ['entityType', "conferenceCall"]])->latest()->first();

            if ($request->status == 'start') {
                $callRecordUpdate = ['callStatusId' => 48, 'updatedBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                CommunicationCallRecord::where('id', $callRecord->id)->update($callRecordUpdate);
            }
            $callRecordId = CallRecord::where('communicationCallRecordId', $callRecord->id)->first();
            if (!empty($start)) {
                $callTime = [
                    'providerId' => $provider, 'providerLocationId' => $providerLocation, 'udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(),
                    'callRecordId' => $callRecordId->id, 'updatedBy' => Auth::id(), 'startTime' => $start, 'entityType' => $entityType
                ];
                CallRecordTime::create($callTime);
            }
            if (!empty($end)) {
                $comm['updatedBy'] = Auth::id();
                $comm['endTime'] = $end;
                $callRecordTime = CallRecordTime::where('callRecordId', $callRecordId->id)->whereNull('endTime')->first();
                if (!empty($callRecordTime)) {
                    CallRecordTime::where('id', $callRecordTime->id)->update($comm);
                    $callRecordTime = CallRecordTime::where('id', $callRecordTime->id)->first();
                    $timeFirst = strtotime($callRecordTime->startTime);
                    $timeSecond = strtotime($callRecordTime->endTime);
                    $differenceInSeconds = $timeSecond - $timeFirst;
                    $input = [
                        'staffId' => Auth::user()->staff->id, 'udid' => Str::uuid()->toString(), 'patientId' => $callRecord->patientId,
                        'time' => $differenceInSeconds, 'typeId' => 104, 'statusId' => 328, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'entityType' => 'communication', 'referenceId' => $id, 'createdBy' => Auth::id()
                    ];
                    TimeApproval::create($input);
                }

                // start generate nextbilling date
                $isBilling = 0;
                if(isset($callRecord->id)){
                    $isBilling = 1;
                    $patientId = $callRecord->patientId;
                    //checking if next billing exists.
                    $cptBillling = CptCodeNextBillingServices::where("referenceId",$patientId)
                    ->where("entityType","patient")
                    ->first();
                    if(isset($cptBillling->id)){
                        $isBilling = 0;
                    }
                }else{
                    $patientId = "";
                }

                if($isBilling > 0){
                    // Getting start date of next billing
                    $com = CommunicationCallRecord::where("callStatusId","49")
                    ->where('patientId', $patientId)->orderBy("id","ASC")->first();


                    // Getting Cpt code 99457 for First 20mint.
                    $cptCode = CPTCode::where("name",99457)->first();
                    //Generate Nextbilling Date
                    if(isset($cptCode->id) && isset($com->createdAt)){
                        $nextDate = strtotime($com->createdAt. ' + 15 days');
                        $nextDate = Helper::date($nextDate);
                        $insertArr = array(
                            "cptCodeId" => $cptCode->id,
                            "referenceId" => $patientId,
                            "entityType" => "patient",
                            "functionName" => "callUpdate",
                            "controllerName" => "CommunicationController",
                            "lastBillingAt" => '',
                            "nextBillingAt" => $nextDate,
                            'providerId' => $provider
                        );
                        $cptBilling = CptCodeNextBillingServices::create($insertArr);
                    }
                }
                // end
            }
            // $changeLog = [
            //     'udid' => Str::uuid()->toString(), 'table' => 'communicationCallRecords', 'tableId' => $callRecord->id,
            //     'value' => json_encode($comm), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(),'providerId'=>$provider,'providerLocationId' => $providerLocation
            // ];
            // ChangeLog::create($changeLog);

            return response()->json(['message' => trans('messages.updatedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // call start patient
    public function addCallPatient($request)
    {
        try {
            $staffId = Helper::entity('staff', $request->staff);
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $locationEntityType = Helper::entityType();
            $input = [
                'patientId' => auth()->user()->patient->id,
                'callStatusId' => 47,
                'udid' => Str::uuid()->toString(),
                'referenceId' => $staffId,
                'entityType' => 'staff',
                'createdBy' => Auth::id(),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'locationEntityType' => $locationEntityType,
            ];
            $comm = CommunicationCallRecord::create($input);
            // $changeLog = [
            //     'udid' => Str::uuid()->toString(), 'table' => 'communicationCallRecords', 'tableId' => $comm->id,'providerId'=>$provider,'providerLocationId' => $providerLocation,
            //     'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            // ];
            // ChangeLog::create($changeLog);
            $call = ['providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $locationEntityType, 'udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'communicationCallRecordId' => $comm->id, 'staffId' => $staffId];
            $callRecord = CallRecord::create($call);
            // $changeLog = [
            //     'udid' => Str::uuid()->toString(), 'table' => 'callRecords', 'tableId' => $callRecord->id,'providerId'=>$provider,'providerLocationId' => $providerLocation,
            //     'value' => json_encode($call), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            // ];
            // ChangeLog::create($changeLog);
            $callTime = ['providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $locationEntityType, 'udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'callRecordId' => $callRecord->id];
            $callTimeData = CallRecordTime::create($callTime);
            // $changeLog = [
            //     'udid' => Str::uuid()->toString(), 'table' => 'callRecordTimes', 'tableId' => $callTimeData->id,'providerId'=>$provider,'providerLocationId' => $providerLocation,
            //     'value' => json_encode($callTime), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            // ];
            // ChangeLog::create($changeLog);
            return response()->json(['message' => trans('messages.startCall')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Call Patient Update by staff
    public function updateCallByStaff($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $start = '';
            $end = '';
            if ($request->status == 'start') {
                $start = Carbon::now();
            } elseif ($request->status == 'end') {
                $end = Carbon::now();
            }
            $comm = array();
            if (!empty($start)) {
                $comm['startTime'] = $start;
            }
            if (!empty($end)) {
                $comm['endTime'] = $end;
            }
            $comm['updatedBy'] = Auth::id();
            $comm['providerId'] = $provider;
            $comm['providerLocationId'] = $providerLocation;
            $patientId = User::where('udid', $id)->first();
            $callRecord = CommunicationCallRecord::where([['referenceId', auth()->user()->staff->id], ['createdBy', $patientId->id], ['entityType', 'staff']])->first();
            if ($request->status == 'start') {
                $callRecordUpdate = ['callStatusId' => 48, 'updatedBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                CommunicationCallRecord::where('id', $callRecord->id)->update($callRecordUpdate);
            }
            CallRecordTime::where('callRecordId', $callRecord->id)->update($comm);
            $timeCall = CallRecordTime::where('callRecordId', $callRecord->id)->first();

            // $changeLog = [
            //     'udid' => Str::uuid()->toString(), 'table' => 'callRecordTimes', 'tableId' => $timeCall->id,'providerId'=>$provider,'providerLocationId' => $providerLocation,
            //     'value' => json_encode($comm), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            // ];
            // ChangeLog::create($changeLog);
            return response()->json(['message' => trans('messages.updatedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // call update by patient
    public function updateCallByPatient($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $start = '';
            $end = '';
            if ($request->status == 'start') {
                $start = Carbon::now();
            } elseif ($request->status == 'end') {
                $end = Carbon::now();
            }
            $comm = array();
            if (!empty($start)) {
                $comm['startTime'] = $start;
            }
            if (!empty($end)) {
                $comm['endTime'] = $end;
            }
            $comm['updatedBy'] = Auth::id();
            $patientId = Helper::entity('patient', $id);
            $callRecord = CommunicationCallRecord::where([['referenceId', auth()->user()->staff->id], ['patientId', $patientId], ['entityType', 'staff']])->latest()->first();
            if ($request->status == 'start') {
                $callRecordUpdate = ['callStatusId' => 48, 'updatedBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                CommunicationCallRecord::where('id', $callRecord->id)->update($callRecordUpdate);
            } else {
                $callRecordUpdate = ['callStatusId' => 49, 'updatedBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                CommunicationCallRecord::where('id', $callRecord->id)->update($callRecordUpdate);
            }
            $callRecordId = CallRecord::where('communicationCallRecordId', $callRecord->id)->first();
            if (!empty($start)) {
                $callTime = ['providerId' => $provider, 'providerLocationId' => $providerLocation, 'udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'callRecordId' => $callRecordId->id, 'updatedBy' => Auth::id(), 'startTime' => $start];
                CallRecordTime::create($callTime);
            }
            if (!empty($end)) {
                $comm['updatedBy'] = Auth::id();
                $comm['endTime'] = $end;
                $comm['providerId'] = $provider;
                $comm['providerLocationId'] = $providerLocation;
                $callRecordTime = CallRecordTime::where('callRecordId', $callRecordId->id)->whereNull('endTime')->first();
                if (!empty($callRecordTime)) {
                    CallRecordTime::where('id', $callRecordTime->id)->update($comm);
                    $callRecordTime = CallRecordTime::where('id', $callRecordTime->id)->first();
                    $timeFirst = strtotime($callRecordTime->startTime);
                    $timeSecond = strtotime($callRecordTime->endTime);
                    $differenceInSeconds = $timeSecond - $timeFirst;
                    $input = [
                        'staffId' => Auth::user()->staff->id, 'udid' => Str::uuid()->toString(), 'patientId' => $callRecord->patientId,
                        'time' => $differenceInSeconds, 'typeId' => 104, 'statusId' => 328, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'entityType' => 'communication', 'referenceId' => $id, 'createdBy' => Auth::id(), 'locationEntityType' => $entityType
                    ];
                    TimeApproval::create($input);
                }

                // start generate nextbilling date
                $isBilling = 0;
                if(isset($callRecord->id)){
                    $isBilling = 1;
                    $patientId = $callRecord->patientId;
                    //checking if next billing exists.
                    $cptBillling = CptCodeNextBillingServices::where("referenceId",$patientId)
                    ->where("entityType","patient")
                    ->first();
                    if(isset($cptBillling->id)){
                        $isBilling = 0;
                    }
                }else{
                    $patientId = "";
                }

                if($isBilling > 0){
                    // Getting start date of next billing
                    $com = CommunicationCallRecord::where("callStatusId","49")
                    ->where('patientId', $patientId)->orderBy("id","ASC")->first();


                    // Getting Cpt code 99457 for First 20mint.
                    $cptCode = CPTCode::where("name",99457)->first();
                    //Generate Nextbilling Date
                    if(isset($cptCode->id) && isset($com->createdAt)){
                        $nextDate = strtotime($com->createdAt. ' + 15 days');
                        $nextDate = Helper::date($nextDate);
                        $insertArr = array(
                            "cptCodeId" => $cptCode->id,
                            "referenceId" => $patientId,
                            "entityType" => "patient",
                            "functionName" => "callUpdate",
                            "controllerName" => "CommunicationController",
                            "lastBillingAt" => '',
                            "nextBillingAt" => $nextDate,
                            'providerId' => $provider
                        );
                        $cptBilling = CptCodeNextBillingServices::create($insertArr);
                    }
                }
                // end
            }
            // $changeLog = [
            //     'udid' => Str::uuid()->toString(), 'table' => 'callRecordTimes', 'tableId' => $timeCall->id,'providerId'=>$provider,'providerLocationId' => $providerLocation,
            //     'value' => json_encode($comm), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            // ];
            // ChangeLog::create($changeLog);
            return response()->json(['message' => trans('messages.updatedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Communication Message
    public function getCommunicationMessages($request, $id)
    {
        try {
            $data = Communication::select('communications.*')->with('communicationMessage');
            $data->where([['communications.id', $id], ['communications.messageTypeId', 105]])->orWhere([['communications.id', $id], ['communications.messageTypeId', 327]]);

            // $data->leftJoin('providers', 'providers.id', '=', 'communications.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'communications.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('communications.providerLocationId', '=', 'providerLocations.id')->where('communications.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('communications.providerLocationId', '=', 'providerLocationStates.id')->where('communications.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('communications.providerLocationId', '=', 'providerLocationCities.id')->where('communications.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('communications.providerLocationId', '=', 'subLocations.id')->where('communications.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('communications.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['communications.providerLocationId', $providerLocation], ['communications.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['communications.providerLocationId', $providerLocation], ['communications.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['communications.providerLocationId', $providerLocation], ['communications.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['communications.providerLocationId', $providerLocation], ['communications.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['communications.programId', $program], ['communications.entityType', $entityType]]);
            // }
            $data = $data->get();
            if ($data) {
                return fractal()->collection($data)->transformWith(new EmailTransformer(true))->toArray();
            } else {
                return response()->json(['data' => []]);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Communication Calls
    public function getCommunicationCalls($request, $id)
    {
        try {
            $data = CommunicationCallRecord::where('communicationCallRecords.communicationId', $id)
                ->whereHas('communication', function ($query) {
                    $query->where('messageTypeId', 104);
                })->whereHas('callRecord', function ($query) {
                    $query->whereHas('callRecordTime', function ($query) {
                        $query->whereNotNull('startTime')->orWhereNotNull('endTime');
                    });
                });

            // $data->leftJoin('providers', 'providers.id', '=', 'communicationCallRecords.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'communicationCallRecords.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('communicationCallRecords.providerLocationId', '=', 'providerLocations.id')->where('communicationCallRecords.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('communicationCallRecords.providerLocationId', '=', 'providerLocationStates.id')->where('communicationCallRecords.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('communicationCallRecords.providerLocationId', '=', 'providerLocationCities.id')->where('communicationCallRecords.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('communicationCallRecords.providerLocationId', '=', 'subLocations.id')->where('communicationCallRecords.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('communicationCallRecords.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['communicationCallRecords.providerLocationId', $providerLocation], ['communicationCallRecords.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['communicationCallRecords.providerLocationId', $providerLocation], ['communicationCallRecords.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['communicationCallRecords.providerLocationId', $providerLocation], ['communicationCallRecords.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['communicationCallRecords.providerLocationId', $providerLocation], ['communicationCallRecords.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['communicationCallRecords.programId', $program], ['communicationCallRecords.entityType', $entityType]]);
            // }
            $data = $data->get();
            return fractal()->collection($data)->transformWith(new CommunicationCallTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Patient Calls
    public function patientCommunicationCalls($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $staffId = Helper::entity('staff', $id);
            $staff = Staff::where('id', $staffId)->first();
            $commData = [
                'from' => auth()->user()->patient->userId,
                'referenceId' => $staff->userId,
                'messageTypeId' => 104,
                'subject' => 'App Call',
                'priorityId' => 72,
                'messageCategoryId' => 40,
                'createdBy' => Auth::id(),
                'entityType' => 'appCall',
                'udid' => Str::uuid()->toString(),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'locationEntityType' => $entityType
            ];
            $dataComm = Communication::create($commData);
            if ($dataComm) {
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'communications', 'tableId' => $dataComm->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($commData), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                ];
                ChangeLog::create($changeLog);
            }
            $input = [
                'patientId' => auth()->user()->patient->id,
                'callStatusId' => 47,
                'udid' => Str::uuid()->toString(),
                'referenceId' => $staffId,
                'entityType' => 'staff',
                'createdBy' => Auth::id(),
                'communicationId' => $dataComm->id,
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'locationEntityType' => $entityType
            ];
            $comm = CommunicationCallRecord::create($input);
            if ($comm) {
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'communicationCallRecords', 'tableId' => $comm->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                ];
                ChangeLog::create($changeLog);
            }
            $call = [
                'udid' => Str::uuid()->toString(),
                'createdBy' => auth()->user()->id,
                'communicationCallRecordId' => $comm->id,
                'staffId' => $staffId,
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'entityType' => $entityType
            ];
            $callRecord = CallRecord::create($call);
            if ($callRecord) {
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'callRecords', 'tableId' => $callRecord->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($call), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                ];
                ChangeLog::create($changeLog);
            }
            $callTime = [
                'udid' => Str::uuid()->toString(),
                'createdBy' => auth()->user()->id,
                'communicationCallRecordId' => $comm->id,
                'staffId' => $staffId,
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'entityType' => $entityType
            ];
            $callRecord = CallRecord::create($callTime);
            if ($callRecord) {
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'callRecords', 'tableId' => $callRecord->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($call), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                ];
                ChangeLog::create($changeLog);
            }
            return response()->json(['message' => trans('messages.createdSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Call Status (Patient)
    public function patientCommunicationCallStatusUpdate($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $end = Carbon::now();
            $patientId = Auth::id();
            $input = [
                'callStatusId' => '49',
                'updatedBy' => $patientId,
                'providerId' => $provider,
                'providerLocationId' => $providerLocation
            ];
            $callRecord = CommunicationCallRecord::where([['patientId', auth()->user()->patient->id], ['callStatusId', '!=', 49]])->first();
            CommunicationCallRecord::where([['patientId', auth()->user()->patient->id], ['callStatusId', '!=', 49]])->update($input);
            if ($callRecord) {
                $callRecordId = CallRecord::where('communicationCallRecordId', $callRecord->id)->first();
                $comm['updatedBy'] = Auth::id();
                $comm['endTime'] = $end;
                $comm['providerId'] = $provider;
                $comm['providerLocationId'] = $providerLocation;
                $callRecordTime = CallRecordTime::where('callRecordId', $callRecordId->id)->whereNull('endTime')->first();
                if ($callRecordTime) {
                    CallRecordTime::where('id', $callRecordTime->id)->update($comm);
                }
            }
            return response()->json(['message' => trans('messages.updatedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Call Status
    public function callStatusList($request, $id)
    {
        try {
            $patientId = Helper::entity('patient', $id);
            $data = CommunicationCallRecord::select('communicationCallRecords.*')->with('status')
                ->where([['communicationCallRecords.patientId', $patientId], ['communicationCallRecords.referenceId', NULL]]);

            // $data->leftJoin('providers', 'providers.id', '=', 'communicationCallRecords.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'communicationCallRecords.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('communicationCallRecords.providerLocationId', '=', 'providerLocations.id')->where('communicationCallRecords.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('communicationCallRecords.providerLocationId', '=', 'providerLocationStates.id')->where('communicationCallRecords.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('communicationCallRecords.providerLocationId', '=', 'providerLocationCities.id')->where('communicationCallRecords.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('communicationCallRecords.providerLocationId', '=', 'subLocations.id')->where('communicationCallRecords.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('communicationCallRecords.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['communicationCallRecords.providerLocationId', $providerLocation], ['communicationCallRecords.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['communicationCallRecords.providerLocationId', $providerLocation], ['communicationCallRecords.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['communicationCallRecords.providerLocationId', $providerLocation], ['communicationCallRecords.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['communicationCallRecords.providerLocationId', $providerLocation], ['communicationCallRecords.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['communicationCallRecords.programId', $program], ['communicationCallRecords.entityType', $entityType]]);
            // }
            $data = $data->get();
            return fractal()->collection($data)->transformWith(new AppoinmentCallStatusTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Reply Message
    public function communicationReply($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $data = Communication::where('id', $id)->first();
            $communicationMessageInput = [
                'communicationId' => $id,
                'message' => $request->message,
                'createdBy' => Auth::id(),
                'udid' => Str::uuid()->toString(),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'entityType' => $entityType
            ];
            CommunicationMessage::create($communicationMessageInput);

            Communication::where('id', $id)->update(['updatedBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation]);

            $patient = Patient::with("user")->where('userId', $data->referenceId)->first();
            $userIds = array();
            if (!empty($patient)) {
                $patient = Patient::with("user")->where('userId', $data->referenceId)->first();
                $userEmail = $patient->user->email;
                $sendTo = $patient->phoneNumber;
                $fullName = $patient->firstName . " " . $patient->lastName;
                $UserId = $patient->userId;
                $userIds[$patient->user->email] = $userIds;
            } else {
                $patient = Patient::with("user")->where('userId', $data->from)->first();
                if (!empty($patient)) {
                    $userEmail = $patient->user->email;
                    $sendTo = $patient->phoneNumber;
                    $fullName = $patient->firstName . " " . $patient->lastName;
                    $UserId = $patient->userId;
                    $userIds[$patient->user->email] = $userIds;
                } elseif ($data->referenceId == Auth::id()) {
                    $staff = Staff::with("user")->where('userId', $data->from)->first();
                    $userEmail = $staff->user->email;
                    $sendTo = $staff->phoneNumber;
                    $fullName = $staff->firstName . " " . $staff->lastName;
                    $UserId = $staff->userId;
                    $userIds[$staff->user->email] = $userIds;
                } else {
                    $staff = Staff::with("user")->where('userId', $data->referenceId)->first();
                    $userEmail = $staff->user->email;
                    $sendTo = $staff->phoneNumber;
                    $fullName = $staff->firstName . " " . $staff->lastName;
                    $UserId = $staff->userId;
                    $userIds[$staff->user->email] = $userIds;
                }
            }
            $inboundData = CommunicationInbound::where('communicationId', $id)->first();
            if (!is_null($inboundData)) {
                $to = $inboundData->from;
                $emailFrom = $inboundData->to;
            } else {
                $to = $userEmail;
                $emailFrom = '';
            }
            if ($data->messageTypeId == 327) {
                $sendTo = $inboundData->to;
                Helper::sendBandwidthMessage($request->message, $sendTo);
            } elseif ($data->messageTypeId == 105) {
                $fromName = "Virtare Health";
                $subject = "Re: " . $data->subject;
                Helper::commonMailjet($to, $fromName, $request->message, $subject, $emailFrom, $userIds, 'Communication Reply', $data->id);
            }
            if (!is_null($patient)) {
                $timeLine = [
                    'patientId' => $patient->id, 'heading' => 'Message Sent', 'title' => $request->message . ' ' . '<b>to' . ' ' . ucfirst($patient->lastName) . ' ' . ucfirst($patient->firstName) . ' ' . '</b>', 'type' => 9,
                    'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                ];
                PatientTimeLine::create($timeLine);
            }
            $notificationData = [
                'body' => 'Communication reply.',
                'title' => 'Communication Reply',
                'userId' => $data->referenceId,
                'isSent' => 0,
                'entity' => 'Communication',
                'referenceId' => $UserId,
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'entityType' => $entityType
            ];
            $notification = Notification::create($notificationData);

            event(new NotificationEvent($notification));
            $notificationUpdate = ['isSent' => '1', 'providerId' => $provider, 'providerLocationId' => $providerLocation, 'updatedBy' => Auth::id()];
            Notification::where('id', $notification->id)->update($notificationUpdate);

            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'notifications', 'tableId' => $notification->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($notificationData), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);
            return response()->json(['message' => trans('messages.messageSent')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Communication Inbound
    public function getCommunicationInbound($request, $id)
    {
        try {
            $data = CommunicationInbound::select('communicationInbounds.*')->where('communicationId', null);
            // $data->leftJoin('providers', 'providers.id', '=', 'communicationInbounds.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'communicationInbounds.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('communicationInbounds.providerLocationId', '=', 'providerLocations.id')->where('communicationInbounds.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('communicationInbounds.providerLocationId', '=', 'providerLocationStates.id')->where('communicationInbounds.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('communicationInbounds.providerLocationId', '=', 'providerLocationCities.id')->where('communicationInbounds.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('communicationInbounds.providerLocationId', '=', 'subLocations.id')->where('communicationInbounds.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('communicationInbounds.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['communicationInbounds.providerLocationId', $providerLocation], ['communicationInbounds.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['communicationInbounds.providerLocationId', $providerLocation], ['communicationInbounds.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['communicationInbounds.providerLocationId', $providerLocation], ['communicationInbounds.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['communicationInbounds.providerLocationId', $providerLocation], ['communicationInbounds.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['communicationInbounds.programId', $program], ['communicationInbounds.entityType', $entityType]]);
            // }
            if (empty($id)) {
                if ($request->search) {
                    $data->where(function ($query) use ($request) {
                        $query->where('communicationInbounds.from', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('communicationInbounds.to', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('communicationInbounds.type', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('communicationInbounds.subject', 'LIKE', '%' . $request->search . '%');
                    });
                }
                if ($request->orderField == 'from') {
                    $data->orderBy('communicationInbounds.from', $request->orderBy);
                } elseif ($request->orderField == 'to') {
                    $data->orderBy('communicationInbounds.to', $request->orderBy);
                } elseif ($request->orderField == 'subject') {
                    $data->orderBy('communicationInbounds.subject', $request->orderBy);
                } else {
                    $data->orderBy('communicationInbounds.createdAt', 'ASC');
                }
                $data = $data->select('communicationInbounds.*')->paginate(env('PER_PAGE', 20));
                return fractal()->collection($data)->transformWith(new CommunicationInboundTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
            }
            $data = $data->where('communicationInbounds.udid', $id)->first();
            return fractal()->item($data)->transformWith(new CommunicationInboundTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Communication Inbound
    public function deleteCommunicationInbound($request, $id)
    {
        try {
            $data = CommunicationInbound::where('udid', $id)->first();
            $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1];
            CommunicationInbound::where('udid', $id)->update($input);
            CommunicationInbound::where('udid', $id)->delete();
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'communicationInbounds', 'tableId' => $data->id,
                'value' => json_encode($input), 'type' => 'deleted', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            return response()->json(['message' => trans('messages.deletedSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Communication Inbound
    public function updateCommunicationInbound($request, $id)
    {
        try {
            $data = CommunicationInbound::where('udid', $id)->first();
            $input = array();
            if (!empty($request->input('communicationId'))) {
                $input['communicationId'] = $request->input('communicationId');
            }
            $input['updatedBy'] = Auth::id();
            if (!empty($input)) {
                CommunicationInbound::where('udid', $id)->update($input);
            }
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'communicationInbounds', 'tableId' => $data->id,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            return response()->json(['message' => trans('messages.updatedSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
