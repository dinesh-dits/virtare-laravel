<?php

namespace App\Transformers\Task;

use League\Fractal\TransformerAbstract;

class PatientTaskTransformer extends TransformerAbstract
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
        return [
            'patientTaskId' => $data->udid,
            'title' => $data->title,
            'priority' => $data->priority ? fractal()->item($data->priority)->transformWith(new PatientTaskGlobalCodeTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
            'startTimeDate' => strtotime($data->startTimeDate),
            'dueDate' => strtotime($data->dueDate),
            'status' => $data->status ? fractal()->item($data->status)->transformWith(new PatientTaskGlobalCodeTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
            'isActive' => $data->isActive,
            'description' => $data->description,
            'patient' => $data->patient ? fractal()->item($data->patient)->transformWith(new PatientTasksTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
        ];

    }
}
