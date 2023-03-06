<?php

namespace App\Transformers\Patient;

use League\Fractal\TransformerAbstract;

class NewPatientCountTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data)
	{
		return [
			'total' => $data->total,
			'duration'=>strtotime($data->duration),
			'time'=>@$data->time,
		];
	}


}