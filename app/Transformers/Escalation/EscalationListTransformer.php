<?php

namespace App\Transformers\Escalation;

use App\Transformers\Flag\FlagTransformer;
use App\Transformers\Patient\PatientDetailTransformer;
use League\Fractal\TransformerAbstract;


class EscalationListTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [
        //
    ];


    protected array $availableIncludes = [
        //
    ];


    public function transform($data): array
    {
        return [
                'id' =>$data->udid,
			    'patientId'=>$data->patient->udid,
		];
    }
}
