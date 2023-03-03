<?php

namespace App\Transformers\Patient;

use App\Transformers\Provider\ProviderTransformer;
use League\Fractal\TransformerAbstract;


class PatientProviderTransformer extends TransformerAbstract
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
            'id'=>$data->udid,
            'isDefault'=>$data->isDefault,
            "provider" => fractal()->item($data->providers)->transformWith(new ProviderTransformer(false))->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray(),
		];
    }
}
