<?php

namespace App\Transformers\Escalation;

use App\Models\Flag\Flag;
use App\Models\Patient\PatientFlag;
use App\Models\GlobalCode\GlobalCode;
use League\Fractal\TransformerAbstract;
use App\Transformers\Patient\PatientFlagTransformer;


class EscalationFlagTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        $flagName = "";
        $flagColor = "";
        $pflag = array();
        if(isset($data->flagId) && !empty($data->flagId)){
            $pflag = PatientFlag::where("id",$data->flagId)->withTrashed()->first();

            if(isset($pflag->flagId)){
                $flag = Flag::where("id",$pflag->flagId)->first();
                if(isset($flag->name)){
                    $flagName = $flag->name;
                    $flagColor = $flag->color;
                }
            }
        }

        $escalationType = ""; 
        if(isset($data->escalationType) && !empty($data->escalationType)){
            $globalCode = GlobalCode::where("id",$data->escalationType)->first();
            if(isset($globalCode->name)){
                $escalationType = $globalCode->name;
            }
        }
        
        return [
            'udid' => $data->udid,
            'flagId' => $data->flagId,
            'flagName' => $flagName,
            'flagColor' => $flagColor,
            'escalationId' => $data->escalationId,
            'escalationTypeId' => $data->escalationType,
            'escalationType' => $escalationType,
            'isActive' => $data->isActive?True:False,
            'flag'=> !empty($pflag)?fractal()->item($pflag)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->transformWith(new PatientFlagTransformer())->toArray():"",
        ];
    }
}
