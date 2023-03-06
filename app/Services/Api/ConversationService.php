<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\Patient\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Patient\PatientTimeLine;
use App\Models\Communication\Communication;
use App\Models\Patient\PatientFamilyMember;
use App\Services\Api\PushNotificationService;
use App\Models\Conversation\ConversationMessage;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Transformers\Conversation\ConversationTransformer;
use App\Transformers\Conversation\LatestMessageTransformer;
use App\Transformers\Conversation\ConversationListTransformer;

class ConversationService
{

    // Add Conversation
    public function createConversation($request, $id)
    {
        // try {
        $provider = Helper::providerId();
        $providerLocation = Helper::providerLocationId();
        $entityType = Helper::entityType();
        if (!$id) {
            $familyMember = PatientFamilyMember::where([['userId', auth()->user()->id], ['isPrimary', 1]])->exists();
            if ($familyMember == true) {
                return response()->json(['message' => trans('messages.unauthenticated')], 401);
            } else {
                $senderId = auth()->user()->id;
            }
        } elseif ($id == auth()->user()->id) {
            return response()->json(['message' => trans('messages.unauthenticated')], 401);
        } elseif ($id) {
            $familyMember = PatientFamilyMember::where([['userId', auth()->user()->id], ['isPrimary', 1]])->exists();
            if ($familyMember == true) {
                $senderId = $id;
            } else {
                return response()->json(['message' => trans('messages.unauthenticated')], 401);
            }
        } else {
            return response()->json(['message' => trans('messages.unauthenticated')], 401);
        }
        $receiverId = $request->receiverId;
        $data = DB::table('communications')->where([['from', '=', $senderId], ['referenceId', '=', $receiverId], ['messageTypeId', '=', 102]])->orWhere(function ($query) use ($receiverId, $senderId) {
            $query->where([['from', '=', $receiverId], ['referenceId', $senderId]])->where('messageTypeId', '=', 102);
        })->exists();
        if ($data == false) {
            $input = array(
                'udid' => Str::uuid()->toString(),
                'from' => $senderId,
                'referenceId' => $request->receiverId,
                'entityType' => 'staff',
                'messageTypeId' => 102,
                'subject' => 'SMS',
                'priorityId' => 72,
                'messageCategoryId' => 40,
                "createdBy" => Auth::id(),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'locationEntityType' => $entityType,
            );
            $conversation = Communication::create($input);

            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'communications', 'tableId' => $conversation->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);

            return fractal()->item($conversation)->transformWith(new ConversationListTransformer(true))->toArray();
        } elseif ($data == true) {
            $conversation = Communication::with('sender', 'receiver')->where([['from', '=', $senderId], ['referenceId', '=', $receiverId], ['messageTypeId', '=', 102]])->orWhere(function ($query) use ($receiverId, $senderId) {
                $query->where([['from', '=', $receiverId], ['referenceId', $senderId]])->where('messageTypeId', '=', 102);
            })->first();
            return fractal()->item($conversation)->transformWith(new ConversationListTransformer(true))->toArray();
        } else {
            return response()->json(['message' => trans('messages.unauthenticated')], 401);
        }
        // } catch (Exception $e) {
        //     throw new \RuntimeException($e);
        // }
    }

    // List Conversation
    public function allConversation($request, $id)
    {
        try {
            $data = Communication::select('communications.*')->with('communicationMessage');

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

            if (!$id) {
                $data->whereHas('conversationMessages')->where(function ($query) {
                    $query->where('from', auth()->user()->id)->orWhere('referenceId', auth()->user()->id);
                });
            }
            if ($id) {
                $patient = Patient::where('udid', $id)->first();
                $notHaveAccess = Helper::haveAccess($patient->id);
                if (!$notHaveAccess) {
                    $data->whereHas('conversationMessages')->where(function ($query) use ($patient) {
                        $query->where('from', $patient->userId)->orWhere('referenceId', $patient->userId);
                    });
                } else {
                    return $notHaveAccess;
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
            if ($request->orderField == 'date') {
                $data->orderBy('communications.updatedAt', $request->orderBy);
            } else {
                $data->orderBy('communications.id', 'DESC');
            }
            $data = $data->paginate(env('PER_PAGE', 50));
            return fractal()->collection($data)->transformWith(new ConversationListTransformer)->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Detail Conversation
    public function conversationDetail($request, $id)
    {
        try {
            $data = Communication::select('communications.*')->whereHas('conversationMessages')->where('communications.id', $id);

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
            $data = $data->first();
            return fractal()->item($data)->transformWith(new ConversationListTransformer)->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Sent Message
    public function sendMessage($request, $id)
    {
        $providerId = Helper::providerId();
        $providerLocation = Helper::providerLocationId();
        $entityType = Helper::entityType();
        if (!$id) {
            $familyMember = PatientFamilyMember::where([['userId', auth()->user()->id], ['isPrimary', 1]])->exists();
            if ($familyMember == true) {
                return response()->json(['message' => trans('messages.unauthenticated')], 401);
            } else {
                $senderId = auth()->user()->id;
            }
        } elseif ($id == auth()->user()->id) {
            return response()->json(['message' => trans('messages.unauthenticated')], 401);
        } elseif ($id) {
            $familyMember = PatientFamilyMember::where([['userId', auth()->user()->id], ['isPrimary', 1]])->exists();
            if ($familyMember == true) {
                $senderId = $id;
            } else {
                return response()->json(['message' => trans('messages.unauthenticated')], 401);
            }
        } else {
            return response()->json(['message' => trans('messages.unauthenticated')], 401);
        }
        $input = array(
            'communicationId' => $request->conversationId,
            'message' => $request->message,
            'senderId' => $senderId,
            'type' => $request->type,
            "createdBy" => Auth::id(),
            'providerId' => $providerId,
            'providerLocationId' => $providerLocation,
            'entityType' => $entityType,
        );
        $conversation = ConversationMessage::create($input);
        $communicationId = Communication::where('communications.id', $request->conversationId)->selectRaw('communications.*,if(p1.firstName IS NULL, CONCAT(s1.lastName,","," ",s1.firstName," ",s1.middleName)
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
        if ($communicationId->patientId) {
            if ($communicationId->udid == auth()->user()->id) {
                $timeLine = [
                    'patientId' => $communicationId->patientId, 'heading' => 'Message Received', 'title' => $request->message . ' ' . '<b>From' . ' ' . $communicationId->fromName . ' ' . '</b>', 'type' => 9,
                    'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                ];
            } else {
                $timeLine = [
                    'patientId' => $communicationId->patientId, 'heading' => 'Message Sent', 'title' => $request->message . ' ' . '<b>to' . ' ' . $communicationId->fromName . ' ' . '</b>', 'type' => 9,
                    'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation, 'entityType' => $entityType
                ];
            }
            PatientTimeLine::create($timeLine);
        }
        $changeLog = [
            'udid' => Str::uuid()->toString(), 'table' => 'messages', 'tableId' => $conversation->id, 'providerId' => $providerId, 'providerLocationId' => $providerLocation,
            'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
        ];
        ChangeLog::create($changeLog);
        $data = ['updatedAt' => Carbon::now(), 'updatedBy' => Auth::id(), 'providerId' => $providerId, 'providerLocationId' => $providerLocation];
        $commMessage = Communication::where('id', $request->conversationId)->update($data);
        // $changeLog = [
        //     'udid' => Str::uuid()->toString(), 'table' => 'communications', 'tableId' => $request->conversationId,'providerId' => $providerId,'providerLocationId' => $providerLocation,
        //     'value' => json_encode($data), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
        // ];
        // ChangeLog::create($changeLog);
        $con = ConversationMessage::where('communicationId', $request->conversationId)->first();
        $pushnotification = new PushNotificationService();
        $communication = Communication::where('id', $request->conversationId)->first();
        if ($conversation->senderId == $communication->from) {
            $notificationData = array(
                "body" => "You have received new message from" . ' ' . ($con->communication->sender->staff ? ucfirst($con->communication->sender->staff->firstName) . ' ' . ucfirst($con->communication->sender->staff->lastName) : ucfirst($con->communication->sender->patient->firstName) . ' ' . ucfirst($con->communication->sender->patient->lastName)),
                "title" => "New message",
                "type" => "Communication",
                "typeId" => $request->conversationId,
            );
            $pushnotification->sendNotification([$communication->referenceId], $notificationData);
        } elseif ($conversation->senderId == $communication->referenceId) {
            $notificationData = array(
                "body" => "You have received new message from" . ' ' . ($con->communication->receiver->staff ? ucfirst($con->communication->receiver->staff->firstName) . ' ' . ucfirst($con->communication->receiver->staff->lastName) : ucfirst($con->communication->receiver->patient->firstName) . ' ' . ucfirst($con->communication->receiver->patient->lastName)),
                "title" => "New message",
                "type" => "Communication",
                "typeId" => $request->conversationId,
            );
            $pushnotification->sendNotification([$communication->from], $notificationData);
        }
        return response()->json([
            'message' => trans('messages.message_sent')
        ]);
    }

    // Show Conversation
    public function showConversation($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $conversationId = $request->conversationId;
            if ($conversationId) {
                $input = Communication::where([['id', $conversationId]])->exists();
                if ($input == true) {
                    $data = ConversationMessage::where([['communicationId', $conversationId]])->orderBy('id', 'DESC')->paginate(env('PER_PAGE', 20));
                } else {
                    $data = ConversationMessage::where([['communicationId', $conversationId]])->orderBy('id', 'DESC')->paginate(env('PER_PAGE', 20));
                }
                $senderId = auth()->user()->id;
                $message = ['isRead' => 1, 'updatedBy' => Auth::id(), 'providerId' => $provider, 'providerLocationId' => $providerLocation];
                ConversationMessage::where([['communicationId', $conversationId], ['senderId', "!=", $senderId]])->update($message);
                $conversation = ConversationMessage::where([['communicationId', $conversationId], ['senderId', "!=", $senderId]])->first();

                if ($conversation) {
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'messages', 'tableId' => $conversation->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($message), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                }
                return fractal()->collection($data)->transformWith(new ConversationTransformer)->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
            } else {
                if (!$id) {
                    $familyMember = PatientFamilyMember::where([['userId', auth()->user()->id], ['isPrimary', 1]])->exists();
                    if ($familyMember == true) {
                        return response()->json(['message' => trans('messages.unauthenticated')], 401);
                    } else {
                        $senderId = auth()->user()->id;
                    }
                } elseif ($id == auth()->user()->id) {
                    return response()->json(['message' => trans('messages.unauthenticated')], 401);
                } elseif ($id) {
                    $familyMember = PatientFamilyMember::where([['userId', auth()->user()->id], ['isPrimary', 1]])->exists();
                    if ($familyMember == true) {
                        $senderId = $id;
                    } else {
                        return response()->json(['message' => trans('messages.unauthenticated')], 401);
                    }
                } else {
                    return response()->json(['message' => trans('messages.unauthenticated')], 401);
                }
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Latest Message
    public function latestMessage($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            if (!$id) {
                $familyMember = PatientFamilyMember::where([['userId', auth()->user()->id]])->exists();
                if ($familyMember == true) {
                    return response()->json(['message' => trans('messages.unauthenticated')], 401);
                } else {
                    $senderId = auth()->user()->id;
                }
            } elseif ($id == auth()->user()->id) {
                return response()->json(['message' => trans('messages.unauthenticated')], 401);
            } elseif ($id) {
                $familyMember = PatientFamilyMember::where([['userId', auth()->user()->id]])->exists();
                if ($familyMember == true) {
                    $senderId = $id;
                } else {
                    return response()->json(['message' => trans('messages.unauthenticated')], 401);
                }
            } else {
                return response()->json(['message' => trans('messages.unauthenticated')], 401);
            }
            $communicationId = $request->conversationId;
            $data = ConversationMessage::where([['isRead', 0], ['communicationId', $communicationId], ['senderId', "!=", $senderId]]);
            $newdata = $data->get();
            $input = ['isRead' => 1];
            $data->update($input);
            if ($data) {
                foreach ($newdata as $value) {
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'messages', 'tableId' => $value->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                        'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                    ];
                    ChangeLog::create($changeLog);
                }
            }
            return fractal()->collection($newdata)->transformWith(new LatestMessageTransformer)->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Check Conversation Exists
    public function conversationExists($request)
    {
        $provider = Helper::providerId();
        $providerLocation = Helper::providerLocationId();
        $entityType = Helper::entityType();
        $receiverId = $request->receiverId;
        $senderId = auth()->user()->id;
        $data = DB::table('communications')->where([['from', '=', $senderId], ['referenceId', '=', $receiverId], ['messageTypeId', '=', 102]])->orWhere(function ($query) use ($receiverId, $senderId) {
            $query->where([['from', '=', $receiverId], ['referenceId', $senderId]])->where('messageTypeId', '=', 102);
        })->exists();
        if ($data == false) {
            $input = array(
                'udid' => Str::uuid()->toString(),
                'from' => $senderId,
                'referenceId' => $request->receiverId,
                'entityType' => 'staff',
                'messageTypeId' => 102,
                'subject' => 'SMS',
                'priorityId' => 72,
                'messageCategoryId' => 40,
                "createdBy" => Auth::id(),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'locationEntityType' => $entityType,
            );
            $conversation = Communication::create($input);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'communications', 'tableId' => $conversation->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);
            return fractal()->item($conversation)->transformWith(new ConversationListTransformer(true))->toArray();
        }
    }
}
