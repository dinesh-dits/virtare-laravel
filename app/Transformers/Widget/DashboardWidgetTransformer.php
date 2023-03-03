<?php

namespace App\Transformers\Widget;

use League\Fractal\TransformerAbstract;

class DashboardWidgetTransformer extends TransformerAbstract
{

	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data)
	{
		return [
            'id' =>$data->id,
            'name'=>$data->name,
            'description'=>$data->description,
            'widget' => $data->dashboardWidgets ? fractal()->collection($data->dashboardWidgets)->transformWith(new WidgetTypeTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"", 
		];
	}
}
