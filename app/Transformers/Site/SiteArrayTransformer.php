<?php

namespace App\Transformers\Site;

use League\Fractal\TransformerAbstract;

class SiteArrayTransformer extends TransformerAbstract
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

    public function transform($data)
    {
        return [
            'udid' => $data->udid,
            'friendlyName' => $data->friendlyName
        ];
    }
}
