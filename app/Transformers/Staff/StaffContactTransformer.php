<?php

namespace App\Transformers\Staff;

use League\Fractal\TransformerAbstract;


class StaffContactTransformer extends TransformerAbstract
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
    public function transform($data): array
    {
        return [
            'id' => $data->udid,
            'firstName' => $data->firstName,
            'middleName' => $data->middleName,
            'lastName' => $data->lastName,
            'extension' => $data->extension,
            'email' => $data->email,
            'phoneNumber' => $data->phoneNumber,
            'staffId' => $data->staffId,
		];
    }
}
