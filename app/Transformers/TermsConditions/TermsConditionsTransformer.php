<?php

namespace App\Transformers\TermsConditions;

use League\Fractal\TransformerAbstract;


class TermsConditionsTransformer extends TransformerAbstract
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
        return[
            "id"=>$data->udid,
            "key"=>$data->key,
            'value'=>$data->value,
            'lastUpdated'=>strtotime($data->updatedAt)
        ];

    }
}
