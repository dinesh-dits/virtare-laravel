<?php

namespace App\Transformers\Patient;

use App\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use League\Fractal\TransformerAbstract;

class PatientBasicInfoTransformer extends TransformerAbstract
{
    protected $showData;

    public function __construct($showData = true)
    {
        $this->showData = $showData;
    }
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];


    public function transform($data): array
    {
        return [
            'id' => $data->udid,
            'sipId' => "UR" . @$data->user->id,
            'firstName' => ucfirst($data->firstName),
            'middleName' => (!empty($data->middleName)) ? ucfirst($data->middleName) : '',
            'lastName' => ucfirst($data->lastName),
            'fullName' => str_replace("  ", " ", ucfirst($data->lastName) . ',' . ' ' . ucfirst($data->firstName) . ' ' . ucfirst($data->middleName)),
            'email' => @$data->user->email,
            'dob' => $data->dob,
            
            'age' => Helper::age($data->dob),
           // 'genderName' => (!empty($data->gender->name)) ? $data->gender->name : '',
           // 'gender' => (!empty($data->genderId)) ? $data->genderId : '',
           // 'genderId' => (!empty($data->genderId)) ? $data->genderId : '',
            //'language' => (!empty($data->language->name)) ? $data->language->name : '',
            //'languageId' => (!empty($data->languageId)) ? $data->languageId : '',
            //'otherLanguage' => (!empty($data->otherLanguageId) && $data->otherLanguageId != "[]") ? json_decode($data->otherLanguageId) : [],
            'nickName' => (!empty($data->nickName)) ? ucfirst($data->nickName) : '',
            //'height' => (!empty($data->height)) ? $data->height : '',
           // 'heightInCentimeter' => (!empty($data->heightInCentimeter)) ? $data->heightInCentimeter : '',
          //  'weight' => (!empty($data->weight)) ? $data->weight : '',
           // 'phoneNumber' => $data->phoneNumber,
            //'contactType' => (!empty($data->contactTypeId) && $data->contactTypeId != "[]") ? json_decode($data->contactTypeId) : [],
           // 'contactTime' => (!empty($data->contactTime->name)) ? $data->contactTime->name : [],
           // 'contactTimeId' => (!empty($data->contactTimeId) && $data->contactTimeId != "[]") ? json_decode($data->contactTimeId) : [],
            //'country' => (!empty($data->country->name)) ? $data->country->name : '',
           // 'countryId' => (!empty($data->countryId)) ? $data->countryId : '',
           // 'state' => (!empty($data->state->name)) ? $data->state->name : '',
           // 'stateId' => (!empty($data->stateId)) ? $data->stateId : '',
          //  'city' => (!empty($data->city)) ? $data->city : '',
           // 'zipCode' => (!empty($data->zipCode)) ? $data->zipCode : '',
           // 'bitrixId' => (!empty($data->bitrixId)) ? $data->bitrixId : '',
            //'appartment' => (!empty($data->appartment)) ? $data->appartment : '',
           // 'address' => (!empty($data->address)) ? $data->address : '',
            'isActive' => $data->isActive == 1 ? 'Active' : 'Inactive',
            //'nonCompliance' => $data->nonCompliance==1?'Yes':'No',  
            'medicalRecordNumber' => (!empty($data->medicalRecordNumber)) ? $data->medicalRecordNumber : '',
            'profilePhoto' => (!empty($data->user->profilePhoto)) && (!is_null($data->user->profilePhoto)) ? Storage::disk('s3')->temporaryUrl($data->user->profilePhoto,Carbon::now()->addDays(5)) : "",
        ];
    }
}
