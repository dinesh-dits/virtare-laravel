<?php

namespace App\Transformers\Patient;

use App\Models\Patient\PatientFlag;
use App\Models\Patient\PatientVital;
use League\Fractal\TransformerAbstract;
use App\Transformers\Patient\PatientFlagTransformer;

class PatientTimeLineTransformer extends TransformerAbstract
{
	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data): array
	{
		$entity = new \stdClass();
		if ($data->type == '7' && !is_null($data->refrenceId)) {
			$entityData = PatientFlag::where('id', $data->refrenceId)->withTrashed()->first();
			if (!empty($entityData)) {
				$entity = fractal()->item($entityData)->transformWith(new PatientFlagTransformer())->toArray();
			}
		}

		if ($data->type == '4' || $data->type == '10') {
			$entityData = PatientVital::where('id', $data->refrenceId)->withTrashed()->first();
			if (!empty($entityData)) {
				$entity = fractal()->item($entityData)->transformWith(new PatientVitalTransformer())->toArray();
			}
		}

		return [
			'id' => $data->udid,
			'heading' => $data->heading,
			'title' => $data->title,
			'type' => $data->type,
			'entity' => $entity,
			'createdAt' => strtotime($data->createdAt)
		];
	}
}
