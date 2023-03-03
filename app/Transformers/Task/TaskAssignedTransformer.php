<?php

namespace App\Transformers\Task;

use League\Fractal\TransformerAbstract;


class TaskAssignedTransformer extends TransformerAbstract
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
        if ($data->entityType == 'staff') {
            return [
                'id' => (!empty($data->assigned)) ? @$data->assigned->udid : $data->staffUdid,
                'assignedId' => (!empty($data->assigned)) ? @$data->assigned->udid : $data->staffId,
                'entityType' => @$data->entityType,
                'taskId' => @$data->taskId,
                'name' => (!empty($data->assigned)) ? ucfirst(@$data->assigned->firstName) . ' ' . ucfirst(@$data->assigned->lastName) : ucfirst(@$data->staffFirstName) . ' ' . ucfirst(@$data->staffLastName)
            ];
        } elseif ($data->entityType == 'patient') {
            return [
                'id' => (!empty($data->patient)) ? @$data->patient->udid : $data->patentUdid,
                'assignedId' => (!empty($data->patient)) ? @$data->patient->id : $data->patientsId,
                'entityType' => @$data->entityType,
                'taskId' => @$data->taskId,
                'name' => (!empty($data->patient)) ? ucfirst(@$data->patient->firstName) . ' ' . ucfirst(@$data->patient->lastName) : ucfirst(@$data->patientFirstName) . ' ' . ucfirst(@$data->patientLastName)
            ];
        }

    }
}
