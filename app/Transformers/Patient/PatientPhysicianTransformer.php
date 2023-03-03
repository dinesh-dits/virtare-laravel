<?php

namespace App\Transformers\Patient;

use League\Fractal\TransformerAbstract;
use App\Transformers\User\UserTransformer;

class PatientPhysicianTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        return [
            'id' => $data->udid,
            'patientId' => $data->patientId,
            'name' => (!empty($data->name)) ? $data->name : '',
            'designation' => (!empty($data->designation)) ? $data->designation->name : '',
            'designationId' => (!empty($data->designation)) ? $data->designation->id : '',
            'phoneNumber' => (!empty($data->phoneNumber)) ? $data->phoneNumber : '',
            'fax' => (!empty($data->name)) ? $data->fax : '',
            'isPrimary' => (!empty($data->isPrimary)) ? $data->isPrimary : 0,
            'sameAsReferal' => (!empty($data->sameAsReferal)) ? $data->sameAsReferal : 0,
            'email' => (!empty($data->user)) ? $data->user->email : '',
            'user' => $data->user ? fractal()->item($data->user)->transformWith(new UserTransformer(false))->toArray() : [],
        ];
    }
}
