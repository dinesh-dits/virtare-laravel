<?php

namespace App\Transformers\Patient;

use League\Fractal\TransformerAbstract;
use App\Transformers\User\UserTransformer;

class PatientProgramTransformer extends TransformerAbstract
{
	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data): array
	{
		return [
			'id'=>$data->udid,
            'program'=>@$data->program->type->name,
            'programId'=>@$data->programtId,
            'patientId'=>$data->patientId,
            'onboardingScheduleDate'=>strtotime($data->onboardingScheduleDate),
            'dischargeDate'=>(!empty($data->dischargeDate))?strtotime($data->dischargeDate):'',
			'renewalDate'=>(!empty($data->renewalDate))?strtotime($data->renewalDate):'',
            'status'=> (!empty($data->dischargeDate))?(time()>strtotime($data->dischargeDate)?0:$data->isActive) :$data->isActive,
		];
	}
}
