<?php

namespace App\Transformers\AssignProgram;

use League\Fractal\TransformerAbstract;
use App\Transformers\Program\ProgramTransformer;
use App\Transformers\Program\ProgramNewTransformer;

class AssignProgramTransformer extends TransformerAbstract
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
        return fractal()->item($data->program)->transformWith(new ProgramTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
    }
}
