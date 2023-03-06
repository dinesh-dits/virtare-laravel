<?php

namespace App\Transformers\Escalation;

use League\Fractal\TransformerAbstract;


class EscalationCountTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data)
    {
       
        return [
            'trending' => $data['trending'],
            'urgent' => $data['urgent'],
        ];
    }
}
