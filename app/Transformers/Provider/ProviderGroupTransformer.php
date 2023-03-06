<?php

namespace App\Transformers\Provider;

use League\Fractal\TransformerAbstract;
use App\Transformers\Group\GroupTransformer;


class ProviderGroupTransformer extends TransformerAbstract
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

    protected $showData;

    public function __construct($showData = true)
    {
        $this->showData = $showData;
    }

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($data)
    {
        return [
            'id' => $data->udid,
            'group' => $data->group ? fractal()->collection($data->group)->transformWith(new GroupTransformer(false))->toArray() : array(),
        ];
    }
}
