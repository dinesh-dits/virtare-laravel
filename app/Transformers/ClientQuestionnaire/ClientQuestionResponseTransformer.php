<?php

namespace App\Transformers\ClientQuestionnaire;

use App\Models\GlobalCode\GlobalCode;
use League\Fractal\TransformerAbstract;
use App\Models\Questionnaire\ClientResponseAnswer;
use App\Models\QuestionnaireSection\QuestionnaireSection;
use App\Transformers\Questionnaire\QuestionTransformer;

class ClientQuestionResponseTransformer extends TransformerAbstract
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
        // Question::
        $cleintQuestionAns = "";
        $answer = "";
        if(isset($data->clientFillupQuestionnaireQuestionId) && !empty($data->clientFillupQuestionnaireQuestionId)){
            if(isset($data->questionnaireQuestion->dataTypeId) && $data->questionnaireQuestion->dataTypeId == "244"){
                $cleintQuestionAns = ClientResponseAnswer::where("clientFillupQuestionnaireQuestionId",$data->clientFillupQuestionnaireQuestionId)->where("isActive","1")->get();
                if(!empty($cleintQuestionAns)){
                    $answer = array();
                    foreach($cleintQuestionAns as $vall){
                        $answer[] = $vall->response;
                    }
                }
            }else{
                $cleintQuestionAns = ClientResponseAnswer::where("clientFillupQuestionnaireQuestionId",$data->clientFillupQuestionnaireQuestionId)->where("isActive","1")->first();
                if(isset($cleintQuestionAns->response)){
                    $answer = $cleintQuestionAns->response;
                }
            }
        }

        $questionnaireSection = "";
        if($data->entityType == "questionnaireSection"){
            $questionnaireSection = QuestionnaireSection::where("questionnaireSectionId",$data->referenceId)->first();
        }

        if(isset($data->questionnaireQuestion->dataTypeId)){
            $global = GlobalCode::where('id', $data->questionnaireQuestion->dataTypeId)->first();
        }else{
            $global = "";
        }

        return[
            "clientQuestionResponseUdid"=> $data->udid,
            "clientFillupQuestionnaireQuestionId" => $data->clientFillupQuestionnaireQuestionId,
            "questionnaireQuestionId" => $data->questionnaireQuestionId,
            "questionId" => isset($data->questionnaireQuestion->udid)?$data->questionnaireQuestion->udid:"",
            "entityType" => $data->entityType?$data->entityType:"",
            "referenceId" => isset($questionnaireSection->udid)?$questionnaireSection->udid:"",
            "sectionName" => isset($questionnaireSection->sectionName)?$questionnaireSection->sectionName:"",
            "answer" => isset($answer)?$answer:"",
            "dataTypeName" => isset($global->name)?$global->name:"",
            "dataTypeId" => isset($data->questionnaireQuestion->dataTypeId)?$data->questionnaireQuestion->dataTypeId:"",
            "question" => isset($data->questionnaireQuestion)?fractal()->item($data->questionnaireQuestion)->transformWith(new QuestionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"",
        ];
      
    }
}
