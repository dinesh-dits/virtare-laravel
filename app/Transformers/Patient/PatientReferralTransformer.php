<?php

namespace App\Transformers\Patient;

use League\Fractal\TransformerAbstract;
use App\Transformers\Referral\ReferralTransformer;

class PatientReferralTransformer extends TransformerAbstract
{
	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data): array
	{
		return [
            $data->patientReferral ? fractal()->item($data->patientReferral)->transformWith(new ReferralTransformer(false))->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : [],
		];
	}
}
