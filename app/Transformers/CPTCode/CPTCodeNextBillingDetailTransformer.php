<?php

namespace App\Transformers\CPTCode;

use App\Models\CPTCode\CPTCode;
use Illuminate\Support\Facades\DB;
use League\Fractal\TransformerAbstract;
use App\Models\Patient\PatientInventory;
use App\Models\CPTCode\CptCodeServiceDetail;
use App\Transformers\Patient\PatientVitalTransformer;
use App\Transformers\Patient\PatientInventoryTransformer;


class CPTCodeNextBillingDetailTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [
        //
    ];


    protected array $availableIncludes = [
        //
    ];

   
    public function transform($data): array
    {
        $serviceId = "";
        if(isset($data->id)){
            $serviceId = CptCodeServiceDetail::where('cptCodeServicesId',$data->id)->selectRaw('group_concat(serviceId) as serviceId')->first();
            $serviceId = explode(",", $serviceId['serviceId']);
            $patientIdx = $data->patientId;
            $vital = null;
            if(isset($data["cptCode"]->createdAt)){
                $fromDate = $data["cptCode"]->createdAt;
            }else{
                $fromDate = "";
            }
            $vital = DB::select(
                "CALL getVitalByCPTServices('" . $patientIdx . "','" . $fromDate . "')",
            );
        }

        if(isset($data->entity) && $data->entity == 'device'){
            $device = PatientInventory::whereIn('id',$serviceId)->withTrashed()->get();
        }else{
            $device = null;
        }
        


         if(isset($data->cptCodeActivity->billingInterval)){
            $id = $data->cptCodeActivity->cptCodeId;
            $cptCode = CPTCode::where('id',$id)->first();
            $sId = '1000';
            $serviceId = $data->id + $sId;
         }else{
            $serviceId = "";
         }

         if(isset($data->cptCodeActivity->billingInterval)){
            $interval = $data->cptCodeActivity->billingInterval;
         }else{
            $interval = 0;
         }

        return [
                 'id' => isset($data->id)?$data->id:"",
                 'entity' => isset($data->id)?$data->entity:"",
                 'serviceId' => $serviceId,
                 'billingInterval' => isset($data->cptCodeActivity)?$data->cptCodeActivity->billingInterval:"",
                 'billingIntervalType' => isset($data->cptCodeActivity)?$data->cptCodeActivity->billingIntervalType:"",
                 'minimumUnit' => isset($data->cptCodeActivity)?$data->cptCodeActivity->minimumUnit:"",
                 'billingDate' => isset($data->id)?strtotime($data->createdAt):"",
                 'nextBillDate' => isset($data->id)?strtotime($data->createdAt. ' + '.$interval.' days'):"",
                 'numberOfUnit' => isset($data->id)?$data->units:"",
                 'cost' => isset($data->id)?$data->cost:"",
                 'isActive'=> $data->isActive ? True : False,
          //        'placeOfService' => $data->placesOfService ? fractal()->item($data->placesOfService)->transformWith(new GlobalCodeTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"",
          //        'status' => $data->cptCodeStatus ? fractal()->item($data->cptCodeStatus)->transformWith(new GlobalCodeTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"",
          //        'condition' => $data->cptCodeServiceCondition ? fractal()->collection($data->cptCodeServiceCondition)->transformWith(new CPTCodeServiceConditionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"",
          //       'typeOfService' => $data->cptCodeActivity ? fractal()->item($data->cptCodeActivity)->transformWith(new CptCodeActivityTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"",
          //       'cptCode' => $cptCode ? fractal()->item($cptCode)->transformWith(new CPTCodeTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"",
          //       'patient' => $data->patient ? fractal()->item($data->patient)->transformWith(new PatientTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"",
		        'devices' => $device ? fractal()->collection($device)->transformWith(new PatientInventoryTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"",
                'vitals' => $vital ? fractal()->collection($vital)->transformWith(new PatientVitalTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"",
            ];
    }
}
