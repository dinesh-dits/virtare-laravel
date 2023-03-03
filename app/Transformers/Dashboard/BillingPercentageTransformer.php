<?php

namespace App\Transformers\Dashboard;

use League\Fractal\TransformerAbstract;


class BillingPercentageTransformer extends TransformerAbstract
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
           "paid"=>$data[0]->paid,
           "unPaid"=>$data[0]->unPaid,
           "Billed"=>$data[0]->billed,
           "logged"=>$data[0]->logged,
        ];
    }
}
