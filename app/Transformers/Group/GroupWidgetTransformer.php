<?php

namespace App\Transformers\Group;

use League\Fractal\TransformerAbstract;
use App\Transformers\Widget\WidgetTransformer;


class GroupWidgetTransformer extends TransformerAbstract
{
   
    protected array $defaultIncludes = [
        //
    ];
    
   
    protected array $availableIncludes = [
        //
    ];
    
    
    public function transform($data): array
    {
        return fractal()->item($data->widget)->transformWith(new WidgetTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
    }
}
