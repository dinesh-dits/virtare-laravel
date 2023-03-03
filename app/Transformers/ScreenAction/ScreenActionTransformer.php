<?php

namespace App\Transformers\ScreenAction;

use App\Transformers\User\UserTransformer;
use League\Fractal\TransformerAbstract;


class ScreenActionTransformer extends TransformerAbstract
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
			'user' => $data['user'] ? fractal()->item($data['user'])->transformWith(new UserTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : array(),
            'action' =>$data->action->name, 
            'time' => $data->createdAt,   
		];
    }
}
