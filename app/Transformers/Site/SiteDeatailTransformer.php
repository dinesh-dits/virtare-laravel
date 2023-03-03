<?php

namespace App\Transformers\Site;

use App\Models\Program\Program;
use League\Fractal\TransformerAbstract;
use App\Models\Client\AssignProgram\AssignProgram;

class SiteDeatailTransformer extends TransformerAbstract
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
        $peoples = AssignProgram::where(['referenceId' => $data->udid, 'entityType' => 'Site'])->get('programId');
        $program = Program::whereIn('id', $peoples)->get();
        foreach ($program as $key => $people) {
            $listData[$key] = $people->udid;
        }
        $address = [
            'addressLine1' => $data->addressLine1,
            'addressLine2' => $data->addressLine2,
            'city' => $data->city,
            'zipCode' => $data->zipCode,
            'stateId' => (!empty($data->state)) ? $data->state->id : '',
            'stateName' => (!empty($data->state)) ? $data->state->iso : '',
        ];
        $addressData = $data->virtual == 1 ? '' : $address;
        return [
            'udid' => $data->udid,
            'friendlyName' => $data->friendlyName,
            'status' => (!empty($data->status)) ? $data->status->id : '',
            'statusName' => (!empty($data->status)) ? $data->status->name : '',
            'siteHeadId' => (isset($data->head->id)) ? $data->head->udid : '',
            'virtual' => $data->virtual,
            'comment' => $data->comment,
            // 'isHead' => $data->getSiteHead($data->id),
            'isHead' => (isset($data->head->id)) ? $data->head->title . ' ' . $data->head->firstName . ' ' . $data->head->lastName : '',
            'programs' => (!empty($listData)) ? $listData : [],
            'address' => $addressData,
        ];
    }
}
