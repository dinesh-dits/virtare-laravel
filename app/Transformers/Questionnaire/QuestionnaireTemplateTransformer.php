<?php

namespace App\Transformers\Questionnaire;

use App\Models\GlobalCode\GlobalCode;
use League\Fractal\TransformerAbstract;
use App\Models\Questionnaire\QuestionnaireField;
use App\Models\Questionnaire\ClientQuestionnaireAssign;
use App\Transformers\QuestionnaireSection\QuestionnaireQuestionSectionTransformer;
 

class QuestionnaireTemplateTransformer extends TransformerAbstract
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
        $templateType = "";
        if(isset($data->templateType->name)){
            $templateType = $data->templateType->name;
        }else{
            if(isset($data->templateType)){
                $templateType = $data->templateType;
            }
        }

        $dataObj = array();
        if(isset($data->questionnaireField)){
            $questionnaireField = explode(",",$data["questionnaireFields"]);
            foreach($data->questionnaireField as $v){
                $dataObj[$v["parameterKey"]] = $v["parameterValue"];
                if(in_array($v["parameterKey"],$questionnaireField)){
                    $global = GlobalCode::where("id",$v["parameterValue"])->first();
                    $typeKeyName = str_replace("Id","",$v["parameterKey"]);
                    if(isset($global->name)){
                        $dataObj[$typeKeyName] = $global->name;
                    }else{
                        $dataObj[$typeKeyName] = "";
                    }
                }
            }
        }

        $isAssign = false;
        if(isset($data->questionnaireTemplateId)){
            $questionnaireType = QuestionnaireField::getQuestionnaireField("questionnaireTemplate",$data->questionnaireTemplateId,"questionnaireTypeId");

            // check questionniare assigned or not assigned.
            $clientQuestionnaireAssign = ClientQuestionnaireAssign::with("clientQuestionnaireTemplate")
                                        ->where("questionnaireTemplateId",$data->questionnaireTemplateId)
                                        ->first();
            if(isset($clientQuestionnaireAssign->clientQuestionnaireTemplate->questionnaireTemplateId)){
                $isAssign = true;
            }
        }else{
            $questionnaireType = "";
        }
        $obj = [ 
            'id'=> $data->udid,
            'templateName'=>$data->templateName,
            'isAssign'=>$isAssign,
            'templateTypeId'=>$data->templateTypeId?$data->templateTypeId:'',
            'templateType'=>(!empty($data->templateType))?$templateType:"",
            'questionnaireTypeId' => isset($questionnaireType->parameterValue)?$questionnaireType->parameterValue:"",
            'questionnaireType' => isset($questionnaireType->getOptionName->name)?$questionnaireType->getOptionName->name:"",
            'tags'=>isset($data->tags)?$data->tags:[],
            'questionnaireCustomField'=>$dataObj,
        ];
        
        if(isset($data["id"]) && !empty($data["id"])){
            $obj["questionnaireQuestion"] = $this->showData && isset($data->questionnaireQuestion)? fractal()->collection($data->questionnaireQuestion)->transformWith(new QuestionnaireQuestionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():[];
            $obj["assignedSection"] = (!empty($data->assignedSection))?fractal()->collection($data->assignedSection)->transformWith(new QuestionnaireQuestionSectionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():[];
        }else{
            $obj["questionnaireQuestion"] = [];
            $obj["assignedSection"] = "[]";
        }
        return $obj;
      
    }
}
