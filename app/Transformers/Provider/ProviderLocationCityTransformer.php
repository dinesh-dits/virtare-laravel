<?php

namespace App\Transformers\Provider;

use League\Fractal\TransformerAbstract;
use App\Transformers\Provider\SubLocationTransformer;
use App\Transformers\Provider\ProviderLocationProgramTransformer;


class ProviderLocationCityTransformer extends TransformerAbstract
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
            'patentName' => (!empty($data->state)) ? $data->state->state->name : '',
            'parentId' => (!empty($data->state)) ? $data->state->id : '',
            'locationId' => (!empty($data->city)) ? $data->city : '',
            'locationName' => (!empty($data->city)) ? $data->city : '',
            'entityType' => 'City',
            'programs' => $this->showData && (!empty($data->providerLocationProgram)) ? fractal()->collection($data->providerLocationProgram)->transformWith(new ProviderLocationProgramTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : [],
            'subLocation' => $this->showData && (!empty($data->subLocation)) ? fractal()->item($data->subLocation)->transformWith(new SubLocationTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : [],
        ];
    }
}
