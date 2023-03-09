<?php

namespace App\Transformers\PatientNew;

use App\Models\User\User;
use App\Models\Address\Address;
use App\Models\Contact\Contact;
use League\Fractal\TransformerAbstract;

class PatientNewDetailTransformer extends TransformerAbstract
{


    protected $showData;

    public function __construct($showData = true)
    {
        $this->showData = $showData;
    }
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        //
    ];

    /**
     * A Fractal transformer.
     *
     * 
     * @return array
     */

    public function getAddress($userId)
    {
        $address = Address::where(['userId' => $userId])->first();
        if (!$address) {
            return [];
        }
        return [
            "line1" => $address->line1,
            "line2" => $address->line2,
            "stateId" => (!empty($address->state)) ? $address->state->id : '',
            "state" => (!empty($address->state)) ? $address->state->name : '',
            "city" => $address->city,
            "zipCode" => $address->zipCode
        ];
    }

    public function getUser($userId)
    {
        $users = User::where(['udid' => $userId])->first();
        if (!$users) {
            return [];
        }
        return [
            "email" => $users->email,
            "timeZoneId" => (!empty($users->timeZone)) ?  $users->timeZone->id : '',
            "timeZone" => (!empty($users->timeZone)) ?  $users->timeZone->timeZone : '',
            "bitrixId" => $users->bitrixId,
        ];
    }

    public function getContact($userId)
    {
        $users = Contact::where(['referenceId' => $userId])->first();
        if (!$users) {
            return [];
        }
        return [
            "firstName" => $users->firstName,
            "middleName" => $users->middleName,
            "lastName" => $users->lastName,
            "dob" => $users->dob,
            "phoneNumber" => $users->phoneNumber,
            "genderId" => (!empty($users->gender)) ? $users->gender->id : '',
            "gender" => (!empty($users->gender)) ? $users->gender->name : "",
        ];
    }

    public function transform($data): array
    {
        return [
            'udid' => $data->udid,
            'primaryLanguageId' => (!empty($data->patientDetail)) ? $data->patientDetail->primary->id : '',
            'primaryLanguage' => (!empty($data->patientDetail)) ?  $data->patientDetail->primary->name : '',
            'secondaryLanguageId' => (!empty($data->patientDetail)) ? $data->patientDetail->secondary->id : '',
            'secondaryLanguage' => (!empty($data->patientDetail)) ? $data->patientDetail->secondary->name : '',
            'contactMethodId' => (!empty($data->patientDetail)) ? $data->patientDetail->contactMethod->id : '',
            'contactMethod' => (!empty($data->patientDetail)) ? $data->patientDetail->contactMethod->name : '',
            'bestTimeToCallId' => (!empty($data->patientDetail)) ? $data->patientDetail->bestTimeToCall->id : '',
            'bestTimeToCall' => (!empty($data->patientDetail)) ? $data->patientDetail->bestTimeToCall->name : '',
            'height' => (!empty($data->patientDetail)) ? $data->patientDetail->height : '',
            'weight' => (!empty($data->patientDetail)) ? $data->patientDetail->weight : '',
            'nickName' => (!empty($data->patientDetail)) ? $data->patientDetail->nickName : '',
            'placeOfServiceId' => (!empty($data->patientDetail)) ? $data->patientDetail->placeOfService->id : '',
            'placeOfService' => (!empty($data->patientDetail)) ? $data->patientDetail->placeOfService->name : '',
            'user' => $this->getUser($data->udid),
            'contact' => $this->getContact($data->udid),
            'address' => $this->getAddress($data->udid),
        ];
    }
}
