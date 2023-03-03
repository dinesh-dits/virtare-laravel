<?php

namespace App\Transformers\Widget;

use League\Fractal\TransformerAbstract;

class WidgetTransformer extends TransformerAbstract
{

	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data)
	{
		return [
            'id' =>$data->id,
            'udid'=>$data->udid,
            'widgetName'=>$data->widgetName,
            'title'=>$data->title,
		];
	}
}
