<?php

namespace App\Transformers\QuestionnaireSection;

use League\Fractal\TransformerAbstract;
use App\Transformers\QuestionnaireSection\QuestionSectionTransformer;
use App\Transformers\QuestionnaireSection\QuestionnaireSectionTransformer;
 

class QuestionnaireQuestionSectionTransformer extends TransformerAbstract
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
            'questionnaireSectionId'=>$data->questionnaireSectionId,
            'questionnaireTemplateId'=>$data->referenceId,
            'entityType'=>$data->entityType,
            'referenceId'=>$data->referenceId,
            'isActive' => $data->isActive ? True : False,
            'questionniareSection' => (!empty($data->questionnaireSection)) &&  count($data->questionnaireSection) > 0 ? fractal()->collection($data->questionnaireSection)->transformWith(new QuestionnaireSectionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():[],
        ];
      
    }
}
