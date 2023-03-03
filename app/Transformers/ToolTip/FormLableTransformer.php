<?php

namespace App\Transformers\ToolTip;

use League\Fractal\TransformerAbstract;

class FormLableTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data)
    {
        return [
            'id' => $data->id,
            'udid' => $data->udid,
            'name' => $data->name,
            'lableType' => $data->lableType,
            'type' => $data->types->name,
            'refrenceType' => $data->refrenceType,
            'description' => $data->description,
            'isActive' => $data->isActive,
        ];
    }
}
