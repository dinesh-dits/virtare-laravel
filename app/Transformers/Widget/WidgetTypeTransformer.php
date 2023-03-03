<?php

namespace App\Transformers\Widget;

use App\Transformers\GlobalCode\GlobalCodeTransformer;
use League\Fractal\TransformerAbstract;

class WidgetTypeTransformer extends TransformerAbstract
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
			'type' => $data->typeWidget->name,
            'widgetType' => $data->widgetType,
		];
	}
}
