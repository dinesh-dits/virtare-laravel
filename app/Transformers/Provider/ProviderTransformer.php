<?php

namespace App\Transformers\Provider;

use League\Fractal\TransformerAbstract;
use App\Transformers\Program\ProgramTransformer;


class ProviderTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
    ];

    protected $showData;

    public function __construct($showData = true)
    {
        $this->showData = $showData;
    }

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
        return[
            'id'=>$data->id,
            'udid'=>$data->udid,
            'name' => $data->name,
            'totalLocations'=>$data->totalLocations,
            'countLocations'=>$data->countLocations,
            'zipcode'=>$data->zipcode,
            'phoneNumber'=>$data->phoneNumber,
            'countryId'=>$data->country->id,
            'countryName'=>$data->country->name,
            'stateId'=>$data->state->id,
            'stateName'=>$data->state->name,
            'city'=>$data->city,
            'zipCode'=>$data->zipcode,
            'tagId'=>$data->tagId,
            'moduleId'=>$data->moduleId,
            'domain'=>(!empty($data->domain))?$data->domain->name:'',
            'domainId'=>(!empty($data->domain))?$data->domain->id:'',
            'isActive'=>$data->isActive?true:false,
            'programs'=>$this->showData && (!empty($data->providerProgram))?fractal()->collection($data->providerProgram)->transformWith(new ProviderProgramTransformer(true))->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():[],
            'locations'=>$this->showData && (!empty($data->location))?fractal()->collection($data->location)->transformWith(new ProviderLocationTransformer(false))->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():[],
            'defaultLocation'=>$this->showData && (!empty($data->default))?fractal()->item($data->default)->transformWith(new ProviderLocationTransformer(false))->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():[],
        ];

    }
}
