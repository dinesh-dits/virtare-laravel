<?php

namespace App\Transformers\Staff;

use League\Fractal\TransformerAbstract;
use App\Transformers\Provider\SubLocationTransformer;
use App\Transformers\Provider\ProviderLocationTransformer;
use App\Transformers\Provider\ProviderLocationCityTransformer;
use App\Transformers\Provider\ProviderLocationStateTransformer;


class StaffLocationTransformer extends TransformerAbstract
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
    public function transform($data): array
    {
        if ($data->entityType == 'Country') {
            $locationId = $data->location->country->id;
            $locationName = $data->location->country->name;
            $level = '';
        } elseif ($data->entityType == 'State') {
            $locationId = $data->state->state->id;
            $locationName = $data->state->state->name;
            $level = '';
        } elseif ($data->entityType == 'City') {
            $locationId = $data->city->id;
            $locationName = $data->city->city;
            $level = '';
        } elseif ($data->entityType == 'subLocation') {
            $locationId = $data->subLocation->id;
            $locationName = $data->subLocation->subLocationName;
            $level = $data->subLocation->level->name;
        } else {
            $locationId = '';
            $locationName = '';
            $level = '';
        }
        return [
            'udid' => $data->udid,
            'isDefault' => $data->isDefault,
            'locationId' => $locationId,
            'locationName' => $locationName,
            'locationsHierarchy' => $data->locationsHierarchy,
            'entityType' => $data->entityType,
            'subLocationLevel' => $level,
        ];
    }
}
