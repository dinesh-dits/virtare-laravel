<?php

namespace App\Transformers\Patient;

use League\Fractal\TransformerAbstract;
use App\Transformers\User\UserTransformer;

class PatientResponsibleTransformer extends TransformerAbstract
{
	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data): array
	{
		return [
			'id'=>$data->udid,
            'self'=>$data->self,
            'relation'=>(!empty($data->relation))?@$data->relation->name:'',
            'relationId'=>(!empty($data->relation))?@$data->relation->id:'',
            'gender'=>(!empty($data->gender))?@$data->gender->name:'',
            'genderId'=>(!empty($data->gender))?$data->gender->id:'',
			'firstName'=>(!empty($data->firstName))?$data->firstName:'',
			'middleName'=>(!empty($data->middleName))?$data->middleName:'',
			'lastName'=>(!empty($data->lastName))?$data->lastName:'',
			'contactType'=>(!empty($data->contactType) && $data->contactType!="[]") ? json_decode($data->contactType) : [],
			'contactTime'=>(!empty($data->contactTime) && $data->contactTime!="[]") ? json_decode($data->contactTime) : [],
			'phoneNumber'=>(!empty($data->phoneNumber))?$data->phoneNumber:'',
			'email'=>(!empty($data->email))?$data->email:'',
			'fullName'=>str_replace("  "," ", ucfirst($data->lastName) .','. ' ' . ucfirst($data->middleName) . ' ' . ucfirst($data->firstName)),
		];
	}
}
