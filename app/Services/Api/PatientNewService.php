<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use App\Models\User\User;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use App\Models\Address\Address;
use App\Models\Contact\Contact;
use App\Models\Patient\Patient;
use App\Events\SetUpPasswordEvent;
use App\Models\Dashboard\Timezone;
use Illuminate\Support\Facades\Auth;
use App\Models\Patient\PatientProvider;
use App\Models\Patient\PatientTimeLine;
use App\Models\Patient\PatientInsurance;
use App\Models\PatientNew\PatientDetail;
use App\Transformers\PatientNew\PatientNewTransformer;
use App\Transformers\PatientNew\PatientNewDetailTransformer;
use Carbon\Carbon;

class PatientNewService
{
    // Add Patient
    public function patientAdd($request)
    {
        try {
            // Add User Data
            $data = $this->addUser($request->input('user'));
            if ($data == false) {
                return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
            // Add Patient Detail
            $patientDetail = $this->addPatientDetail($request, $data);
            if ($patientDetail === 0) {
                return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
            // Add Patient Basic Info
            if (isset($request->contact) && !empty($request->input('contact'))) {
                $contact = $this->addContact($data, $request->contact, 'Patient');
                if ($contact === 0) {
                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                }
            }
            // Add Patient Address
            if (isset($request->address) && !empty($request->input('address'))) {
                $address = $this->addAddress($data, $request->address);
                if ($address === 0) {
                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                }
            }
            // Add Patient Insurance
            if (isset($request->insurance) && !empty($request->input('insurance'))) {
                $insurance = $this->addInsurance($data, $request);
                if ($insurance === 0) {
                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                }
            }
            // Add Patient Care Team
            if (isset($request->careTeam) && !empty($request->input('careTeam'))) {
                $careTeam = $this->addCareTeam($data, $request->careTeam);
                if ($careTeam === 0) {
                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                }
            }
            $patientDetail = User::where(['udid' => $data])->first();
            $userData = fractal()->item($patientDetail)->transformWith(new PatientNewDetailTransformer())->toArray();
            $message = ['message' => trans('messages.createdSuccesfully')];
            return array_merge($message, $userData);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Patient Login Info in users Table
    public function addUser($data)
    {
        try {
            $timeZone = Timezone::where('udid', $data['timeZoneId'])->first();
            $user = [
                'password' => '', 'email' => $data['email'], 'udid' => Str::uuid()->toString(), 'timeZoneId' => $timeZone->id, 'roleId' => 4,
                'emailVerify' => 1, 'createdBy' => Auth::id(), 'bitrixId' => $data['bitrixId']
            ];
            $userData = User::create($user);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => $userData->id,
                'value' => json_encode($user), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(),
            ];
            ChangeLog::create($changeLog);
            if (!$userData) {
                return 0;
                // return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
            return $userData->udid;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Patient Details in patientDetails Table
    public function addPatientDetail($request, $id)
    {
        try {
            $patient = [
                'primaryLanguageId' => $request->input('primaryLanguageId'), 'secondaryLanguageId' => $request->input('secondaryLanguageId'), 'userId' => $id, 'medicalRecordNumber' => "",
                'contactMethodId' => $request->input('contactMethodId'), 'bestTimeToCallId' => $request->input('bestTimeToCallId'), 'createdBy' => Auth::id(),
                'height' => $request->input('height'), 'weight' => $request->input('weight'), 'udid' => Str::uuid()->toString(), 'placeOfServiceId' => $request->input('placeOfServiceId'),
            ];
            $newData = PatientDetail::create($patient);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientDetails', 'tableId' => $newData->id,
                'value' => json_encode($patient), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(),
            ];
            ChangeLog::create($changeLog);
            if (!$newData) {
                return 0;
                // return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
            $medicalRecordNumber = "VH" . date('y') . str_pad($newData->id, 8, "0", STR_PAD_LEFT);
            Patient::where("id", $newData->id)->update(['medicalRecordNumber' => $medicalRecordNumber]);
            $timeLine = [
                'patientId' => $newData->id, 'heading' => 'Patient Intake', 'title' => $newData->lastName . ',' . ' ' . $newData->firstName . ' ' . $newData->middleName . ' ' . 'Added to platform', 'type' => 1,
                'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString()
            ];
            PatientTimeLine::create($timeLine);
            if (isset($postData["phoneNumber"]) && !empty($postData["phoneNumber"])) {
                // $msgSMSObj = ConfigMessage::where("type", "patientAdd")
                //     ->where("entityType", "sendSMS")
                //     ->first();
                // $variablesArr = array(
                //     "password" => $password
                // );
                // $message = '';
                // $message .= "<p>Hi " . $request->input('firstName') . '' . $request->input('lastName') . ",</p>";
                // $message .= "<p>Your account was successfully created with Virtare Health. Your password is " . $password . "</p>";
                // $message .= "<p>Thanks</p>";
                // $message .= "<p>Virtare Health</p>";
                // if (isset($msgSMSObj->messageBody)) {
                //     $messageBody = $msgSMSObj->messageBody;
                //     $message = Helper::getMessageBody($messageBody, $variablesArr);
                // }
                // $responseApi = Helper::sendBandwidthMessage($message, $postData["phoneNumber"]);
            }
            $emailData = [
                'email' => $request->input('email'),
                'firstName' => $request->input('firstName'),
                'template_name' => 'welcome_email'
            ];
            event(new SetUpPasswordEvent($emailData));
            return $newData->id;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Patient Basic Info in contacts Table
    public function addContact($id, $data, $entityType)
    {
        try {
            $user = [
                'udid' => Str::uuid()->toString(), 'roleId' => 4, 'firstName' => $data['firstName'], 'middleName' => $data['middleName'], 'lastName' => $data['lastName'],
                'createdBy' => Auth::id(), 'phoneNumber' => $data['phoneNumber'], 'dob' => $data['dob'], 'genderId' => $data['genderId'], 'entityType' => $entityType,
                'referenceId' => $id
            ];
            $data = Contact::create($user);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'contacts', 'tableId' => $data->id,
                'value' => json_encode($user), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(),
            ];
            ChangeLog::create($changeLog);
            if (!$data) {
                return 0;
                // return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
            return $data->udid;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Patient Address in address Table
    public function addAddress($id, $data)
    {
        try {
            $user = [
                'udid' => Str::uuid()->toString(), 'line1' => $data['line1'], 'line2' => $data['line2'], 'stateId' => $data['stateId'], 'city' => $data['city'],
                'createdBy' => Auth::id(), 'zipCode' => $data['zipCode'], 'userId' => $id
            ];
            $data = Address::create($user);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'address', 'tableId' => $data->id,
                'value' => json_encode($user), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(),
            ];
            ChangeLog::create($changeLog);
            if (!$data) {
                return 0;
                // return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
            return $data->udid;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Patient Insurance into patientInsurances Table
    public function addInsurance($id, $data)
    {
        try {
            $ipInfo = file_get_contents('http://ip-api.com/json/' . $data->ip());
            $ipInfo = json_decode($ipInfo);
            $timezone = 'UTC';
            if (isset($ipInfo->timezone)) {
                $timezone = $ipInfo->timezone;
            }
            $startDate = Helper::dateOnly($data->insurance['startDate'], $timezone);
            $endDate = Helper::dateOnly($data->insurance['endDate'], $timezone);
            $input = [
                'insuranceNameId' => $data->insurance['insuranceNameId'], 'insuranceNumber' => $data->insurance['insuranceNumber'], 'startDate' => $startDate, 'expirationDate' => $endDate,
                'udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'patientId' => $id
            ];
            $insurance = new PatientInsurance();
            $insurance = $insurance->insuranceAdd($input);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientInsurances', 'tableId' => $insurance->id,
                'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(),
            ];
            ChangeLog::create($changeLog);
            if (!$insurance) {
                return 0;
                // return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Add Patient CareTeam into patientProviders Table
    public function addCareTeam($id, $data)
    {
        try {
            $input = ['providerId' => $data, 'patientId' => $id, 'udid' => Str::uuid()->toString(), 'createdBy' => Auth::id()];
            $careTeam = new PatientProvider();
            $careTeam = $careTeam->careTeamAdd($input);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientProviders', 'tableId' => $careTeam->id,
                'value' => json_encode($input), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id(),
            ];
            ChangeLog::create($changeLog);
            if (!$careTeam) {
                return 0;
                // return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // List All or By Detail Patient
    public function patientList($request, $id)
    {
        try {
            if (!$id) {
                $patient = User::where(['roleId' => 4])->get();
                return fractal()->collection($patient)->transformWith(new PatientNewTransformer())->toArray();
            }
            $patient = User::where(['roleId' => 4, 'udid' => $id])->first();
            return fractal()->item($patient)->transformWith(new PatientNewDetailTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }








    // Update Patient
    public function patientUpdate($request, $id)
    {
        try {
            // Add User Data
            $user = $this->updateUser($id, $request->input('user'));
            if ($user === 0) {
                return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
            // Add Patient Detail
            $patientDetail = $this->updatePatientDetail($request, $id);
            if ($patientDetail === 0) {
                return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
            // Add Patient Basic Info
            if (isset($request->contact) && !empty($request->input('contact'))) {
                $contact = $this->updateContact($id, $request->contact, 'Patient');
                if ($contact === 0) {
                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                }
            }
            // Add Patient Address
            if (isset($request->address) && !empty($request->input('address'))) {
                $address = $this->updateAddress($id, $request->address);
                if ($address === 0) {
                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                }
            }
            // Add Patient Insurance
            if (isset($request->insurance) && !empty($request->insurance)) {
                $insurance = $this->updateInsurance($id, $request);
                if ($insurance === 0) {
                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                }
            }
            // Add Patient Care Team
            if (isset($request->careTeam) && !empty($request->careTeam)) {
                $careTeam = $this->updateCareTeam($id, $request->careTeam);
                if ($careTeam === 0) {
                    return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
                }
            }
            $patientDetail = User::where(['udid' => $id])->first();
            $userData = fractal()->item($patientDetail)->transformWith(new PatientNewDetailTransformer())->toArray();
            $message = ['message' => trans('messages.updatedSuccesfully')];
            return array_merge($message, $userData);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Patient Login Info
    public function updateUser($id, $data)
    {
        try {
            $timeZone = Timezone::where('udid', $data['timeZoneId'])->first();
            $user = [
                'email' => $data['email'], 'timeZoneId' => $timeZone->id, 'updatedBy' => Auth::id(), 'bitrixId' => $data['bitrixId']
            ];
            User::where(['udid' => $id])->update($user);
            $data = User::where(['udid' => $id])->first();
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'users', 'tableId' => $data->id,
                'value' => json_encode($user), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(),
            ];
            ChangeLog::create($changeLog);
            if (!$data) {
                return 0;
                // return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
            return $data->udid;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Patient Details
    public function updatePatientDetail($request, $id)
    {
        try {
            $patient = [
                'primaryLanguageId' => $request->input('primaryLanguageId'), 'secondaryLanguageId' => $request->input('secondaryLanguageId'),
                'contactMethodId' => $request->input('contactMethodId'), 'bestTimeToCallId' => $request->input('bestTimeToCallId'), 'updatedBy' => Auth::id(),
                'height' => $request->input('height'), 'weight' => $request->input('weight'), 'placeOfServiceId' => $request->input('placeOfServiceId'),
            ];
            PatientDetail::where(['userId' => $id])->update($patient);
            $newData = PatientDetail::where(['userId' => $id])->first();
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientDetails', 'tableId' => $newData->id,
                'value' => json_encode($patient), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(),
            ];
            ChangeLog::create($changeLog);
            if (!$newData) {
                return 0;
                // return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
            $timeLine = [
                'patientId' => $newData->id, 'heading' => 'Patient Intake', 'title' => $newData->lastName . ',' . ' ' . $newData->firstName . ' ' . $newData->middleName . ' ' . 'Added to platform', 'type' => 1,
                'createdBy' => Auth::id(), 'udid' => Str::uuid()->toString()
            ];
            PatientTimeLine::create($timeLine);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Patient Basic Info
    public function updateContact($id, $data, $entityType)
    {
        try {
            $user = [
                'firstName' => $data['firstName'], 'middleName' => $data['middleName'], 'lastName' => $data['lastName'],
                'updatedBy' => Auth::id(), 'phoneNumber' => $data['phoneNumber'], 'dob' => $data['dob'], 'genderId' => $data['genderId']
            ];
            Contact::where(['referenceId' => $id, 'entityType' => $entityType])->update($user);
            $data = Contact::where(['referenceId' => $id, 'entityType' => $entityType])->first();
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'contacts', 'tableId' => $data->id,
                'value' => json_encode($user), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(),
            ];
            ChangeLog::create($changeLog);
            if (!$data) {
                return 0;
                // return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
            return $data->udid;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Patient Address
    public function updateAddress($id, $data)
    {
        try {
            $user = [
                'line1' => $data['line1'], 'line2' => $data['line2'], 'stateId' => $data['stateId'], 'city' => $data['city'],
                'updatedBy' => Auth::id(), 'zipCode' => $data['zipCode']
            ];
            Address::where(['userId' => $id])->update($user);
            $data = Address::where(['userId' => $id])->first();
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'address', 'tableId' => $data->id,
                'value' => json_encode($user), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(),
            ];
            ChangeLog::create($changeLog);
            if (!$data) {
                return 0;
                // return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
            return $data->udid;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Patient Insurance
    public function updateInsurance($id, $data)
    {
        try {
            $ipInfo = file_get_contents('http://ip-api.com/json/' . $data->ip());
            $ipInfo = json_decode($ipInfo);
            $timezone = 'UTC';
            if (isset($ipInfo->timezone)) {
                $timezone = $ipInfo->timezone;
            }
            $startDate = Helper::dateOnly($data->insurance['startDate'], $timezone);
            $endDate = Helper::dateOnly($data->insurance['endDate'], $timezone);
            $input = [
                'insuranceNameId' => $data->insurance['insuranceNameId'], 'insuranceNumber' => $data->insurance['insuranceNumber'], 'startDate' => $startDate, 'expirationDate' => $endDate,
                'updatedBy' => Auth::id(), 'patientId' => $id
            ];
            $insurance = new PatientInsurance();
            $insurance = $insurance->insuranceAdd($input);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientInsurances', 'tableId' => $insurance->id,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(),
            ];
            ChangeLog::create($changeLog);
            if (!$insurance) {
                return;
                // return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Update Patient CareTeam
    public function updateCareTeam($id, $data)
    {
        try {
            $input = ['providerId' => $data, 'patientId' => $id, 'updatedBy' => Auth::id()];
            $careTeam = new PatientProvider();
            $careTeam = $careTeam->careTeamAdd($input);
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'patientProviders', 'tableId' => $careTeam->id,
                'value' => json_encode($input), 'type' => 'updated', 'ip' => request()->ip(), 'createdBy' => Auth::id(),
            ];
            ChangeLog::create($changeLog);
            if (!$careTeam) {
                return 0;
                // return response()->json(['message' => trans('messages.INTERNAL_ERROR')], 500);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }





    // Delete Patient
    public function patientDelete($request, $id)
    {
        try {
            $data = $this->inputRequest();
            $tables = [
                User::where('udid', $id),
                PatientDetail::where('userId', $id),
                Contact::where(['referenceId' => $id, 'entityType' => 'Patient']),
                PatientInsurance::where(['patientId' => $id]),
                Address::where(['userId' => $id]),
            ];
            foreach ($tables as $table) {
                $table->update($data);
            }
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Common Function of delete input
    public function inputRequest()
    {
        try {
            $input = ['isActive' => 0, 'isDelete' => 1, 'deletedBy' => Auth::id(), 'deletedAt' => Carbon::now()];
            return $input;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }


    // Get Assigned Inventory to Patient
    // public function inventoryPatientAssign($request,$id)
    // {
    //     try {
    //        $inventory=PatientInventory
    //     } catch (Exception $e) {
    //         throw new \RuntimeException($e);
    //     }
    // }
}
