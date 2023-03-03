<?php

namespace App\Transformers\Client;

use League\Fractal\TransformerAbstract;
use App\Transformers\Contact\ContactTransformer;
use App\Transformers\Contact\ContactNewTransformer;
use App\Transformers\AssignProgram\AssignProgramTransformer;
use App\Models\Client\CareTeam;
use App\Models\Patient\PatientProvider;
class ClientTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        //
    ];
    public function getActivePatients($clientId){
        $careTeamArray = array();
        $careTeams = CareTeam::where('clientId',$clientId)->get('udid'); 
        if($careTeams->count() >0){
            foreach( $careTeams as $key=> $team){
                $careTeamArray[$key] = $team->udid;
            }
          $count=  PatientProvider::whereIn('providerId',$careTeamArray)->count();
          return $count;
        }else{
            return 0;
        }
        
    }
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($data)
    {
        return [
            'udid' => $data->udid,
            'friendlyName' => $data->friendlyName,
            'legalName' => $data->legalName,
            'npi' => $data->npi,
            'statusId' => (!empty($data->status)) ? $data->status->id : '',
            'statusName' => (!empty($data->status)) ? $data->status->name : '',
            'color' => (!empty($data->status)) ? $data->status->color : '',
            'addressLine1' => $data->addressLine1,
            'addressLine2' => $data->addressLine2,
            'city' => $data->city,
            'phoneNumber' => $data->phoneNumber,
            'fax' => $data->fax,
            'zipCode' => $data->zipCode,
            'contractTypeId' => (!empty($data->contractType)) ? $data->contractType->id : '',
            'contractTypeName' => (!empty($data->contractType)) ? $data->contractType->name : '',
            'stateId' => (!empty($data->state)) ? $data->state->id : '',
            'stateName' => (!empty($data->state)) ? $data->state->iso : '',
            'startDate' => strtotime($data->startDate),
            'endDate' => strtotime($data->endDate),
            'activePatients' =>$this->getActivePatients($data->udid),
            'isActive' => $data->isActive,
            'programs' => fractal()->collection($data->assignProgram)->transformWith(new AssignProgramTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray(),
            // 'contactPerson' => (!empty($data->contact))?fractal()->item($data->contact)->transformWith(new ContactNewTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():'',
        ];
    }
}
