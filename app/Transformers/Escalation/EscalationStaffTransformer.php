<?php

namespace App\Transformers\Escalation;

use App\Models\Staff\Staff;
use App\Models\GlobalCode\GlobalCode;
use League\Fractal\TransformerAbstract;


class EscalationStaffTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        $staffName = "";
        $staffUdid = "";
        if(isset($data->staffId)){
            $s = Staff::where("id",$data->staffId)->first();
            if(isset($s->id)){
                $staffName = ucfirst($s->lastName). ','  . ' ' . ucfirst($s->firstName);
                $staffUdid = $s->udid;
            }
        }
        return [
            'udid' => $data->udid,
            'staffId' => $data->staffId,
            'staffUdid' => $staffUdid,
            'staffName' => $staffName,
            'escalationId' => $data->escalationId,
            'isActive' => $data->isActive?True:False,
        ];
    }
}
