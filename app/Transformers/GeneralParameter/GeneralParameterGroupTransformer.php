<?php

namespace App\Transformers\GeneralParameter;

use Illuminate\Support\Facades\DB;
use League\Fractal\TransformerAbstract;

class GeneralParameterGroupTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    { 
        return [
            'id'=>$data->udid,
            'name'=>$data->name,
            'deviceType'=>(!empty($data->deviceType->name))?$data->deviceType->name:'',
            'deviceTypeId'=>(!empty($data->deviceType->id))?$data->deviceType->id:'',
            'generalparameter'=> ["data" => DB::select("CALL getGenralParameter('" . $data->id . "')")],
        ];
    }
}
