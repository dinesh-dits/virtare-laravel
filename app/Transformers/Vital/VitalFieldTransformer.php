<?php

namespace App\Transformers\Vital;

use App\Transformers\Patient\PatientGoalTransformer;
use League\Fractal\TransformerAbstract;
use App\Transformers\Patient\PatientVitalTransformer;

class VitalFieldTransformer extends TransformerAbstract
{
	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data)
	{
		return [
			'vitalFieldId' => $data->vitalFieldId,
			'goals'=>$data->patientGoal ? fractal()->collection($data->patientGoal)->transformWith(new PatientGoalTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray(): [],
		];
	}
}
