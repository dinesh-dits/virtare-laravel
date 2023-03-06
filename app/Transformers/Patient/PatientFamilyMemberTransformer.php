<?php

namespace App\Transformers\Patient;

use Illuminate\Support\Facades\URL;
use League\Fractal\TransformerAbstract;
use App\Transformers\User\UserTransformer;

class PatientFamilyMemberTransformer extends TransformerAbstract
{
	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];
	public function __construct($showData = true)
	{
		$this->showData = $showData;
	}
	public function transform($data): array
	{
		if($data->lastName){
			$fullName=str_replace("  ", " ", ucfirst($data->lastName) . ',' . ' ' . ucfirst($data->firstName) . ' ' . ucfirst($data->middleName));
		}else{
			$fullName=str_replace("  ", " ", ucfirst($data->firstName) . ' ' . ucfirst($data->middleName));
		}
		return [
			'id' => $data->id,
			'sipId' => "UR" . $data->userId,
			'udid' => $data->udid,
			'patientId' => (!empty($data->patientId)) ? $data->patientId : '',
			'fullName' => $fullName,
			'firstName' => (!empty($data->firstName)) ? ucfirst($data->firstName) : '',
			'middleName' => (!empty($data->middleName)) ? ucfirst($data->middleName) : '',
			'lastName' => (!empty($data->lastName)) ? ucfirst($data->lastName) : '',
			'gender' => (!empty($data->gender->name)) ? $data->gender->name : '',
			'genderId' => (!empty($data->gender->id)) ? $data->gender->id : '',
			'phoneNumber' => (!empty($data->phoneNumber)) ? $data->phoneNumber : '',
			'contactType' => (!empty($data->contactTypeId)) ? $data->contactTypeId : json_encode(array()),
			'contactTime' => (!empty($data->contactTime->name)) ? $data->contactTime->name : json_encode(array()),
			'contactTimeId' => (!empty($data->contactTimeId)) ? $data->contactTimeId : json_encode(array()),
			'relation' => (!empty($data->relation->name)) ? $data->relation->name : '',
			'relationId' => (!empty($data->relationId)) ? $data->relationId : '',
			'isPrimary' => (!empty($data->isPrimary)) ? $data->isPrimary : 0,
			'vital' => (!empty($data->vital)) ? $data->vital : 0,
			'message' => (!empty($data->messages)) ? $data->messages : 0,
			'email' => (!empty($data->user->email)) ? $data->user->email : '',
			'emergencyEmail' => (!empty($data->email)) ? $data->email : 0,
			'sameAsPrimary' => (!empty($data->sameAsPrimary)) ? $data->sameAsPrimary : 0,
			'user' => ($data->user) && ($this->showData) ? fractal()->item($data->user)->transformWith(new UserTransformer())->toArray() : [],
		];
	}
}
