<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Carbon\Carbon;
use App\Models\User\User;
use App\Models\Group\Group;
use App\Models\Staff\Staff;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\Program\Program;
use App\Models\Provider\Provider;
use Illuminate\Support\Facades\DB;
use App\Models\Group\GroupProvider;
use App\Models\Provider\SubLocation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Provider\ProviderDomain;
use App\Models\Provider\ProviderProgram;
use App\Models\Provider\ProviderLocation;
use App\Models\ConfigMessage\ConfigMessage;
use App\Transformers\Group\GroupTransformer;
use App\Models\Provider\ProviderLocationCity;
use App\Models\Provider\ProviderLocationState;
use App\Models\Provider\ProviderLocationProgram;
use App\Models\Staff\StaffProvider\StaffProvider;
use App\Transformers\Provider\ProviderTransformer;
use App\Transformers\Staff\StaffDetailTransformer;
use App\Transformers\Group\GroupProviderTransformer;
use App\Transformers\Provider\SubLocationTransformer;
use App\Transformers\Provider\ProviderGroupTransformer;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Transformers\Provider\ProviderLocationTransformer;
use App\Transformers\Provider\ProviderLocationCityTransformer;
use App\Transformers\Provider\ProviderLocationStateTransformer;
use App\Transformers\Provider\ProviderLocationDetailTransformer;
use App\Transformers\Provider\ProviderLocationProgramTransformer;
use App\Models\Client\CareTeam;

class ProviderService
{

    // Add Provider
    public function store($request)
    {
        try {
            $dataDomain = ProviderDomain::where('name', $request->input('domain'))->first();
            if (!$dataDomain) {
                $domainData = ['udid' => Str::uuid()->toString(), 'name' => $request->input('domain'), 'createdBy' => Auth::id()];
                $domain = ProviderDomain::create($domainData);
                $active = $request->isActive == true ? 1 : 0;
                $active = 1;
                $input = $request->only(['name', 'address', 'address2', 'stateId', 'city', 'zipcode', 'phoneNumber', 'moduleId', 'networkId', 'tagId', 'countryId']);
                $otherData = [
                    'udid' => Str::uuid()->toString(),
                    'createdBy' => Auth::id(),
                    'isActive' => $active,
                    'domainId' => $domain->id,
                    'totalLocations' => $request->input('totalLocations'),
                    'countLocations' => 0,
                ];
                $data = JSON_ENCODE(array_merge(
                    $input,
                    $otherData
                ));

                $id = DB::select(
                    "CALL addProvider('" . $data . "')"
                );
                if ($request->programs) {
                    foreach ($request->programs as $value) {
                        $program = Program::where('udid', $value)->first();
                        $providerProgram = ['createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $id[0]->id, 'programId' => $program->id];
                        ProviderProgram::create($providerProgram);
                    }
                }
                $provider = Provider::where('id', $id[0]->id)->first();
                $userdata = fractal()->item($provider)->transformWith(new ProviderTransformer())->toArray();
                $message = ['message' => trans('messages.createdSuccesfully')];
                $endData = array_merge($message, $userdata);
                return $endData;
            } else {
                return response()->json(['domain' => array(trans('messages.domain'))], 422);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Provider Contact
    public function addProviderContact($request, $id)
    {
        try {
            $providerLocation = Helper::providerLocationId();
            $password = Str::random("10");
            $user = [
                'udid' => Str::uuid()->toString(),
                'email' => $request->email,
                'password' => Hash::make($password),
                'emailVerify' => 1,
                'createdBy' => Auth::id(),
                'roleId' => 2,
                'providerId' => $id,
                'providerLocationId' => $providerLocation,
            ];
            $userData = User::create($user);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => $userData->id, 'providerId' => $id, 'providerLocationId' => $providerLocation,
                'value' => json_encode($user), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);

            $staff = [
                'udid' => Str::uuid()->toString(),
                'userId' => $userData->id,
                'firstName' => $request->firstName,
                'middleName' => $request->middleName,
                'lastName' => $request->lastName,
                'phoneNumber' => $request->phoneNumber,
                'genderId' => $request->genderId,
                'specializationId' => 6,
                'networkId' => 4,
                'roleId' => 2,
                'createdBy' => Auth::id(),
                'providerId' => $id,
                'providerLocationId' => $providerLocation,
                'designationId' => 366,
            ];
            $newData = Staff::create($staff);
            $defaultProvider = [
                'udid' => Str::uuid()->toString(), 'providerId' => $id, 'createdBy' => Auth::id(), 'isDefault' => 1,
                'staffId' => $newData->id
            ];
            StaffProvider::create($defaultProvider);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'staffs', 'tableId' => $newData->id, 'providerId' => $id, 'providerLocationId' => $providerLocation,
                'value' => json_encode($staff), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            $provider = Staff::where('id', $newData->id)->first();
            $userdata = fractal()->item($provider)->transformWith(new StaffDetailTransformer())->toArray();
            $message = ['message' => trans('messages.createdSuccesfully')];
            $endData = array_merge($message, $userdata);

            if (isset($request->phoneNumber) && !empty($request->phoneNumber)) {
                $msgSMSObj = ConfigMessage::where("type", "addProvider")
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

                $responseApi = Helper::sendBandwidthMessage($message, $request->phoneNumber);
            }
            if (isset($request->email)) {
                $to = $request->email;
                $msgObj = ConfigMessage::where("type", "addProvider")
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

                if (isset($msgObj->subject)) {
                    $subject = $msgObj->subject;
                } else {
                    $subject = "Create New Account";
                }


                Helper::commonMailjet($to, $fromName, $message, $subject, '', array(), 'Provider Craeted', '');
            }
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Provider Contact
    public function listProviderContact($request, $id, $contactId)
    {
        try {
            $data = Staff::where('providerId', $id);
            if (!$contactId) {
                if ($request->orderBy && $request->orderField) {
                    $data->orderBy($request->orderField, $request->orderBy);
                } else {
                    $data->orderBy('firstName', 'ASC');
                }
                $data = $data->paginate(env('PER_PAGE', 20));
                return fractal()->collection($data)->transformWith(new StaffDetailTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
            } else {
                $data = $data->where('udid', $contactId)->first();
                return fractal()->item($data)->transformWith(new StaffDetailTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Provider Location
    public function providerLocationStore($request, $id)
    {
        try {
            $country = ProviderLocation::where([['providerId', $id], ['countryId', $request->country]])->first();
            if (!$country) {
                $otherData = [
                    'udid' => Str::uuid()->toString(),
                    'createdBy' => Auth::id(),
                    'providerId' => $id,
                    'countryId' => $request->country,
                    'isDefault' => $request->input('isDefault'),
                ];
                $data = JSON_ENCODE(array_merge(
                    $otherData
                ));
                $location = DB::select(
                    "CALL addProviderLocations('" . $data . "')"
                );
            }
            if ($country) {
                $locId = $country->id;
            } else {
                $locId = $location[0]->id;
            }

            if ($request->state) {
                $stateData = ProviderLocationState::where([['providerId', $id], ['countryId', $locId], ['stateId', $request->state]])->first();
                if (!$stateData) {
                    $input = [
                        'udid' => Str::uuid()->toString(),
                        'createdBy' => Auth::id(),
                        'countryId' => $locId,
                        'stateId' => $request->state,
                        'providerId' => $id,
                    ];
                    $state = ProviderLocationState::create($input);
                }
                if ($stateData) {
                    $stateDataId = $stateData->id;
                } else {
                    $stateDataId = $state->id;
                }
            }

            if ($request->city) {
                $cityData = ProviderLocationCity::where([['providerId', $id], ['stateId', $stateDataId], ['city', $request->city]])->first();
                if (!$cityData) {
                    $input = [
                        'udid' => Str::uuid()->toString(),
                        'createdBy' => Auth::id(),
                        'stateId' => $stateDataId,
                        'city' => $request->city,
                        'providerId' => $id,
                    ];
                    ProviderLocationCity::create($input);
                }
            }
            $provider = ProviderLocation::where('id', $locId)->first();
            $userdata = fractal()->item($provider)->transformWith(new ProviderLocationDetailTransformer())->toArray();
            $message = ['message' => trans('messages.createdSuccesfully')];
            $endData = array_merge($message, $userdata);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Provider Location SubLocation
    public function providerLocationSubLocationAdd($request, $id, $locationId)
    {
        try {
            if ($request->entityType == 'City') {
                $entityType = $request->entityType;
            } else {
                $entityType = 'subLocation';
            }
            $subLocation = [
                'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $id,
                'subLocationLevel' => $request->input('subLocationLevel'), 'subLocationName' => $request->input('subLocationName'),
                'subLocationParent' => $locationId, 'entityType' => $entityType
            ];
            $data = SubLocation::create($subLocation);
            $provider = SubLocation::where('id', $data->id)->first();
            $userdata = fractal()->item($provider)->transformWith(new SubLocationTransformer())->toArray();
            $message = ['message' => trans('messages.createdSuccesfully')];
            $endData = array_merge($message, $userdata);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Provider Location SubLocation
    public function providerLocationSubLocationDelete($request, $id, $locationId, $subLocationId)
    {
        try {
            $subLocation = ['updatedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1];
            SubLocation::where([['providerLocationId', $locationId], ['id', $subLocationId]])->update($subLocation);
            SubLocation::where([['providerLocationId', $locationId], ['id', $subLocationId]])->delete();
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Provider
    public function index($request, $id)
    {
        try {
            $careteamsData = array();
            $careteams = CareTeam::where(['isActive' => 1]);
            if (auth()->user()->roleId != 1) {
                $client = Staff::where(['userId' => Auth::id()])->get('clientId');
                $careteams = $careteams->whereIn('clientId', $client)->get('id');
            }
            if ($request->search) {
                $careteams->where('name', 'LIKE', '%' . $request->search . '%');
            }
            $allRecords =  $careteams->get();
            if ($allRecords->count() > 0) {
                foreach ($allRecords as $key => $record) {
                    $careteamsData[$key]['udid'] = $record->udid;
                    $careteamsData[$key]['name'] = $record->name;
                }
            }
            return response()->json(['data' => $careteamsData], 200);
            /*   if (!$id) {
                if ($request->all) {
                    if ($request->active == 1) {
                        $data = Provider::all();
                    } else {
                        $data = Provider::where('isActive', 1)->get();
                    }
                    return fractal()->collection($data)->transformWith(new ProviderTransformer(true))->toArray();
                } else {
                    $data = Provider::with('state');
                    if ($request->search) {
                        $data->where('name', 'LIKE', '%' . $request->search . '%');
                    }
                    if ($request->active) {
                        $data;
                    } else {
                        $data->where('isActive', 1);
                    }
                    if ($request->orderBy && $request->orderField) {
                        $data->orderBy($request->orderField, $request->orderBy);
                    } else {
                        $data->orderBy('name', 'ASC');
                    }
                    $data = $data->paginate(env('PER_PAGE', 20));
                    return fractal()->collection($data)->transformWith(new ProviderTransformer(true))->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
                }
            } elseif ($id) {
                $data = Provider::where('id', $id)->with('providerProgram')->first();
                return fractal()->item($data)->transformWith(new ProviderTransformer(true))->toArray();
            } else {
                return response()->json(['message' => trans('messages.unauthenticated')], 401);
            }*/
        } catch (Exception $e) {
            echo $e->getMessage().''.$e->getLine();
            //throw new \RuntimeException($e);
            throw $e;
        }
    }

    // List Location
    public function listLocation($request, $id, $locationId)
    {
        try {
            $data = ProviderLocation::leftJoin('globalCodes', 'globalCodes.id', '=', 'providerLocations.level')
                ->leftJoin('subLocations', 'subLocations.providerLocationId', '=', 'providerLocations.id')
                ->leftJoin('providerLocationStates', 'providerLocationStates.countryId', '=', 'providerLocations.id')
                ->with('parentName')->where('providerLocations.providerId', $id)->select('providerLocations.*')->groupBy('providerLocations.id');
            if (!$locationId) {
                $data = $data->select('providerLocations.*')->groupBy('providerLocations.id')->get();
                return fractal()->collection($data)->transformWith(new ProviderLocationTransformer())->toArray();
            } elseif ($locationId) {
                if ($request->entityType == 'Country') {
                    $data = $data->where('providerLocations.id', $locationId)->first();
                    return fractal()->collection($data->state)->transformWith(new ProviderLocationStateTransformer())->toArray();
                }
                if ($request->entityType == 'State') {
                    $data = ProviderLocationState::where([['providerId', $id], ['id', $locationId]])->first();
                    return fractal()->collection($data->city)->transformWith(new ProviderLocationCityTransformer())->toArray();
                }
                if ($request->entityType == 'City') {
                    $data = SubLocation::where([['providerId', $id], ['subLocationParent', $locationId], ['entityType', 'City']])->get();
                    return fractal()->collection($data)->transformWith(new SubLocationTransformer())->toArray();
                }
                if ($request->entityType == 'subLocation') {
                    $data = SubLocation::where([['providerId', $id], ['subLocationParent', $locationId], ['entityType', 'subLocation']])->get();
                    return fractal()->collection($data)->transformWith(new SubLocationTransformer())->toArray();
                }
                $data = $data->where('providerLocations.id', $locationId)->first();
                return fractal()->item($data)->transformWith(new ProviderLocationTransformer())->toArray();
            } else {
                return response()->json(['message' => trans('messages.unauthenticated')], 401);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Provider
    public function updateProviderOld($request, $id)
    {
        try {
            $provider = Helper::providerId();
            $provider = array();
            if (!empty($request->name)) {
                $provider['name'] = $request->name;
            }
            if (!empty($request->address)) {
                $provider['address'] = $request->address;
            }
            if (!empty($request->countryId)) {
                $provider['countryId'] = $request->countryId;
            }
            if (!empty($request->stateId)) {
                $provider['stateId'] = $request->stateId;
            }
            if (!empty($request->city)) {
                $provider['city'] = $request->city;
            }
            if (!empty($request->zipCode)) {
                $provider['zipCode'] = $request->zipCode;
            }
            if (!empty($request->phoneNumber)) {
                $provider['phoneNumber'] = $request->phoneNumber;
            }
            if (!empty($request->tagId)) {
                $provider['tagId'] = $request->tagId;
            }
            if (!empty($request->moduleId)) {
                $provider['moduleId'] = $request->moduleId;
            }
            if (isset($request->isActive)) {
                $provider['isActive'] = $request->isActive;
            }
            $provider['updatedBy'] = Auth::id();
            Provider::where('id', $id)->update($provider);


            if ($request->programs) {
                $input = ['updatedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1];
                ProviderProgram::where([['providerId', $id]])->update($input);
                $providerData = ProviderProgram::where([['providerId', $id]])->first();
                if ($providerData) {
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'providerPrograms', 'tableId' => $providerData->providerProgramId,
                        'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                }
                $location = ProviderLocationProgram::where('providerId', $id)->get();
                if ($location) {
                    $input = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1];
                    ProviderLocationProgram::where([['providerId', $id]])->update($input);
                    ProviderLocationProgram::where([['providerId', $id]])->delete();
                }
                ProviderProgram::where([['providerId', $id]])->delete();
                foreach ($request->programs as $value) {
                    $program = Program::where('udid', $value)->first();

                    $providerProgram = ['createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $id, 'programId' => $program->id];
                    $data = ProviderProgram::create($providerProgram);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'providerPrograms', 'tableId' => $data->id,
                        'value' => json_encode($providerProgram), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                }
            }

            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'providers', 'tableId' => $id,
                'value' => json_encode($provider), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            $enddata = Provider::where('id', $id)->first();
            $message = ['message' => trans('messages.updatedSuccesfully')];
            $data = fractal()->item($enddata)->transformWith(new ProviderTransformer())->toArray();
            return array_merge($message, $data);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function updateProvider($request, $id)
    {
        try {
            $provider = array();
            if (!empty($request->name)) {
                $provider['name'] = $request->name;
            }
            if (!empty($request->friendlyName)) {
                $provider['friendlyName'] = $request->friendlyName;
            }
            if (!empty($request->address)) {
                $provider['address'] = $request->address;
            }
            if (!empty($request->status)) {
                $provider['status'] = $request->status;
            }
            if (!empty($request->clientType)) {
                $provider['clientType'] = $request->clientType;
            }
            if (!empty($request->npi)) {
                $provider['npi'] = $request->npi;
            }
            // if (!empty($request->countryId)) {
            //     $provider['countryId'] = $request->countryId;
            // }
            if (!empty($request->stateId)) {
                $provider['stateId'] = $request->stateId;
            }
            if (!empty($request->city)) {
                $provider['city'] = $request->city;
            }
            if (!empty($request->zipCode)) {
                $provider['zipCode'] = $request->zipCode;
            }
            if (!empty($request->phoneNumber)) {
                $provider['phoneNumber'] = $request->phoneNumber;
            }
            // if (!empty($request->tagId)) {
            //     $provider['tagId'] = $request->tagId;
            // }
            // if (!empty($request->moduleId)) {
            //     $provider['moduleId'] = $request->moduleId;
            // }
            // if (isset($request->isActive)) {
            //     $provider['isActive'] = $request->isActive;
            // }
            // if (isset($request->totalLocations)) {
            //     $provider['isActive'] = $request->input('totalLocations');
            // }
            $provider['updatedBy'] = Auth::id();
            Provider::where('id', $id)->update($provider);

            if ($request->programs) {
                $providerData = ProviderProgram::where('providerId', $id)->where("isActive", 1)->get();
                $newProg = array();
                foreach ($request->programs as $val) {
                    $program = Program::where('udid', $val)->first();
                    $newProg[$program->id] = $val;
                }

                $currentProgram = array();
                $deleteProgram = [];
                if ($providerData) {
                    $location = ProviderLocationProgram::where('providerId', $id)->where("isActive", 1)->get();
                    $i = 0;
                    foreach ($providerData as $value) {
                        if (isset($newProg[$value->programId])) {
                            $currentProgram[$value->programId] = $id;
                        } else {
                            if (isset($value->providerProgramId)) {
                                $now = Carbon::now();
                                $input = ['updatedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1, "deletedAt" => $now];
                                ProviderProgram::where('providerId', $id)
                                    ->where("providerProgramId", $value->providerProgramId)
                                    ->update($input);
                                $deleteProgram[$i] = $value->programId;
                                if (!empty($location)) {
                                    ProviderLocationProgram::where([['providerId', $id]])
                                        ->where("programId", $value->programId)
                                        ->update($input);
                                }
                            }
                        }
                        $i++;
                    }
                }

                foreach ($request->programs as $pro) {
                    $program = Program::where('udid', $pro)->first();
                    if (isset($program->id)) {
                        if (isset($currentProgram[$program->id])) {
                        } else {
                            if (isset($program->id)) {
                                $providerProgram = ['createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'providerId' => $id, 'programId' => $program->id];
                                $data = ProviderProgram::create($providerProgram);
                                $changeLog = [
                                    'udid' => Str::uuid()->toString(), 'table' => 'providerPrograms', 'tableId' => $data->id,
                                    'value' => json_encode($providerProgram), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                                ];
                                ChangeLog::create($changeLog);
                            }
                        }
                    }
                }
            }

            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'providers', 'tableId' => $id,
                'value' => json_encode($provider), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            $enddata = Provider::where('id', $id)->first();
            $message = ['message' => trans('messages.updatedSuccesfully')];
            $data = fractal()->item($enddata)->transformWith(new ProviderTransformer())->toArray();
            return array_merge($message, $data);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Provider Contact
    public function updateProviderContact($request, $id, $contactId)
    {
        try {
            $enddata = Staff::where('udid', $contactId)->first();
            $providerLocation = Helper::providerLocationId();
            $user = [
                'email' => $request->email,
                'updatedBy' => Auth::id(),
            ];
            $userData = User::where('id', $enddata->userId)->update($user);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => $enddata->userId, 'providerId' => $id, 'providerLocationId' => $providerLocation,
                'value' => json_encode($user), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);

            $provider = array();
            if (!empty($request->firstName)) {
                $provider['firstName'] = $request->firstName;
            }
            if (!empty($request->lastName)) {
                $provider['lastName'] = $request->lastName;
            }
            if (!empty($request->middleName)) {
                $provider['middleName'] = $request->middleName;
            }
            if (!empty($request->genderId)) {
                $provider['genderId'] = $request->genderId;
            }
            if (!empty($request->phoneNumber)) {
                $provider['phoneNumber'] = $request->phoneNumber;
            }
            $provider['updatedBy'] = Auth::id();
            Staff::where('udid', $contactId)->update($provider);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'staffs', 'tableId' => $enddata->id,
                'value' => json_encode($provider), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            $message = ['message' => trans('messages.updatedSuccesfully')];
            $data = fractal()->item($enddata)->transformWith(new StaffDetailTransformer())->toArray();
            return array_merge($message, $data);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Provider Location
    public function updateLocation($request, $id, $locationId)
    {
        try {
            $input = array();
            if (!empty($request->country)) {
                $input['countryId'] = $request->country;
            }
            if (!empty($request->isDefault)) {
                $input['isDefault'] = $request->isDefault;
            }
            $input['updatedBy'] = Auth::id();
            ProviderLocation::where('id', $locationId)->update($input);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'providerLocations', 'tableId' => $locationId, 'providerId' => $id,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            if ($request->stateId) {
                $input = array();
                if (!empty($request->state)) {
                    $input['stateId'] = $request->state;
                }
                $input['updatedBy'] = Auth::id();
                ProviderLocationState::where('id', $request->stateId)->update($input);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'providerLocationStates', 'tableId' => $request->stateId, 'providerId' => $id,
                    'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
            }
            if ($request->cityId) {
                $input = array();
                if (!empty($request->city)) {
                    $input['city'] = $request->city;
                }
                $input['updatedBy'] = Auth::id();
                ProviderLocationCity::where('id', $request->cityId)->update($input);
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'providerLocationCities', 'tableId' => $request->cityId, 'providerId' => $id,
                    'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
            }
            $enddata = ProviderLocation::where('id', $locationId)->first();
            $message = ['message' => trans('messages.updatedSuccesfully')];
            $data = fractal()->item($enddata)->transformWith(new ProviderLocationTransformer())->toArray();
            return array_merge($message, $data);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Provider Location
    public function deleteProviderLocation($id, $locationId)
    {
        try {
            $provider = Helper::providerId();
            if (!$locationId) {
                $input = [
                    'isActive' => 0, 'isDelete' => 1, 'deletedBy' => Auth::id()
                ];
                Provider::where('id', $id)->update($input);
                $location = ProviderLocation::where('providerId', $id)->first();
                ProviderLocation::where('providerId', $id)->update($input);
                if ($id) {
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'providers', 'tableId' => $id, 'providerId' => $provider,
                        'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                }
                if ($location) {
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'providerLocations', 'tableId' => $location->id,
                        'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                    $state = ProviderLocationState::where('countryId', $location->id)->get();
                    if ($state) {
                        foreach ($state as $value) {
                            ProviderLocationState::where('countryId', $location->id)->update($input);
                            $changeLog = [
                                'udid' => Str::uuid()->toString(), 'table' => 'providerLocationStates', 'tableId' => $value['id'],
                                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                            ];
                            ChangeLog::create($changeLog);
                            $city = ProviderLocationCity::where('stateId', $value['id'])->get();
                            if ($city) {
                                foreach ($city as $value) {
                                    ProviderLocationCity::where('stateId', $value['id'])->update($input);
                                    $changeLog = [
                                        'udid' => Str::uuid()->toString(), 'table' => 'providerLocationPrograms', 'tableId' => $value['id'],
                                        'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                                    ];
                                    ChangeLog::create($changeLog);
                                    ProviderLocationCity::where('stateId', $value['id'])->delete();
                                }
                            }
                            ProviderLocationState::where('countryId', $location->id)->delete();
                        }
                    }
                    $locationProgram = ProviderLocationProgram::where([['referenceId', $locationId], ['entityType', 'providerLocation']])->get();
                    if ($locationProgram) {
                        foreach ($locationProgram as $value) {
                            ProviderLocationProgram::where('providerLocationId', $value['providerLocationId'])->update($input);
                            $changeLog = [
                                'udid' => Str::uuid()->toString(), 'table' => 'providerLocationPrograms', 'tableId' => $value['id'],
                                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                            ];
                            ChangeLog::create($changeLog);
                            ProviderLocationProgram::where([['referenceId', $locationId], ['entityType', 'providerLocation']])->delete();
                        }
                    }
                }
                Provider::where('id', $id)->delete();
                ProviderLocation::where('providerId', $id)->delete();
            } elseif ($locationId) {
                $input = [
                    'isActive' => 0, 'isDelete' => 1, 'deletedBy' => Auth::id()
                ];
                ProviderLocation::where([['providerId', $id], ['id', $locationId]])->update($input);


                $state = ProviderLocationState::where('countryId', $locationId)->get();
                if ($state) {
                    foreach ($state as $value) {
                        ProviderLocationState::where('countryId', $locationId)->update($input);
                        $changeLog = [
                            'udid' => Str::uuid()->toString(), 'table' => 'providerLocationStates', 'tableId' => $value['id'],
                            'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                        ];
                        ChangeLog::create($changeLog);
                        $city = ProviderLocationCity::where('stateId', $value['id'])->get();
                        if ($city) {
                            foreach ($city as $value) {
                                ProviderLocationCity::where('stateId', $value['id'])->update($input);
                                $changeLog = [
                                    'udid' => Str::uuid()->toString(), 'table' => 'providerLocationPrograms', 'tableId' => $value['id'],
                                    'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                                ];
                                ChangeLog::create($changeLog);
                                ProviderLocationCity::where('stateId', $value['id'])->delete();
                            }
                        }
                        ProviderLocationState::where('countryId', $locationId)->delete();
                    }
                }


                $parent = ProviderLocation::where('parent', $locationId)->get();
                if ($parent) {
                    $parent = ProviderLocation::where('parent', $locationId)->update($input);
                    $parent = ProviderLocation::where('parent', $locationId)->delete();
                }
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'providerLocations', 'tableId' => $locationId, 'providerId' => $provider,
                    'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
                $locationProgram = ProviderLocationProgram::where([['referenceId', $locationId], ['entityType', 'providerLocation']])->get();
                if ($locationProgram) {
                    ProviderLocationProgram::where([['referenceId', $locationId], ['entityType', 'providerLocation']])->update($input);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'providerLocationPrograms', 'tableId' => $locationId,
                        'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                    ProviderLocationProgram::where([['referenceId', $locationId], ['entityType', 'providerLocation']])->delete();
                }
                ProviderLocation::where([['providerId', $id], ['id', $locationId]])->delete();
            } else {
                return response()->json(['message' => trans('messages.unauthenticated')], 401);
            }
            return response()->json(['message' => trans('messages.deletedSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Provider Location Program
    public function providerLocationProgramAdd($request, $id, $locationId)
    {
        try {
            foreach ($request->programs as $value) {
                $program = Program::where('udid', $value)->first();
                $dataProgram = ProviderLocationProgram::where([['entityType', $request->entityType], ['referenceId', $locationId], ['programId', $program->id]])->first();
                if (!$dataProgram) {
                    $providerProgram = ['createdBy' => Auth::id(), 'udid' => Str::uuid()->toString(), 'programId' => $program->id, 'entityType' => $request->entityType, 'referenceId' => $locationId, 'providerId' => $id];
                    $data = ProviderLocationProgram::create($providerProgram);
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'providerLocationPrograms', 'tableId' => $data->id,
                        'value' => json_encode($providerProgram), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                } else {
                    return response()->json(['programs' => array(trans('messages.locationProgram'))], 422);
                }
            }
            $enddata = ProviderLocationProgram::where([['entityType', $request->entityType], ['referenceId', $locationId], ['providerId', $id]])->get();
            $message = ['message' => trans('messages.createdSuccesfully')];
            $data = fractal()->collection($enddata)->transformWith(new ProviderLocationProgramTransformer())->toArray();
            return array_merge($message, $data);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Provider Location Program
    public function deleteProviderLocationProgramOld($id, $locationId, $programId)
    {
        try {
            $input = ['isActive' => 0, 'isDelete' => 1, 'deletedBy' => Auth::id()];
            ProviderLocationProgram::where('id', $programId)->update($input);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'providerLocationPrograms', 'tableId' => $locationId,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);
            ProviderLocationProgram::where('id', $programId)->delete();
            return response()->json(['message' => trans('messages.deletedSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete Provider Location Program
    public function deleteProviderLocationProgram($request, $id, $locationId, $programId)
    {
        try {
            $post = $request->all();
            $input = ['isActive' => 0, 'isDelete' => 1, 'deletedBy' => Auth::id()];

            if (isset($post["entityType"])) {
                $locationType = $post["entityType"];

                $stateId = [];
                if ($locationType == "Country") {
                    // getting state base country location id
                    $stateObj = ProviderLocationState::where([['providerId', $id], ['countryId', $locationId]])->get();
                    if (!empty($stateObj)) {
                        foreach ($stateObj as $s) {
                            $stateId[] = $s->id;
                        }
                    }
                }

                if ($locationType == "State") {
                    // getting state based on state location id
                    $stateId[] = $locationId;
                }


                if ($locationType == "Country" || $locationType == "State") {
                    // getting city based on state.
                    if (!empty($stateId)) {
                        $cityObj = ProviderLocationCity::whereIn("stateId", $stateId)->where('providerId', $id)->get();
                    }

                    $cityId = [];
                    if (!empty($cityObj)) {
                        foreach ($cityObj as $c) {
                            $cityId[] = $c->id;
                        }
                    }
                }

                if ($locationType == "City") {
                    // getting city
                    $cityId[] = $locationId;
                    // get sublocations base city
                    $citySubLocatoinObj = SubLocation::where("entityType", "City")->whereIn('subLocationParent', $cityId)->get();
                    $citySublocationId = [];
                    if (!empty($citySubLocatoinObj)) {
                        foreach ($citySubLocatoinObj as $sbL) {
                            $citySublocationId[] = $sbL->id;
                        }
                    }
                }

                if ($locationType == "subLocation") {
                    $sublocationId[] = $locationId;
                    $this->deleteSublocation($input, $programId, $citySublocationId);
                }

                if ($locationType == "Country") {
                    // delete program from Country
                    ProviderLocationProgram::where("entityType", "Country")->where('referenceId', $locationId)->where('programId', $programId)->update($input);
                    ProviderLocationProgram::where("entityType", "Country")->where('referenceId', $locationId)->where('programId', $programId)->delete();
                }

                if ($locationType == "Country" || $locationType == "State") {
                    if (!empty($stateId)) {
                        //delete program from State
                        ProviderLocationProgram::where("entityType", "State")->whereIn('referenceId', $stateId)->where('programId', $programId)->update($input);
                        ProviderLocationProgram::where("entityType", "State")->whereIn('referenceId', $stateId)->where('programId', $programId)->delete();
                    }
                }

                if ($locationType == "Country" || $locationType == "State" || $locationType == "City") {
                    if (!empty($cityId)) {
                        // delete program from City
                        ProviderLocationProgram::where("entityType", "City")->whereIn('referenceId', $cityId)->where('programId', $programId)->update($input);
                        ProviderLocationProgram::where("entityType", "City")->whereIn('referenceId', $cityId)->where('programId', $programId)->delete();
                    }
                }

                if ($locationType == "Country" || $locationType == "State" || $locationType == "subLocation") {
                    if (!empty($citySublocationId)) {
                        // delete program from subLocation
                        ProviderLocationProgram::where("entityType", "subLocation")->whereIn('referenceId', $citySublocationId)->where('programId', $programId)->update($input);
                        ProviderLocationProgram::where("entityType", "subLocation")->whereIn('referenceId', $citySublocationId)->where('programId', $programId)->delete();

                        // getting sublocation based sublocation id.
                        $this->deleteSublocation($input, $programId, $citySublocationId);
                    }
                }

                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'providerLocationPrograms', 'tableId' => $locationId,
                    'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
            }
            return response()->json(['message' => trans('messages.deletedSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Delete SubLocation
    public function deleteSublocation($input, $programId, $citySublocationId)
    {
        try {
            $subLocatoinObj = SubLocation::where("entityType", "subLocation")->whereIn('subLocationParent', $citySublocationId)->get();
            $sublocationId = [];
            if (!empty($subLocatoinObj)) {
                foreach ($subLocatoinObj as $sbL) {
                    $sublocationId[] = $sbL->id;
                }
            }
            if (!empty($sublocationId)) {
                ProviderLocationProgram::where("entityType", "subLocation")->whereIn('referenceId', $sublocationId)->where('programId', $programId)->update($input);
                ProviderLocationProgram::where("entityType", "subLocation")->whereIn('referenceId', $sublocationId)->where('programId', $programId)->delete();
                $this->deleteSublocation($input, $programId, $sublocationId);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List Provider Location Program
    public function providerLocationProgramList($request, $id, $locationId)
    {
        try {
            $data = ProviderLocationProgram::where([['referenceId', $locationId], ['providerId', $id]]);
            if ($request->entityType) {
                $data->where('entityType', $request->entityType);
            }
            $data = $data->get();
            return fractal()->collection($data)->transformWith(new ProviderLocationProgramTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Provider Location Count
    public function locationUpdate($request, $id)
    {
        try {
            $count = array();
            if ($request->countLocations == true) {
                $data = Provider::where('id', $id)->first();
                $count['countLocations'] = $data->countLocations + 1;
            }
            $count['updatedBy'] = Auth::id();
            Provider::where('id', $id)->update($count);
            return response()->json(['message' => trans('messages.updatedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }


    // Provider Group
    public function providerGroupList($request, $id, $groupId)
    {
        try {
            $data = Group::where('providerId', $id)->get();
            return fractal()->collection($data)->transformWith(new GroupTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
