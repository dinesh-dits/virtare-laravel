<?php

namespace App\Transformers\Staff;

use League\Fractal\TransformerAbstract;
use App\Transformers\Program\ProgramTransformer;
use App\Transformers\Staff\StaffProviderTransformer;

class StaffDetailBasicTransformer extends TransformerAbstract
{


    protected $showData;

    public function __construct($showData = true)
    {
        $this->showData = $showData;
    }
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
     * 
     * @return array
     */
    public function transform($data): array
    {

        if (!empty($data->defaultLocation)) {
            if ($data->defaultLocation->entityType == 'Country') {
                $location = (!empty($data->defaultLocation->location)) ? (!empty($data->defaultLocation->location->country)) ? $data->defaultLocation->location->country->id : '' : '';
                $locationName = (!empty($data->defaultLocation->location)) ? (!empty($data->defaultLocation->location->country)) ? $data->defaultLocation->location->country->name : '' : '';
            } elseif ($data->defaultLocation->entityType == 'State') {
                $location = (!empty($data->defaultLocation->state)) ? $data->defaultLocation->state->state->id : '';
                $locationName = (!empty($data->defaultLocation->state)) ? $data->defaultLocation->state->state->name : '';
            } elseif ($data->defaultLocation->entityType == 'City') {
                $location = (!empty($data->defaultLocation->city)) ? $data->defaultLocation->city->id : '';
                $locationName = (!empty($data->defaultLocation->city)) ? $data->defaultLocation->city->city : '';
            } elseif ($data->defaultLocation->entityType == 'subLocation') {
                $location = (!empty($data->defaultLocation->subLocation)) ? $data->defaultLocation->subLocation->id : '';
                $locationName = (!empty($data->defaultLocation->subLocation)) ? $data->defaultLocation->subLocation->subLocationName : '';
            } else {
                $location = '';
                $locationName = '';
            }
        } else {
            $location = '';
            $locationName = '';
        }


        if (!empty($data->lastName)) {
            $fullName = str_replace("  ", " ", ucfirst($data->lastName) . ',' . ' ' . ucfirst($data->firstName) . ' ' . ucfirst($data->middleName));
        } else {
            $fullName = str_replace("  ", " ", ucfirst($data->firstName) . ' ' . ucfirst($data->middleName));
        }

        /*$detail['fullName'] = $fullName;     
        $detail['id'] = $data->udid; 
        $detail['defaultLocationName'] =  $locationName;
        $detail['defaultLocation'] = $location;
        $detail['defaultLocationEntityType'] = (!empty($data->defaultLocation))?$data->defaultLocation->entityType:'';
        $detail['defaultLocationHierarchy'] = (!empty($data->defaultLocation))?$data->defaultLocation->locationsHierarchy:'';
        $detail['providerId'] = (!empty($data->provider))?$data->provider->id:'';
        $detail['providerName'] = (!empty($data->provider))?$data->provider->name:'';
        $datas = $data->getAttributes();    
      
        $excludeList = array('genderId','networkId','specializationId','designationId','id','userId');
        foreach($datas as $key=>$value){   
            if(!in_array($key,$excludeList)){                
                $valueDetail = (!empty($value)) ? $value : '';
                if(is_string($valueDetail)){
                    $valueDetail= ucfirst($valueDetail);
                }   
                $detail[$key] = $valueDetail;    
            }             
        }  
        return $detail;*/

        return [
            'id' => $data->udid,
            'user_id' => $data->userId,
            'fullName' => $fullName,
            'firstName' => ucfirst($data->firstName),
            'middleName' => ucfirst($data->middleName),
            'lastName' => $data->lastName ? ucfirst($data->lastName) : '',
            'title' => $data->title ? ucfirst($data->title) : '',
            'timeZone' => (!empty($data->title)) ? ucfirst($data->title) : '',
            'extension' => $data->extension,
            'designationId' => $data->designationId,
            'genderId' => $data->genderId,
            'specializationId' => $data->specializationId,
            'networkId' => $data->networkId,
            'designation' => $data->designation ? $data->designation->name : '',
            'gender' => $data->gender ? $data->gender->name : '',
            'phoneNumber' => $data->phoneNumber,
            'network' => $data->network ? $data->network->name : '',
            'specialization' => $data->specialization ? $data->specialization->name : '',
            'createdAt' => strtotime($data->createdAt),
            'isActive' => $data->isActive ? true : false,
            'type' => $data->type ? $data->type->name : '',
            'typeId' => $data->type ? $data->type->id : '',
            'organisation' => $data->organisation ? $data->organisation : '',
            'location' => $data->location ? $data->location : '',
            'building' => $data->building ? $data->building : '',
            'email' => $data->user ? $data->user->email : '',
            'defaultLocation' =>  $location,
            'defaultLocationEntityType' => (!empty($data->defaultLocation)) ? $data->defaultLocation->entityType : '',
            'defaultLocationHierarchy' => (!empty($data->defaultLocation)) ? $data->defaultLocation->locationsHierarchy : '',
            'defaultLocationName' =>  $locationName,
           // 'defaultProvider' => $data->defaultProvider ? fractal()->item($data->defaultProvider)->transformWith(new StaffProviderTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : '',
           // 'providers' => $data->staffProvider ? fractal()->collection($data->staffProvider)->transformWith(new StaffProviderTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : array(),
            'providerId' => (!empty($data->defaultProvider)) ?  $data->defaultProvider->providers?$data->defaultProvider->providers->id:'' : '',
            'providerName' => (!empty($data->defaultProvider)) ?  $data->defaultProvider->providers?$data->defaultProvider->providers->name:'' : '',
            'programs' => (!empty($data->program))  ? fractal()->item($data->program->program)->transformWith(new ProgramTransformer(false))->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : array(),
        ];
    }
}
