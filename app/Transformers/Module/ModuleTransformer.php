<?php

namespace App\Transformers\Module;

use App\Transformers\Screen\ScreenTransformer;
use League\Fractal\TransformerAbstract;


class ModuleTransformer extends TransformerAbstract
{
   
    protected array $defaultIncludes = [
        //
    ];
    
   
    protected array $availableIncludes = [
        //
    ];
    
    
    public function transform($data): array
    {
        return [
			    'name'=>$data->name,
                'description'=>$data->description,
                'screens'=>  fractal()->collection($data->screens)->transformWith(new ScreenTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray()
            ];
    }
}
