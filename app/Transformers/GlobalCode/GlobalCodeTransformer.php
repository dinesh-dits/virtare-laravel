<?php

namespace App\Transformers\GlobalCode;

use League\Fractal\TransformerAbstract;


class GlobalCodeTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        //
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($data): array
    {
        return [
            'id'=>$data->id,
            'globalCodeCategoryId'=>$data->globalCodeCategory->id,
            'globalCodeCategory'=>$data->globalCodeCategory->name,
			'name'=>$data->name,
            'description'=>$data->description,
            'isActive'=>$data->isActive,
            'predefined'=>$data->predefined,
            'color'=>$data->color,
            'iso'=>$data->iso,
            'usedCount'=>0,
            'priority' => (isset($data->priority)) ? $data->priority : '',
		];
    }
}
