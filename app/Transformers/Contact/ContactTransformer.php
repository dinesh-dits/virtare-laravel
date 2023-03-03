<?php

namespace App\Transformers\Contact;

use App\Models\Patient\Patient;
use App\Transformers\Task\PatientTaskGlobalCodeTransformer;
use App\Transformers\User\UserTransformer;
use League\Fractal\TransformerAbstract;

class ContactTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        //
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($data)
    {
        $patient = Patient::where('userId', $data->user->id)->first();
        return [
            'id' => $data->id,
            'isActive' => $data->isActive,
            'contactTime' => $data->contactTime ? fractal()->item($data->contactTime)->transformWith(new PatientTaskGlobalCodeTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
            'messageStatus' => $data->messageStatus ? fractal()->item($data->messageStatus)->transformWith(new PatientTaskGlobalCodeTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
            'patient' => $patient ? fractal()->item($patient)->transformWith(new ContactPatientTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
        ];
    }
}
