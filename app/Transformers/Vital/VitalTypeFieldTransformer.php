<?php

namespace App\Transformers\Vital;

use Illuminate\Support\Facades\URL;
use League\Fractal\TransformerAbstract;

class VitalTypeFieldTransformer extends TransformerAbstract
{
	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data): array
	{
		return [
			'id' => $data->vitalFieldId,
			'field' => $data->VitalField->name,
			'icon' => (!empty($data->vitalField->icon)) && (!is_null($data->vitalField->icon)) ? str_replace("public", "", URL::to('/')) . '/' . $data->vitalField->icon : "",
		];
	}
}
