<?php

namespace App\Transformers\Client;

use App\Models\Program\Program;
use League\Fractal\TransformerAbstract;
use App\Transformers\People\PeopleTransformer;
use App\Models\Client\AssignProgram\AssignProgram;
use App\Transformers\Contact\ContactNewTransformer;
use App\Transformers\AssignProgram\AssignProgramTransformer;

class ClientDetailTransformer extends TransformerAbstract
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

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($data)
    {
        $listData = [];
        $peoples = AssignProgram::where(['referenceId' => $data->udid, 'entityType' => 'Client'])->get('programId');
        $program = Program::whereIn('id', $peoples)->get();
        foreach ($program as $key => $people) {
            $listData[$key] = $people->udid;
        }
        // $contact = $data->contact ? $data->contact : $data->staff;
        return [
            'udid' => $data->udid,
            'friendlyName' => $data->friendlyName,
            'legalName' => $data->legalName,
            'npi' => $data->npi,
            'statusId' => (!empty($data->status)) ? $data->status->id : '',
            'statusName' => (!empty($data->status)) ? $data->status->name : '',
            'addressLine1' => $data->addressLine1,
            'addressLine2' => $data->addressLine2,
            'city' => $data->city,
            'phoneNumber' => substr($data->phoneNumber, 0, 3).'-'.substr($data->phoneNumber, 3, 3).'-'.substr($data->phoneNumber,6),
            'fax' => $data->fax,
            'zipCode' => $data->zipCode,
            'contractTypeId' => (!empty($data->contractType)) ? $data->contractType->id : '',
            'contractTypeName' => (!empty($data->contractType)) ? $data->contractType->name : '',
            'stateId' => (!empty($data->state)) ? $data->state->id : '',
            'stateName' => (!empty($data->state)) ? $data->state->iso : '',
            'startDate' => strtotime($data->startDate),
            'endDate' => strtotime($data->endDate),
            'programs' => (!empty($listData)) ? $listData : [],
            // 'contactPerson' => $data->contact ? fractal()->item($contact)->transformWith(new ContactNewTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : (!empty($contact)) ? fractal()->item($contact)->transformWith(new PeopleTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : '',
        ];
    }
}
