<?php

namespace App\Transformers\Provider;

use League\Fractal\TransformerAbstract;


class ProviderLocationDetailTransformer extends TransformerAbstract
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
    public function transform($data)
    {
        return [
            'id' => $data ? $data->id : '',
            'parentId' => '',
            'parentName' => '',
            'locationName' => (!empty($data->country)) ? $data->country->name : '',
            'locationId' => (!empty($data->country)) ? $data->country->id : '',
            'entityType' => 'Country',
            'isDefault' => (!empty($data->isDefault)) ? $data->isDefault : 0,
            'cityId'=>(!empty($data->stateLatest))?$data->stateLatest[0]->cityLatest[0]->id:'',
        ];
    }
}
