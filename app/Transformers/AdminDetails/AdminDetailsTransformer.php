<?php

namespace App\Transformers\AdminDetails;

use League\Fractal\TransformerAbstract;


class AdminDetailsTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data)
    {
        return [
            'adminEmail'=>$data['email'],
            'adminName'=>$data['name'],
        ];
    }
}
