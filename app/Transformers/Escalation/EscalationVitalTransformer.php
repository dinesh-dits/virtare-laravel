<?php

namespace App\Transformers\Escalation;

use Illuminate\Support\Facades\DB;
use App\Models\GlobalCode\GlobalCode;
use League\Fractal\TransformerAbstract;
use App\Transformers\Patient\PatientVitalTransformer;


class EscalationVitalTransformer extends TransformerAbstract
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
        $patientVitalData = "";
        if(isset($data->vitalId) && !empty($data->vitalId)){
            $patientVitalData = DB::select(
                'CALL getPatientVitalById("' . $data->vitalId . '")',
            );
            
            if(isset($patientVitalData[0])){
                $patientVitalData = $patientVitalData[0];
                $patientVitalData->takeTime = strtotime($patientVitalData->takeTime);
            }
        }
    
        return [
            'udid' => $data->udid,
            'vitalId' => $data->vitalId,
            'escalationId' => $data->escalationId,
            'escalationTypeId' => $data->escalationType,
            'escalationType' => $escalationType,
            'isActive' => $data->isActive?True:False,
            'patientVital' => $patientVitalData?$patientVitalData:'',
        ];
    }
}
