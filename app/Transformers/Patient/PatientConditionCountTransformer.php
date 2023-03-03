<?php

namespace App\Transformers\Patient;

use League\Fractal\TransformerAbstract;

class PatientConditionCountTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data): array
	{
		return [
            'text'=>$data->flags->name,
            'count'=>$data['count'],
		];
	}


}