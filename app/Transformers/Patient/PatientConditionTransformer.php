<?php

namespace App\Transformers\Patient;

use League\Fractal\TransformerAbstract;

class PatientConditionTransformer extends TransformerAbstract
{
	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data): array
	{
		return [
			'id' => $data->udid,
			'conditionCode' => $data->condition->code,
			'conditionDescription' => $data->condition->description,
			'conditionId' => $data->conditionId,
			'targetUdid' => $data->conditionId,
			'patientId' => $data->patientId,
			'startDate' => (!empty($data->startDate)) ? strtotime($data->startDate) : '',
			'endDate' => (!empty($data->endDate)) ? strtotime($data->endDate) : '',
		];
	}
}
