<?php

namespace App\Transformers\Patient;

use App\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use League\Fractal\TransformerAbstract;
use App\Transformers\Patient\PatientProviderTransformer;

class PatientBasicDetailTransformer extends TransformerAbstract
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

        //print_r($data->user->id); 
        $responseKeys = array(
            'udid' => 'id',
            'firstName' => 'firstName',
            'middleName' => 'middleName',
            'lastName' => 'lastName',
            'dob' => 'dob',
            'placeOfServiceId' => 'placeOfService',
            'genderId' => 'genderId',
            'languageId' => 'languageId',
            'otherLanguageId' => 'otherLanguage', //[]
            'nickName' => 'nickName',
            'height' => 'height',
            'heightInCentimeter' => 'heightInCentimeter',
            'weight' => 'weight',
            // 'phoneNumber' => 'phoneNumber',
            'contactTypeId' => 'contactType', //[]
            'contactTimeId' => 'contactTimeId', //[]
            'countryId' => 'countryId',
            'stateId' => 'stateId',
            'city' => 'city',
            'zipCode' => 'zipCode',
            'bitrixId' => 'bitrixId',
            'appartment' => 'appartment',
            'address' => 'address',
            'isActive' => 'isActive', //Active' : 'Inactive',
            'nonCompliance' => 'nonCompliance', //'Yes':'No',  
            'medicalRecordNumber' => 'medicalRecordNumber'
        );

        $datas = $data->getAttributes();
        $detail['sipId'] = "UR" . @$data->user->id;
        $detail['userId'] = @$data->user->id;
        $detail['email'] =  $data->isApp == 1 ? '' : @$data->user->email;
        $detail['phoneNumber'] =  $data->isApp == 1 ? '' : @$data->phoneNumber;
        $detail['fullName'] = str_replace("  ", " ", ucfirst($data['lastName']) . ',' . ' ' . ucfirst($data['firstName']) . ' ' . ucfirst($data['middleName']));
        $detail['genderName'] = (!empty($data->gender->name)) ? $data->gender->name : '';
        $detail['gender'] = (!empty($data->gender->id)) ? $data->gender->id : '';
        $detail['profilePhoto'] = (!empty($data->user->profilePhoto)) && (!is_null($data->user->profilePhoto)) ? Storage::disk('s3')->temporaryUrl($data->user->profilePhoto, Carbon::now()->addDays(5)) : "";
        $detail['contactTime'] = (!empty($data->contactTime->name)) ? $data->contactTime->name : [];
        $detail['language'] = (!empty($data->language->name)) ? $data->language->name : '';
        $detail['country'] = (!empty($data->country->name)) ? $data->country->name : '';
        $detail['state'] = (!empty($data->state->name)) ? $data->state->name : '';
       // $detail['defaultProvider'] = $this->showData && $data->defaultProvider ? fractal()->item($data->defaultProvider)->transformWith(new PatientProviderTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : '';
      //  $detail['providers'] = $this->showData && $data->patientProvider ? fractal()->collection($data->patientProvider)->transformWith(new PatientProviderTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : array();

        // Patient Record data        
        foreach ($datas as $key => $value) {
            $valueDetail = (!empty($value)) ? $value : '';
            if (is_string($valueDetail) && $key != 'udid') {
                $valueDetail = ucfirst($valueDetail);
            }
            if (isset($responseKeys[$key])) {
                if ($key == 'otherLanguageId' || $key == 'contactTypeId' || $key == 'contactTimeId') {
                    $valueDetail =  (!empty($valueDetail) && $valueDetail != "[]") ? json_decode($valueDetail) : [];
                }
                if ($key == 'isActive') {
                    $valueDetail = $valueDetail == 1 ? true : false;
                }
                if ($key == 'nonCompliance') {
                    $valueDetail = $valueDetail == 1 ? 'Yes' : 'No';
                }
                if ($key == 'dob') {
                    $detail['age'] = Helper::age($valueDetail);
                }
                $detail[$responseKeys[$key]] = $valueDetail;
            }
        }
        return $detail;
        /*return [
            'id' => $data->udid,
            'sipId' => "UR" . @$data->user->id,
            'firstName' => ucfirst($data->firstName),
            'middleName' => (!empty($data->middleName)) ? ucfirst($data->middleName) : '',
            'lastName' => ucfirst($data->lastName),
            'fullName' => str_replace("  ", " ", ucfirst($data->lastName) . ',' . ' ' . ucfirst($data->firstName) . ' ' . ucfirst($data->middleName)),
            'email' => @$data->user->email,
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
            'phoneNumber' => $data->phoneNumber,
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
            'nonCompliance' => $data->nonCompliance==1?'Yes':'No',  
            'medicalRecordNumber' => (!empty($data->medicalRecordNumber)) ? $data->medicalRecordNumber : '',
            'profilePhoto' => (!empty($data->user->profilePhoto)) && (!is_null($data->user->profilePhoto)) ? Storage::disk('s3')->temporaryUrl($data->user->profilePhoto,Carbon::now()->addDays(5)) : "",
        ];*/
    }
}
