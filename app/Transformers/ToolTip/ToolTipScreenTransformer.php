<?php

namespace App\Transformers\ToolTip;

use League\Fractal\TransformerAbstract;

class ToolTipScreenTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data)
    {
        return [
            'id' => $data->id,
            'udid' => $data->udid,
            'name' => $data->name,
            'description' => $data->description,
            'isActive' => $data->isActive,
            'formLable' => $data->formLable ? fractal()->collection($data->formLable)->transformWith(new FormLableTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
        ];
    }
}
