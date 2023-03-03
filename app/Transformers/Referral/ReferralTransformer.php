<?php

namespace App\Transformers\Referral;

use League\Fractal\TransformerAbstract;

class ReferralTransformer extends TransformerAbstract
{
	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data): array
	{
		if(isset($data->patientUdid) || isset($data->patientFirstName)){
			return [
				'referralId' => $data->udid,
				'firstName' => (!empty($data->firstName)) ? ucfirst($data->firstName) : '',
				'middleName' => (!empty($data->middleName)) ? ucfirst($data->middleName) : '',
				'lastName' => (!empty($data->lastName)) ? ucfirst($data->lastName) : '',
				'name' => ucfirst($data->lastName) . ',' . ' ' . ucfirst($data->firstName) . ' ' . ucfirst($data->middleName),
				'phoneNumber' => (!empty($data->phoneNumber)) ? $data->phoneNumber : '',
				'email' => (!empty($data->email)) ? $data->email : '',
				'fax' => (!empty($data->fax)) ? $data->fax : "'",
				'patientId' => (!empty($data->patientUdid)) ? $data->patientUdid : "'",
				'patientName' => str_replace("  "," ", ucfirst($data->patientLastName) . ',' . ' ' . ucfirst($data->patientFirstName) . ' ' . ucfirst($data->patientMiddleName)),
			];
		}else{
			return [
				'id' => $data->udid,
				'firstName' => (!empty($data->firstName)) ? ucfirst($data->firstName) : '',
				'middleName' => (!empty($data->middleName)) ? ucfirst($data->middleName) : '',
				'lastName' => (!empty($data->lastName)) ? ucfirst($data->lastName) : '',
				'name' => ucfirst($data->lastName) . ',' . ' ' . ucfirst($data->firstName) . ' ' . ucfirst($data->middleName),
				'phoneNumber' => (!empty($data->phoneNumber)) ? $data->phoneNumber : '',
				'email' => (!empty($data->email)) ? $data->email : '',
				'fax' => (!empty($data->fax)) ? $data->fax : "'",
			];
		}
		
	}
}
