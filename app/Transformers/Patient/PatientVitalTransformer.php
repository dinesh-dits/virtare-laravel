<?php

namespace App\Transformers\Patient;

use Illuminate\Support\Facades\URL;
use League\Fractal\TransformerAbstract;
class PatientVitalTransformer extends TransformerAbstract
{
	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data): array
	{
		return [
			'id' => $data->udid,
			'vitalField' => @$data->vitalFieldNames->name?$data->vitalFieldNames->name:@$data->vitalField,
			'deviceType' => @$data->deviceType->name?$data->deviceType->name:@$data->deviceType,
			'value' => $data->value,
			'units'=>$data->units,
			'takeTime'=>strtotime($data->takeTime),
			'startTime'=>strtotime($data->startTime),
			'endTime'=>strtotime($data->endTime),
			'addType'=>$data->addType,
			'createdType'=>$data->createdType,
			'comment'=>@$data->note,
			'lastReadingDate'=>$data->createdAt,
			'deviceInfo'=>$data->deviceInfo,
			'icon'=>(!empty(@$data->icon))?str_replace("public", "", URL::to('/')) . '/' .@$data->icon : ((!empty(@$data->icons->icon))?str_replace("public", "", URL::to('/')) . '/' .@$data->icons->icon:""),
			'color'=>(!empty(@$data->color))?@$data->color:((!empty(@$data->icons->color))?@$data->icons->color:""),
			'flagName'=>(!empty(@$data->flagName))?@$data->flagName:'',
			'flagId'=>(!empty(@$data->flagId))?@$data->flagId:''
		];
	}
}
