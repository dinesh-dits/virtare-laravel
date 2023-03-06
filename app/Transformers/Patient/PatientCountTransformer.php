<?php

namespace App\Transformers\Patient;

use League\Fractal\TransformerAbstract;

class PatientCountTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data)
	{ 
		return [
			'data' => $data,
		];
	}


}