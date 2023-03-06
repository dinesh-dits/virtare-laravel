<?php

namespace App\Transformers\Patient;

use League\Fractal\TransformerAbstract;

class PatientDeviceTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data)
	{
		return [
			'id'=>$data->udid,
			'otherDevice' => $data->otherDevice->name,
			'otherDeviceId' => $data->otherDevice->id,
            'status'=>$data->status,
            'patientId'=>$data->patientId
		];
	}


}
