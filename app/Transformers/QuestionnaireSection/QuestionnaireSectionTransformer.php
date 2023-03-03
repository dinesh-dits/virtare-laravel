<?php

namespace App\Transformers\QuestionnaireSection;

use League\Fractal\TransformerAbstract;
use App\Transformers\QuestionnaireSection\QuestionSectionTransformer;
 

class QuestionnaireSectionTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */

    protected $showData;

    public function __construct($showData = true)
    {
        $this->showData = $showData;
    }
    
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
        return[ 
            'id'=> $data->udid,
            'sectionName'=>$data->sectionName,
            'entityType'=>$data->entityType,
            'referenceId'=>$data->referenceId,
            'isActive' => $data->isActive ? True : False,
            'questionSection' => $data->questionSection?fractal()->collection($data->questionSection)->transformWith(new QuestionSectionTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():""
        ];
       
    }
}
