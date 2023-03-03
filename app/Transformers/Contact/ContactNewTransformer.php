<?php

namespace App\Transformers\Contact;

use League\Fractal\TransformerAbstract;

class ContactNewTransformer extends TransformerAbstract
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
            'udid' => $data->udid,
            'firstName' => $data->firstName,
            'middleName' => $data->middleName,
            'lastName' => $data->lastName,
            'email' => (!empty($data->email)) ? $data->email : '',
            'title' => (!empty($data->title)) ? $data->title : '',
            'entityType' => $data->entityType,
            'referenceId' => $data->referenceId,
            'phoneNumber' => substr($data->phoneNumber, 0, 3).'-'.substr($data->phoneNumber, 3, 3).'-'.substr($data->phoneNumber,6),
            'roleId' => (!empty($data->role)) ? $data->role->udid : '',
            'role' => (!empty($data->role)) ? $data->role->roles : '',
            'specialization' => (!empty($data->specialization)) ? $data->specialization->name : '',
            'specializationId' => (!empty($data->specialization)) ? $data->specialization->id : '',
            'timeZone' => (!empty($data->timeZone)) ? $data->timeZone->name : '',
            'timeZoneId' => (!empty($data->timeZone)) ? $data->timeZone->id : '',
        ];
    }
}
