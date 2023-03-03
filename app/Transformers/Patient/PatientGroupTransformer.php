<?php

namespace App\Transformers\Patient;

use League\Fractal\TransformerAbstract;
use App\Transformers\Group\GroupTransformer;

class PatientGroupTransformer extends TransformerAbstract
{


    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data)
    {

        // 'group'=>$data->group->udid,
        // 'groupName'=>$data->group->group,
        // 'patient'=>fractal()->item($data->patient)->transformWith(new PatientDetailTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray(),
        return fractal()->item($data->group)->transformWith(new GroupTransformer(false))->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();

    }
}
