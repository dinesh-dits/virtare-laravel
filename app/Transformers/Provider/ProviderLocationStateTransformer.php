<?php

namespace App\Transformers\Provider;

use League\Fractal\TransformerAbstract;
use App\Transformers\Provider\ProviderLocationCityTransformer;
use App\Transformers\Provider\ProviderLocationProgramTransformer;


class ProviderLocationStateTransformer extends TransformerAbstract
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
            'id' => $data ? $data->id : '',
            'patentName' => (!empty($data->country)) ? $data->country->name : '',
            'parentId' => (!empty($data->country)) ? $data->country->id : '',
            'locationId' => (!empty($data->state)) ? $data->state->id : '',
            'locationName' => (!empty($data->state)) ? $data->state->name : '',
            'entityType' => 'State',
            'programs' => $this->showData && (!empty($data->providerLocationProgram)) ? fractal()->collection($data->providerLocationProgram)->transformWith(new ProviderLocationProgramTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : [],
            'city' => $this->showData && (!empty($data->city)) ? fractal()->collection($data->city)->transformWith(new ProviderLocationCityTransformer(true))->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : [],
        ];
    }
}
