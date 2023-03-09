<?php

namespace App\Transformers\PatientNew;

use League\Fractal\TransformerAbstract;

class PatientNewTransformer extends TransformerAbstract
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

        return [
           'udid'=>$data->udid,
           'flag'=>'',
           'flagId'=>'',
           'color'=>'',
           'fullName' => (!empty($data->contact))?str_replace("  ", " ", ucfirst($data->contact->lastName) . ',' . ' ' . ucfirst($data->contact->firstName) . ' ' . ucfirst($data->contact->middleName)):'',
           'careTeam'=>'',
           'lastReading'=>'',
           'messages'=>'',
           'openTasks'=>'',
           'nurse'=>'',
           'lastContacted'=>'',
           'todayReadings'=>'',
           'unReviewedReadings'=>'',
           'riskOfCompliance'=>'',
           'outOfCompliance'=>'',
           'shouldBrDischarged'=>'',
        ];
    }
}
