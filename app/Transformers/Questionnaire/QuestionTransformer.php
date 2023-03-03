<?php

namespace App\Transformers\Questionnaire;

use Illuminate\Support\Facades\DB;
use App\Models\GlobalCode\GlobalCode;
use League\Fractal\TransformerAbstract;
use App\Services\Api\QuestionChangeService;
use App\Models\Questionnaire\QuestionChanges;
use App\Models\QuestionnaireSection\QuestionSection;
use App\Transformers\Questionnaire\QuestionOptionTransformer;
 

class QuestionTransformer extends TransformerAbstract
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
        $dataObjCustomField = array();
        $questionOption = array();
        if(isset($data->questionnaireField)){
            foreach($data->questionnaireField as $v){
                $dataObjCustomField[$v["parameterKey"]] = $v["parameterValue"];
            }
        }

        $isAssign = false;
        if(isset($data->questionId)){
            $questionSection = QuestionSection::where("questionId",$data->questionId)->first();
            if(isset($questionSection->questionId)){
                $isAssign = true;
            }
        }

        // $questionUpdate = QuestionChanges::where("questionId",$data->questionId);
        //                     if($parentId){
        //                         $questionUpdate->where("parentId",$parentId);
        //                     }
        //                     if($childId){
        //                         $questionUpdate->where("childId",$childId);
        //                     }
        //                     $questionUpdate->where("entityType",$editType);
        $sectionId = 0;
        if(isset($data["questionnaireSectionId"])){
            $questionOption["questionnaireSectionId"] = $data["questionnaireSectionId"];
            $sectionId = $data["questionnaireSectionId"];
        }
        
        $editType = "question";
        if(isset($data["editType"])){
            $editType = $data["editType"];
        }

        if(isset($data->questionOption)){
            $questionOption = $data->questionOption;
        }
         
        $questionUpdate = QuestionChanges::where("questionId",$data->questionId);
                            if(isset($data["questionnaireSectionId"])){
                                $questionUpdate->where("sectionId",$data["questionnaireSectionId"]);
                            }
                            $questionUpdate->where("entityType",$editType);
                            $questionUpdate = $questionUpdate->first();

        $dataObj = "";
        if(isset($questionUpdate->dataObj)){
            $dataObj = json_decode($questionUpdate->dataObj);
        }

        $dataType = [];
        if(isset($dataObj->dataTypeId)){
            $dataType = GlobalCode::where("id",$dataObj->dataTypeId)->first();
        }
        
        $questionType = [];
        if(isset($dataObj->questionType)){
            $questionType = GlobalCode::where("id",$dataObj->questionType)->first();
        }

        $customFieldDataObj = array();
        if(isset($dataObj->questionnaireCustomField)){
            foreach($dataObj->questionnaireCustomField as $k => $v){
                $customFieldDataObj[$k] = $v;
            }
        }

        $optionObjaRR = [];
            $optionCustomData =  [];
            if($sectionId){
                $optionObjaRR = QuestionChanges::where("sectionId",$sectionId);
                $optionObjaRR->where("questionId",$data->questionId);
                $optionObjaRR->where("entityType","templateOption");
                $optionObjaRR = $optionObjaRR->first();
            }

            if(isset($optionObjaRR->udid)){
                $optionCustomData = QuestionChangeService::getAllCustomOption($optionObjaRR,$sectionId,$editType);
            }else{
                $optionObjData = QuestionChangeService::getAllOptionQuestion($questionOption,$sectionId,$editType);
            }
            

        return[
            'id'=> $data->udid,
            'questionId'=> $data->questionId,
            'question'=>(!empty($dataObj))?$dataObj->question:$data->question,
            'isAssign'=>$isAssign,
            'dataTypeId'=>(!empty($dataObj))?$dataObj->dataTypeId:$data->dataTypeId,
            'dataType'=>(!empty($dataType))?$dataType->name:$data->questionsDataType->name,
            'questionTypeId'=>(!empty($dataObj->questionType))?$dataObj->questionType:$data->questionType,
            'questionType'=>(isset($questionType->name))?$questionType->name : isset($data->questionsType->name)? $data->questionsType->name:'',
            'isActive'  => $data->isActive ? True : False,
            'score'=>(!empty($data->score))? $data->score->score:'',
            // 'options'=> $data->questionOption?fractal()->collection($questionOption)->transformWith(new QuestionOptionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():[],
            // 'options'=> QuestionChangeService::getAllOptionQuestion($questionOption,$sectionId,$editType),
            'options'=> $optionObjaRR?$optionCustomData:$optionObjData,
            'questionnaireCustomField'=> $customFieldDataObj?$customFieldDataObj:$dataObjCustomField,
            'tags'=>isset($data->tags)?$data->tags:[]
        ];
      
    }
}
