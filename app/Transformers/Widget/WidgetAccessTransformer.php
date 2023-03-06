<?php

namespace App\Transformers\Widget;

use League\Fractal\TransformerAbstract;

class WidgetAccessTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data)
    {
        return [
            'id' => $data->widget ? $data->widget->id : '',
            'udid' => $data->widget ? $data->widget->udid : '',
            'name' => $data->widget ? $data->widget->widgetName : '',
            'title' => $data->widget ? $data->widget->title : '',
        ];
    }
}
