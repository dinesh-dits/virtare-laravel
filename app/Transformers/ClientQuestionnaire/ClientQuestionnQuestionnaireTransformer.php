<?php

namespace App\Transformers\ClientQuestionnaire;

use App\Models\GlobalCode\GlobalCode;
use App\Models\Questionnaire\Question;
use League\Fractal\TransformerAbstract;
use App\Transformers\Questionnaire\QuestionTransformer;

class ClientQuestionnQuestionnaireTransformer extends TransformerAbstract
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
        if(isset($data->entityType)){
            $entity = GlobalCode::where("id",$data->entityType)->first();
            if($entity){
                $entity = $entity->name;
            }else{
                $entity = Null;
            }
        }
        
        $templateType = Null;
        if(isset($data->questionnaireTemplate) && !empty($data->questionnaireTemplate)){
            $templateType = GlobalCode::where("id",$data->questionnaireTemplate->templateTypeId)->first();
            if($templateType){
                $templateType = $templateType->name;
            }
        }

        // foreach($data["question"] as $v){
        //     $data = Question::where('questionId', $v->questionId)->first();
        //     $question[] = fractal()->item($data)->transformWith(new QuestionTransformer())->toArray();
        // }

        

        return[ 
            'id'=> $data->udid,
            'questionnaireTempleteId'=>$data->questionnaireTemplateId,
            'templateName'=>(!empty($data->questionnaireTemplate))?$data->questionnaireTemplate->templateName:"",
            'templateTypeId'=>(!empty($data->questionnaireTemplate))?$data->questionnaireTemplate->templateTypeId:"",
            'templateType'=>(!empty($data->questionnaireTemplate))?$templateType:"",
            'referenceId'=>$data->referenceId, 
            'entityType'=>$data->entityType, 
            'entity'=>$entity,
            'isActive' => $data->isActive ? True : False,
            "question" => $data->question
        ];
      
    }
}
