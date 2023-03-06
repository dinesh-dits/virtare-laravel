<?php

namespace App\Transformers\Family;

use Illuminate\Support\Facades\URL;
use League\Fractal\TransformerAbstract;
use App\Transformers\GlobalCode\GlobalCodeTransformer;


class FamilyPatientTransformer extends TransformerAbstract
{
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
     * @return array
     */
    public function transform($data): array
    {
        return [
            'id' => $data->patientId,
            'udid' => $data->patient->udid,
            'name' => ucfirst($data->patients->firstName) . ' ' . ucfirst($data->patients->lastName),
            'dob' => $data->patients->dob,
            'email'=>$data->patients->user->email,
            'nickname'=>$data->patients->nickName,
            'relation' => $data->relation ? $data->relation->name:'',
            'gender' => $data->patients->gender ? $data->patients->gender->name:'',
            'language' =>$data->patients->language ?  $data->patients->language->name:'',
            'phoneNumber' => $data->patients->phoneNumber,
            'profilePhoto' => (!empty($data->patients->user->profilePhoto)) && (!is_null($data->patients->user->profilePhoto)) ? str_replace("public", "", URL::to('/')) . '/' . $data->patients->user->profilePhoto : "",
            'medicalRecordNumber' => $data->patients->medicalRecordNumber,
            'country' => $data->patients->country ? $data->patients->country->name:'',
            'state' =>$data->patients->state ? $data->patients->state->name:'',
            'city' => $data->patients->city,
            'zipCode' => $data->patients->zipCode,
            'appartment' => $data->patients->appartment,
            'address' => $data->patients->address,
            'isDeviceAdded' => $data->patients->isDeviceAdded,
            'isPrimary' => $data->isPrimary,
            'vital' => (!empty($data->vital)) ? $data->vital : '',
            'message' => (!empty($data->messages)) ? $data->messages : '',
            'isActive' => $data->patients->isActive,
        ];
    }
}
