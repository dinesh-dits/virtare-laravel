<?php

namespace App\Transformers\Escalation;

use League\Fractal\TransformerAbstract;

class EscalationActionTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        return [
            'id' => $data->udid,
            'action'=>$data->action?$data->action->name:'',
            'actionId'=>$data->action?$data->action->id:'',
            'note'=>$data->note,
        ];
    }
}
