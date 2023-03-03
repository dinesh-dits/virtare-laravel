<?php

namespace App\Transformers\Contact;

use App\Models\Patient\Patient;
use App\Transformers\Task\PatientTaskGlobalCodeTransformer;
use App\Transformers\User\UserTransformer;
use League\Fractal\TransformerAbstract;

class ContactPatientTransformer extends TransformerAbstract
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
    public function transform($patient)
    {
        return[
           'id' => $patient->udid,
           'fullName' => str_replace("  ", " ", ucfirst($patient->lastName) . ',' . ' ' . ucfirst($patient->firstName) . ' ' . ucfirst($patient->middleName)),
           'dob'  => $patient -> dob,
        ];

    }
}
