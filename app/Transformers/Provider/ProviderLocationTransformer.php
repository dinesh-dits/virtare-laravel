<?php

namespace App\Transformers\Provider;

use League\Fractal\TransformerAbstract;
use App\Transformers\Program\ProgramTransformer;
use App\Transformers\Provider\ProviderTransformer;
use App\Transformers\Provider\ProviderLocationStateTransformer;
use App\Transformers\Provider\ProviderLocationProgramTransformer;

class ProviderLocationTransformer extends TransformerAbstract
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
            'parentId' => '',
            'parentName' => '',
            'locationName' => (!empty($data->country)) ? $data->country->name : '',
            'locationId' => (!empty($data->country)) ? $data->country->id : '',
            'entityType' => 'Country',
            'isDefault' => (!empty($data->isDefault)) ? $data->isDefault : 0,
            'state' => $this->showData && (!empty($data->state)) ? fractal()->collection($data->state)->transformWith(new ProviderLocationStateTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : [],
            'programs' => $this->showData && (!empty($data->providerLocationProgram)) ? fractal()->collection($data->providerLocationProgram)->transformWith(new ProviderLocationProgramTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : [],
            'provider' => $this->showData && (!empty($data->provider)) ? fractal()->item($data->provider)->transformWith(new ProviderTransformer(false))->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : '',
        ];
    }
}
