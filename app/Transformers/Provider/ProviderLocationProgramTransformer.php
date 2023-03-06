<?php

namespace App\Transformers\Provider;

use League\Fractal\TransformerAbstract;


class ProviderLocationProgramTransformer extends TransformerAbstract
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
            "id" => $data->id,
            "program" => $data->program->name,
            "programId" => $data->program->id,
            "programUdid" => $data->program->udid,
            'targetUdid' => $data->programId,
        ];
    }
}
