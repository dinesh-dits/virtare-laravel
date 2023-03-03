<?php

namespace App\Transformers\Patient;

use App\Helper;
use Carbon\Carbon;
use App\Models\Provider\Provider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use League\Fractal\TransformerAbstract;
use App\Transformers\Referral\ReferralTransformer;

class PatientDetailTransformer extends TransformerAbstract
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
        $proName = "";
        $proId = "";
        if(isset($data->patientReferral->providerId)){
            $pro = Provider::where("id",$data->patientReferral->providerId)->first();
            if(isset($pro->id)){
                $proName = $pro->name;
                $proId = $pro->udid;
            }
        }
        return [
            'id' => $data->udid,
            'sipId' => "UR" . @$data->user->id,
            'firstName' => ucfirst($data->firstName),
            'middleName' => (!empty($data->middleName)) ? ucfirst($data->middleName) : '',
            'lastName' => ucfirst($data->lastName),
            'fullName' => str_replace("  ", " ", ucfirst($data->lastName) . ',' . ' ' . ucfirst($data->firstName) . ' ' . ucfirst($data->middleName)),
            'email' => $data->isApp == 1 ? @$data->user->userDefined ? @$data->user->email : '' : @$data->user->email,
            'emailUserDefined' => $data->isApp == 1 ? @$data->user->userDefined ? @$data->user->userDefined : 0 : 0,
            'dob' => $data->dob,
            'placeOfService' => (!empty($data->placeOfServiceId)) ? $data->placeOfServiceId : '',
            'age' => Helper::age($data->dob),
            'genderName' => (!empty($data->gender->name)) ? $data->gender->name : '',
            'gender' => (!empty($data->genderId)) ? $data->genderId : '',
            'genderId' => (!empty($data->genderId)) ? $data->genderId : '',
            'language' => (!empty($data->language->name)) ? $data->language->name : '',
            'languageId' => (!empty($data->languageId)) ? $data->languageId : '',
            'otherLanguage' => (!empty($data->otherLanguageId) && $data->otherLanguageId != "[]") ? json_decode($data->otherLanguageId) : [],
            'nickName' => (!empty($data->nickName)) ? ucfirst($data->nickName) : '',
            'height' => (!empty($data->height)) ? $data->height : '',
            'heightInCentimeter' => (!empty($data->heightInCentimeter)) ? $data->heightInCentimeter : '',
            'weight' => (!empty($data->weight)) ? $data->weight : '',
            'phoneNumber' => $data->isApp == 1 ? @$data->userDefined ? $data->phoneNumber : '' : $data->phoneNumber,
            'phoneUserDefined' => $data->isApp == 1 ? @$data->userDefined ? @$data->userDefined : 0 : 0,
            'contactType' => (!empty($data->contactTypeId) && $data->contactTypeId != "[]") ? json_decode($data->contactTypeId) : [],
            'contactTime' => (!empty($data->contactTime->name)) ? $data->contactTime->name : [],
            'contactTimeId' => (!empty($data->contactTimeId) && $data->contactTimeId != "[]") ? json_decode($data->contactTimeId) : [],
            'country' => (!empty($data->country->name)) ? $data->country->name : '',
            'countryId' => (!empty($data->countryId)) ? $data->countryId : '',
            'state' => (!empty($data->state->name)) ? $data->state->name : '',
            'stateId' => (!empty($data->stateId)) ? $data->stateId : '',
            'city' => (!empty($data->city)) ? $data->city : '',
            'zipCode' => (!empty($data->zipCode)) ? $data->zipCode : '',
            'bitrixId' => (!empty($data->bitrixId)) ? $data->bitrixId : '',
            'appartment' => (!empty($data->appartment)) ? $data->appartment : '',
            'address' => (!empty($data->address)) ? $data->address : '',
            'isActive' => $data->isActive == 1 ? 'Active' : 'Inactive',
            'nonCompliance' => $data->nonCompliance == 1 ? 'Yes' : 'No',
            'medicalRecordNumber' => (!empty($data->medicalRecordNumber)) ? $data->medicalRecordNumber : '',
            'profilePhoto' => (!empty($data->user->profilePhoto)) && (!is_null($data->user->profilePhoto)) ? Storage::disk('s3')->temporaryUrl($data->user->profilePhoto, Carbon::now()->addDays(5)) : "",
            'patientReferral' => (!empty($data->patientReferral)) ? fractal()->item($data->patientReferral->patientReferral)->transformWith(new ReferralTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():[],
            'patientReferralProviderName' => $proName,
            'patientReferralProviderId' => $proId,
        ];
    }
}
