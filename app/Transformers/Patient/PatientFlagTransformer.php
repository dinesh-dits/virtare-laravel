<?php

namespace App\Transformers\Patient;

use Illuminate\Support\Facades\URL;
use League\Fractal\TransformerAbstract;
use App\Transformers\Flag\FlagTransformer;

class PatientFlagTransformer extends TransformerAbstract
{

	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data): array
	{
		if (isset($data->flagName) && !empty($data->flagName)) {
			$flagName = $data->flagName;
		} elseif (isset($data->flag->name) && !empty($data->flag->name)) {
			$flagName = $data->flag->name;
		} else {
			$flagName = "";
		}

		if (isset($data->flagColor) && !empty($data->flagColor)) {
			$flagColor = $data->flagColor;
		} elseif (isset($data->flag->name) && !empty($data->flag->name)) {
			$flagColor = $data->flag->color;
		} else {
			$flagColor = "";
		}

		return [
			'id' => (!empty($data->udid)) ? $data->udid : $data->patientFlagUdId,
			'patientId' => (!empty($data->patientId)) ? $data->patientId : $data->patientFlagPatientId,
			'icon' => (!empty($data->icon)) && (!is_null($data->icon)) ? URL::to('/') . '/' . $data->icon : "",
			'flags' => (!empty($data->flag)) ? fractal()->item($data->flag)->transformWith(new FlagTransformer())->toArray() : '',
			'flagName' => $flagName,
			'flagColor' => $flagColor,
			'color' => (!empty($data->flag->color)) ? $data->flag->color : '',
			'createdAt' => strtotime($data->createdAt),
			'deletedBy' => (!empty($data->user)) ? ucfirst(@$data->user->staff->lastName) . ',' . ' ' . ucfirst(@$data->user->staff->firstName) : '',
			'deletedById' => (!empty($data->user)) ? @$data->user->staff->udid : '',
			'isDelete' => $data->isDelete,
			'deletedAt' => strtotime($data->deletedAt),
			'reason' => (!empty($data->reason)) ? $data->reason->name : '',
			'reasonId' => (!empty($data->reason)) ? $data->reason->id : '',
			'flagReason' => (!empty($data->flagReason)) ? $data->flagReason->note : ''
		];
	}
}
