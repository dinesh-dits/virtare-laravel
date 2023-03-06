<?php

namespace App\Transformers\Widget;

use League\Fractal\TransformerAbstract;

class WidgetModuleTransformer extends TransformerAbstract
{

	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data)
	{
		return [
            'id' =>$data->id,
            'name'=>$data->name,
            'description'=>$data->description,
            'widget' => $data->widgets ? fractal()->collection($data->widgets)->transformWith(new WidgetTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"", 
		];
	}
}
