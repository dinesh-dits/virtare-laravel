<?php

namespace App\Transformers\Escalation;

use App\Models\Patient\PatientGoal;
use App\Models\GlobalCode\GlobalCode;
use League\Fractal\TransformerAbstract;
use App\Transformers\Patient\PatientGoalTransformer;


class EscalationCarePlanTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        $escalationType = ""; 
        if(isset($data->escalationType) && !empty($data->escalationType)){
            $globalCode = GlobalCode::where("id",$data->escalationType)->first();
            if(isset($globalCode->name)){
                $escalationType = $globalCode->name;
            }
        }

        $pGoal = "";
        if(isset($data->carePlanId)){
            $pGoal = PatientGoal::where("id", $data->carePlanId)->first();
        }

        return [
            'udid' => $data->udid,
            'carePlanId' => $data->carePlanId,
            'escalationId' => $data->escalationId,
            'escalationTypeId' => $data->escalationType,
            'escalationType' => $escalationType,
            'isActive' => $data->isActive?True:False,
            "carePlan" => $pGoal?fractal()->item($pGoal)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->transformWith(new PatientGoalTransformer())->toArray():"",
        ];
    }
}
