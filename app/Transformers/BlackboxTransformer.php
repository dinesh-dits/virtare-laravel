<?php

namespace App\Transformers;

use App\Transformers\Patient\PatientGoalTransformer;
use League\Fractal\TransformerAbstract;
use App\Transformers\Patient\PatientVitalTransformer;

class BlackboxTransformer extends TransformerAbstract
{
	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data): array
	{
		return [
			'id' => $data->udid,
			'vital' => ucwords(str_replace("_", " ", $data->vital)),
			'value' => json_decode($data->value,true),
			'takeTime' => strtotime($data->takeTime),
			'requestString' => json_decode($data->request,true),
		];
	}
}
