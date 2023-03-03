<?php

namespace App\Transformers\Patient;

use League\Fractal\TransformerAbstract;
use App\Transformers\Staff\StaffDetailTransformer;

class PatientStaffTransformer extends TransformerAbstract
{
	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data): array
	{
		return [
			'id'=>$data->udid,
            'patient'=>str_replace("  ", " ", ucfirst($data->patient->lastName) . ',' . ' ' . ucfirst($data->patient->firstName) . ' ' . ucfirst($data->patient->middleName)),
            'staffId'=>$data->staff->udid,
            'staff'=>ucfirst($data->staff->lastName) . ',' . ' ' . ucfirst($data->staff->firstName),
            'staffData'=>$data->staff?fractal()->item($data->staff)->transformWith(new StaffDetailTransformer())->toArray():'',
            'name'=>ucfirst($data->staff->lastName) . ',' . ' ' . ucfirst($data->staff->firstName),
			'type'=>$data->isCareTeam,
			'isPrimary'=>$data->isPrimary,
			'staffType'=>($data->staff->type)?$data->staff->type->name:'',
			'organisation'=>$data->staff->organisation ? $data->staff->organisation:'',
			'location'=>$data->staff->location ? $data->staff->location:'',
		];
	}
}
