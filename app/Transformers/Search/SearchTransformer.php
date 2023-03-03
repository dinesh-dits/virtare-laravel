<?php

namespace App\Transformers\Search;

use League\Fractal\TransformerAbstract;


class SearchTransformer extends TransformerAbstract
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
            'id' => $data->udid,
            'type' => $data->type == 4 ? "Patient" : "Staff",
            'firstName' => $data->firstName,
            'lastName' => $data->lastName,
            'middleName' => (!empty($data->middleName)) ? $data->middleName : '',
            'phoneNumber' => $data->phoneNumber,
            'email' => $data->email,
            'fullName' => str_replace("  ", " ", ucfirst($data->lastName) . ',' . ' ' . ucfirst($data->firstName) . ' ' . ucfirst($data->middleName))
        ];
    }
}
