<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Carbon\Carbon;
use App\Models\User\User;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\Contact\Contact;
use App\Models\Patient\Patient;
use App\Models\Contact\ContactText;
use App\Models\Contact\RequestCall;
use App\Models\Contact\ContactEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\Notification\Notification;
use App\Models\Communication\Communication;
use App\Models\ConfigMessage\ConfigMessage;
use App\Transformers\Contact\ContactTransformer;
use App\Models\Communication\CommunicationMessage;
use App\Transformers\Contact\ContactNewTransformer;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class ContactService
{

    // List Request Call
    public function requestContactList()
    {
        try {
            $data = RequestCall::select('requestCalls.*')->with('contactTime', 'messageStatus', 'user')->where('requestCalls.isActive', 1);

            // $data->leftJoin('providers', 'providers.id', '=', 'requestCalls.providerId')->where('providers.isActive', 1)->whereNull('providers.deletedAt');
            // $data->leftJoin('programs', 'programs.id', '=', 'requestCalls.programId')->where('programs.isActive', 1)->whereNull('programs.deletedAt');

            // $data->leftJoin('providerLocations', function ($join) {
            //     $join->on('requestCalls.providerLocationId', '=', 'providerLocations.id')->where('requestCalls.entityType', '=', 'Country');
            // })->whereNull('providerLocations.deletedAt');

            // $data->leftJoin('providerLocationStates', function ($join) {
            //     $join->on('requestCalls.providerLocationId', '=', 'providerLocationStates.id')->where('requestCalls.entityType', '=', 'State');
            // })->whereNull('providerLocationStates.deletedAt');

            // $data->leftJoin('providerLocationCities', function ($join) {
            //     $join->on('requestCalls.providerLocationId', '=', 'providerLocationCities.id')->where('requestCalls.entityType', '=', 'City');
            // })->whereNull('providerLocationCities.deletedAt');

            // $data->leftJoin('subLocations', function ($join) {
            //     $join->on('requestCalls.providerLocationId', '=', 'subLocations.id')->where('requestCalls.entityType', '=', 'subLocation');
            // })->whereNull('subLocations.deletedAt');

            // if (request()->header('providerId')) {
            //     $provider = Helper::providerId();
            //     $data->where('requestCalls.providerId', $provider);
            // }
            // if (request()->header('providerLocationId')) {
            //     $providerLocation = Helper::providerLocationId();
            //     if (request()->header('entityType') == 'Country') {
            //         $data->where([['requestCalls.providerLocationId', $providerLocation], ['requestCalls.entityType', 'Country']]);
            //     }
            //     if (request()->header('entityType') == 'State') {
            //         $data->where([['requestCalls.providerLocationId', $providerLocation], ['requestCalls.entityType', 'State']]);
            //     }
            //     if (request()->header('entityType') == 'City') {
            //         $data->where([['requestCalls.providerLocationId', $providerLocation], ['requestCalls.entityType', 'City']]);
            //     }
            //     if (request()->header('entityType') == 'subLocation') {
            //         $data->where([['requestCalls.providerLocationId', $providerLocation], ['requestCalls.entityType', 'subLocation']]);
            //     }
            // }
            // if (request()->header('programId')) {
            //     $program = Helper::programId();
            //     $entityType = Helper::entityType();
            //     $data->where([['requestCalls.programId', $program], ['requestCalls.entityType', $entityType]]);
            // }
            $data = $data->get();
            return fractal()->collection($data)->transformWith(new ContactTransformer())->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Request Call
    public function requestCall($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $id = Auth::id();
            $data = [
                "userId" => $id,
                "contactTimeId" => $request->contactTimeId,
                "messageStatusId" => 283,
                "createdBy" => $id,
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'entityType' => $entityType,
            ];
            RequestCall::create($data);
            return response()->json(['message' => trans('messages.callRequest')], 200);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Request Call
    public function requestcallUpdate($request, $patientId, $id)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $patientId = Helper::entity('patient', $patientId);
            $patien = Patient::where('id', $patientId)->first();
            $userId = $patien->userId;
            $input = array();
            if (!empty($request->input('contactTimeId'))) {
                $input['contactTimeId'] = $request->input('contactTimeId');
            }
            if (!empty($request->input('messageStatusId'))) {
                $input['messageStatusId'] = $request->input('messageStatusId');
            }
            $input['isActive'] = 0;
            $input['updatedBy'] = Auth::id();
            $input['providerId'] = $provider;
            $input['providerLocationId'] = $providerLocation;
            if (!empty($input)) {
                RequestCall::where([['userId', $userId], ['id', $id]])->update($input);
            }
            return response()->json(['message' => trans('messages.updatedSuccesfully')], 200);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Contact Text
    public function contactMessage($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $id = Auth::id();
            $data = [
                "userId" => $id,
                "message" => $request->message,
                "messageStatusId" => 47,
                "createdBy" => Auth::id(),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'entityType' => $entityType,
            ];
            $contact = ContactText::create($data);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'contactTexts', 'tableId' => $contact->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($data), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);
            return response()->json(['message' => trans('messages.message_request')], 200);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Contact Email
    public function contactEmail($request)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $data = $request->all();
            $status = 1;
            // Mail::to($data['email'])->send(new Contact($data));
            $user = User::where('email', $data['email'])->first();
            $existence = User::where('email', $data['email'])->exists();
            $this->sendData($request, $status);
            if ($existence == true) {
                $input = [
                    'from' => auth()->user()->id,
                    'referenceId' => $user->id,
                    'messageTypeId' => 105,
                    'subject' => $request->subject,
                    'priorityId' => 71,
                    'messageCategoryId' => 40,
                    'createdBy' => auth()->user()->id,
                    'entityType' => $request->entityType,
                    'udid' => Str::uuid()->toString(),
                    'providerId' => $provider,
                    'providerLocationId' => $providerLocation,
                    'locationEntityType' => $entityType,
                ];
                $data = Communication::create($input);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'communications', 'tableId' => $data->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                ];
                ChangeLog::create($changeLog);
                $communication = [
                    'communicationId' => $data->id,
                    'message' => $request->message,
                    'createdBy' => $data->createdBy,
                    'udid' => Str::uuid()->toString(),
                    'providerId' => $provider,
                    'providerLocationId' => $providerLocation,
                    'entityType' => $entityType,
                ];
                $message = CommunicationMessage::create($communication);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'communicationMessages', 'tableId' => $message->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                    'value' => json_encode($communication), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
                ];
                ChangeLog::create($changeLog);
                if (auth()->user()->roleId == 4) {
                    $userName = auth()->user()->patient->firstName . ' ' . auth()->user()->patient->lastName;
                } else {
                    $userName = auth()->user()->staff->firstName . ' ' . auth()->user()->staff->lastName;
                }
                $userName =
                    $notification = [
                        'body' => 'You have a new email from' . ' ' . $userName,
                        'title' => 'Email',
                        'userId' => $user->id,
                        'isSent' => 0,
                        'entity' => 'Communication',
                        'referenceId' => $data->id,
                        'createdBy' => auth()->user()->id,
                        'providerId' => $provider,
                        'providerLocationId' => $providerLocation,
                        'entityType' => $entityType,
                    ];
                $notificationData = Notification::create($notification);
                // $changeLog = [
                //     'udid' => Str::uuid()->toString(), 'table' => 'notifications', 'tableId' => $notificationData->id,'providerId' => $provider,'providerLocationId' => $providerLocation,
                //     'value' => json_encode($notification), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                // ];
                // ChangeLog::create($changeLog);
            }
            return response()->json(['message' => trans('messages.email_request')], 200);
        } catch (Exception $e) {
            $status = 0;
            $this->sendData($request, $status);
            throw new \RuntimeException($e);
        }
    }

    // Add Contact Email
    public function sendData($data, $status)
    {
        try {
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $entityType = Helper::entityType();
            $data = [
                "userId" => Auth::id(),
                "name" => $data->name,
                "email" => $data->email,
                "message" => $data->message,
                "status" => $status,
                "createdBy" => Auth::id(),
                'providerId' => $provider,
                'providerLocationId' => $providerLocation,
                'entityType' => $entityType,
            ];
            $contact = ContactEmail::create($data);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'contactEmails', 'tableId' => $contact->id, 'providerId' => $provider, 'providerLocationId' => $providerLocation,
                'value' => json_encode($data), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(), 'entityType' => $entityType
            ];
            ChangeLog::create($changeLog);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Contact (Client & Site)
    public function contactAdd($request, $entity, $id)
    {
        try {

            if($request->input('email')){
                $password = Str::random("10");
                $user = [
                    'udid' => Str::uuid()->toString(),
                    'email' => $request->email,
                    'password' => Hash::make($password),
                    'emailVerify' => 1,
                    'createdBy' => Auth::id(),
                    'roleId' => 3,
                ];
                $data = User::create($user);
            }

            $reference = Helper::entity($entity, $id);
            $isSystemUser = $request->isSystemUser == true ? 1 : 0;
            $input = $request->only(
                'firstName',
                'middleName',
                'lastName',
                'statusId',
                'phoneNumber',
                'genderId',
                'title',
                'isSiteHead',
                // 'email',
            );

            $entityType = [
                'client' => 'Client',
                'site' => 'Site',
                'careTeam' => 'CareTeam',
            ];
            if (isset($entityType[$entity])) {
                return response()->json(['message' => trans('messages.INTERNAL_ERROR')],500);
            }
            $other = [
                'createdBy' => Auth::id(),
                'udid' => Str::uuid()->toString(),
                'isSystemUser' => $isSystemUser,
                'referenceId' => $reference,
                'entityType' => $entityType[$entity],
            ];
            $data = array_merge($input, $other);
            $contact = new Contact();
            $contact = $contact->contactAdd($data);

            if (!$contact) {
                return response()->json(['message' => trans('messages.INTERNAL_ERROR')],500);
            }
            $client = Contact::where('id', $contact->id)->first();
            $userData = fractal()->item($client)->transformWith(new ContactNewTransformer())->toArray();
            $message = ['message' => trans('messages.createdSuccesfully')];
            return array_merge($message, $userData);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Contact (Client & Site)
    public function contactList($request, $entity, $id, $contactId)
    {
        try {
            $contact = Contact::where(['entityType' => $entity]);
            if (!$contactId) {
                $contact = $contact->paginate(env('PER_PAGE', 20));
                return fractal()->collection($contact)->transformWith(new ContactNewTransformer())->paginateWith(new IlluminatePaginatorAdapter($contact))->toArray();
            } else {
                $contact = $contact->where('udid', $contactId)->first();
                return fractal()->item($contact)->transformWith(new ContactNewTransformer())->toArray();
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Contact (Client & Site)
    public function contactUpdate($request, $entity, $id, $contactId)
    {
        try {
            $data = Contact::where(['udid' => $contactId])->first();
            if (!$data) {
                return response()->json(['message' => trans('messages.UUID_NOT_FOUND')],404);
            }
            $contact = $this->contactRequestInputs($request);
            $contact = Contact::where(['udid' => $contactId])->update($contact);
            if (!$contact) {
                return response()->json(['message' => trans('messages.INTERNAL_ERROR')],500);
            }
            $data = Contact::where(['udid' => $contactId])->first();
            return fractal()->item($data)->transformWith(new ContactNewTransformer())->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Contact (Client & Site)
    public function contactDelete($request, $entity, $id, $contactId)
    {
        try {
            $input = $this->deleteInputs();
            $contact = Contact::where(['udid' => $contactId])->first();
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'contacts', 'tableId' => $contact->id,
                'value' => json_encode($input), 'type' => 'deleted', 'ip' => request()->ip(), 'createdBy' => Auth::id(),
            ];
            $log = new ChangeLog();
            $log = $log->makeLog($changeLog);
            $contact = new Contact();
            $contact = $contact->dataSoftDelete($contactId, $input);
            if (!$contact) {
                return response()->json(['message' => trans('messages.INTERNAL_ERROR')],500);
            }
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Input Contact (Client & Site)
    public function deleteInputs(): array
    {
        return ['isActive' => 0, 'isDelete' => 1, 'deletedBy' => Auth::id(), 'deletedAt' => Carbon::now()];
    }

    // Update Common Function Contact (Client & Site)
    public function contactRequestInputs($request): array
    {
        $client = array();
        if ($request->firstName) {
            $client['firstName'] = $request->firstName;
        }
        if ($request->middleName) {
            $client['middleName'] = $request->middleName;
        }
        if ($request->lastName) {
            $client['lastName'] = $request->lastName;
        }
        if ($request->phoneNumber) {
            $client['phoneNumber'] = $request->phoneNumber;
        }
        if ($request->genderId) {
            $client['genderId'] = $request->genderId;
        }
        if ($request->addressLine1) {
            $client['addressLine1'] = $request->addressLine1;
        }
        if ($request->addressLine2) {
            $client['addressLine2'] = $request->addressLine2;
        }
        if ($request->city) {
            $client['city'] = $request->city;
        }
        if ($request->stateId) {
            $client['stateId'] = $request->stateId;
        }
        if ($request->isAdmin) {
            $client['isAdmin'] = $request->isAdmin == true ? 1 : 0;
        }
        if ($request->isSystemUser) {
            $client['isSystemUser'] = $request->isSystemUser == true ? 1 : 0;
        }
        if ($request->zipCode) {
            $client['zipCode'] = $request->zipCode;
        }
        if ($request->title) {
            $client['title'] = $request->title;
        }
        if ($request->isSiteHead) {
            $client['isSiteHead'] = $request->isSiteHead == true ? 1 : 0;
        }
        if ($request->email) {
            $client['email'] = $request->email;
        }
        $client['updatedBy'] = Auth::id();
        return $client;
    }
}
