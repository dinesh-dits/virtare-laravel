<?php

namespace App\Transformers\Widget;

use League\Fractal\TransformerAbstract;

class AssignedWidgetTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data)
    {
        return [
            'widgetUdid' => $data->widget->udid,
            'widgetName' => $data->widget->widgetName,
            'role' => $data->role->roles,
        ];
    }
}
