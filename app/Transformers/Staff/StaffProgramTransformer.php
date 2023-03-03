<?php

namespace App\Transformers\Staff;

use League\Fractal\TransformerAbstract;
use App\Transformers\Program\ProgramTransformer;
use App\Transformers\Provider\ProviderLocationTransformer;


class StaffProgramTransformer extends TransformerAbstract
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
            'udid' => $data->udid,
            'isDefault' => $data->isDefault,
            'programs' => fractal()->item($data->program)->transformWith(new ProgramTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray()
        ];
    }
}
