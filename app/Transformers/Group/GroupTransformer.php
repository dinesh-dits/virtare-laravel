<?php

namespace App\Transformers\Group;

use League\Fractal\TransformerAbstract;
use App\Transformers\Group\GroupCompositionTransformer;

class GroupTransformer extends TransformerAbstract
{

    protected $showData;

    public function __construct($showData = true)
    {
        $this->showData = $showData;
    }

    protected array $defaultIncludes = [
        //
    ];


    protected array $availableIncludes = [
        //
    ];


    public function transform($data): array
    {

        if ($data->entityType == 'Country') {
            $location = (!empty($data->location)) ? $data->location->country->id : '';
            $locationName = (!empty($data->location)) ? $data->location->country->name : "";
        } elseif ($data->entityType == 'State') {
            $location = (!empty($data->state)) ? $data->state->state->id : '';
            $locationName = (!empty($data->state)) ? $data->state->state->name : '';
        } elseif ($data->entityType == 'City') {
            $location = (!empty($data->city)) ? $data->city->id : '';
            $locationName = (!empty($data->city)) ? $data->city->city : '';
        } elseif ($data->entityType == 'subLocation') {
            $location = (!empty($data->subLocation)) ? $data->subLocation->id : '';
            $locationName = (!empty($data->subLocation)) ? $data->subLocation->subLocationName : '';
        } else {
            $location = '';
            $locationName = '';
        }


        return [
            'groupId' => $data->groupId,
            'udid' => $data->udid,
            'group' => $data->group,
            'isActive' => $data->isActive,
            'createdAt' => $data->createdAt,
            'totalMembers' => (!empty($data->staff)) ? count($data->staff) : '',
            'providerLocation' =>  $location,
            'providerLocationName' =>  $locationName,
            'providerId' => (!empty($data->provider)) ? $data->provider->id : '',
            'providerName' => (!empty($data->provider)) ? $data->provider->name : '',
            'maximumPatientsCount' =>  $data->patientCount,
            'patientAdded' =>  $data->patientAdd,
            'entityType' => $data->entityType,
            'composition' =>  $this->showData && $data->composition ? fractal()->collection($data->composition)->transformWith(new GroupCompositionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : '',
        ];
    }
}
