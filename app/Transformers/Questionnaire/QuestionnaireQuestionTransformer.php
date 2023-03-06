<?php

namespace App\Transformers\Questionnaire;

use League\Fractal\TransformerAbstract;
use App\Transformers\Questionnaire\QuestionTransformer;
use App\Transformers\QuestionnaireSection\QuestionnaireSectionTransformer;
 

class QuestionnaireQuestionTransformer extends TransformerAbstract
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
        $question = false;
        $questionnaireSection = false;
        if(isset($data->entityType) && !empty($data->question) && $data->entityType == "question"){
            $question = true;
        }
        
        if($data->entityType == "questionnaireSection"){
            $questionnaireSection = true;
        }

        return[
            "referenceId" => $data->referenceId,
            "entityType" => $data->entityType,
            "isActive" => $data->isActive?True:False,
            "question"=> $question?fractal()->item($data->question)->transformWith(new QuestionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():[],
            "questionnaireSection"=> (!empty($questionnaireSection)) && !empty($data->questionnaireSection)?fractal()->item($data->questionnaireSection)->transformWith(new QuestionnaireSectionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():[],
        ];
      
    }
}
