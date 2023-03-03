<?php

namespace App\Transformers\Provider;

use League\Fractal\TransformerAbstract;
use App\Transformers\Provider\ProviderLocationProgramTransformer;


class SubLocationTransformer extends TransformerAbstract
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

    protected $showData;

    public function __construct($showData = true)
    {
        $this->showData = $showData;
    }

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($data)
    {
        return [
            'id' => $data ? $data->id : '',
            'subLocationLevelId' => $data->level ? $data->level->id : '',
            'subLocationLevel' => $data->level ? $data->level->name : '',
            'entityType' => 'subLocation',
            'subLocationName' => $data ? $data->subLocationName : '',
            'subLocationParent' => $data ? $data->subLocationParent : '',
            'subLocationParentName' => $data->entityType=='City'?$data->city->city:$data->parentName->subLocationName,
            'programs' => $this->showData && (!empty($data->providerLocationProgram)) ? fractal()->collection($data->providerLocationProgram)->transformWith(new ProviderLocationProgramTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : [],
            // 'childLocation' => $this->showData && (!empty($data->childName)) ? fractal()->item($data->childName)->transformWith(new SubLocationTransformer(false))->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : [],
        ];
    }
}
