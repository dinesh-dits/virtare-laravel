<?php

namespace App\Transformers\CPTCode;

use App\Models\CPTCode\CPTCode;
use App\Models\Patient\PatientVital;
use League\Fractal\TransformerAbstract;
use App\Models\Communication\CallRecord;
use App\Models\Patient\PatientInventory;
use App\Models\CPTCode\CptCodeServiceDetail;
use App\Transformers\CPTCode\CPTCodeTransformer;
use App\Transformers\Patient\PatientVitalTransformer;
use App\Transformers\GlobalCode\GlobalCodeTransformer;
use App\Transformers\Patient\PatientDetailTransformer;
use App\Transformers\CPTCode\CptCodeActivityTransformer;
use App\Transformers\Patient\PatientInventoryTransformer;
use App\Transformers\Communication\CallRecordListTransformer;
use App\Transformers\CPTCode\CPTCodeServiceConditionTransformer;

class CPTCodeServiceTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [
        //
    ];


    protected array $availableIncludes = [
        //
    ];

   
    public function transform($data): array
    {
        $serviceDetail = CptCodeServiceDetail::where('cptCodeServicesId',$data->id)->selectRaw('group_concat(serviceId) as serviceId,group_concat(id) as Id')->first();
        $serviceId = explode(",", $serviceDetail['serviceId']);
        $id = explode(",", $serviceDetail['Id']);
        if($data->entity == 'device'){
            $device = PatientInventory::whereIn('id',$serviceId)->withTrashed()->get();
        }else{
            $device = null;
        }
        if($data->entity == 'vital'){
            $vital = PatientVital::whereIn('id',$serviceId)->withTrashed()->get();
        }else{
            $vital = null;
        }
        if($data->entity == 'call'){
            $call = CallRecord::select('callRecords.*','callRecordTimes.startTime','callRecordTimes.endTime','cptCodeServiceTimeSpents.timeSpent')->whereIn('communicationCallRecordId',$serviceId)->with('staff')->Join('callRecordTimes', 'callRecordTimes.callRecordId', '=', 'callRecords.id')->Join('cptCodeServiceTimeSpents', 'cptCodeServiceTimeSpents.refrenceId', '=', 'callRecordTimes.id')->whereIn('cptCodeServiceTimeSpents.cptCodeServicesDetailId',$id)->withTrashed()->get();
        }else{
            $call = null;
        }
        $id = $data->cptCodeActivity->cptCodeId;
        $cptCode = CPTCode::where('id',$id)->first();

        $sId = '1000';
        $serviceId = $data->id + $sId;

        return [
                'id' => $data->id,
                'entity' => $data->entity,
                'serviceId' => $serviceId,
                'billingDate' => strtotime($data->createdAt),
                'nextBillDate' => strtotime($data->createdAt),
                'numberOfUnit' => $data->units,
                'cost' => $data->cost,
                'isActive'=> $data->isActive ? True : False,
                'placeOfService' => $data->placesOfService ? fractal()->item($data->placesOfService)->transformWith(new GlobalCodeTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"",
                'status' => $data->cptCodeStatus ? fractal()->item($data->cptCodeStatus)->transformWith(new GlobalCodeTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"",
                'condition' => $data->cptCodeServiceCondition ? fractal()->collection($data->cptCodeServiceCondition)->transformWith(new CPTCodeServiceConditionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"",
                'typeOfService' => $data->cptCodeActivity ? fractal()->item($data->cptCodeActivity)->transformWith(new CptCodeActivityTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"",
                'cptCode' => $cptCode ? fractal()->item($cptCode)->transformWith(new CPTCodeTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"",
                'patient' => $data->patient ? fractal()->item($data->patient)->transformWith(new PatientDetailTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"",
                'device' => $device ? fractal()->collection($device)->transformWith(new PatientInventoryTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"",
                'vital' => $vital ? fractal()->collection($vital)->transformWith(new PatientVitalTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"",
                'call' => $call ? fractal()->collection($call)->transformWith(new CallRecordListTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"",
            ];
    }
}
