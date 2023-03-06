<?php

namespace App\Transformers\Questionnaire;

use Illuminate\Support\Facades\DB;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Questionnaire\Question;
use League\Fractal\TransformerAbstract;
use App\Models\Questionnaire\QuestionChanges;
use App\Models\QuestionnaireSection\QuestionSection;
use App\Transformers\Questionnaire\QuestionOptionTransformer;
use App\Services\Api\QuestionChangeService; 

class QuestionChangesTransformer extends TransformerAbstract
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
        $q = $data["question"];
        // $questionOption = new \stdClass();
        // $questionOption = $q->questionOption;
        $questionnaireSectionId = "";
        if(isset($data["questionnaireSectionId"])){
            $questionnaireSectionId = $data["questionnaireSectionId"];
        }
       
        $data = $data["questionUpdate"];
        if(isset($data->dataObj)){
            $dataObj = json_decode($data->dataObj);
        }

        $isAssign = false;
        if(isset($data->sectionId) && $data->sectionId > 0) {
            $isAssign = true;
        }

        $dataType = [];
        if(isset($dataObj->dataTypeId)){
            $dataType = GlobalCode::where("id",$dataObj->dataTypeId)->first();
        }
        
        $questionType = [];
        if(isset($dataObj->questionType)){
            $questionType = GlobalCode::where("id",$dataObj->questionType)->first();
        }

        // print_r($dataObj->questionnaireCustomField);
        // die;
        $customFieldDataObj = array();
        if(isset($dataObj->questionnaireCustomField)){
            foreach($dataObj->questionnaireCustomField as $k => $v){
                $customFieldDataObj[$k] = $v;
            }
        }

        $optionArr = [];
        if(isset($data->questionOption)){
            foreach($data->questionOption as $option){

                if(isset($option->questionOptionId)){
                    // from assignQuestionOPtion
                    $questionArr = [];
                    $questionObjAr = [];
                    $questionObj = [];
                    $assignQuestion = $option->assignQuestion->toArray();
                  
                    if(isset($option->assignQuestion) && !empty($assignQuestion)){
                        $questionIdArr = [];
                        foreach($option->assignQuestion as $q){
                            $questionIdArr[] = $q->questionId;
                                $questionUpdate = QuestionChanges::whereIn("questionId",$q->questionId);
                                $questionUpdate->where("sectionId",$questionnaireSectionId);
                                $questionUpdate->where("entityType","template");
                                $questionUpdate = $questionUpdate->first();
                                if(isset($questionUpdate->questionId)){
                                    $questionDataObj["questionUpdate"] = $questionUpdate;
                                    $questionDataObj["question"] = $questionObjAr;
                                    $questionObj = !empty($questionDataObj)?fractal()->item($questionDataObj)->transformWith(new QuestionChangesTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"";
                                }

                        }
        
                        if(!empty($questionIdArr)){
                            $questionObjAr = Question::whereIn("questionId",$questionIdArr)
                                ->where("isActive",1)->get();
                            if(!empty($questionnaireSectionId)){
                                $questionUpdate = QuestionChanges::whereIn("questionId",$questionIdArr);
                                //                     if($parentId){
                                //                         $questionUpdate->where("parentId",$parentId);
                                //                     }
                                //                     if($childId){
                                //                         $questionUpdate->where("childId",$childId);
                                //                     }
                                                $questionUpdate->where("sectionId",$questionnaireSectionId);
                                                $questionUpdate->where("entityType","template");
                                                
                                $questionUpdate = $questionUpdate->get();
                                
                                if($questionUpdate){
                                    $questionDataObj["questionUpdate"] = $questionUpdate;
                                    $questionDataObj["question"] = $questionObjAr;
                                    $questionObj = QuestionChangeService::getQuestion($questionDataObj);
                                    // die("dd");
                                }else{
                                    $questionObj = fractal()->collection($questionObjAr)->transformWith(new QuestionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
                                }
                            }else{
                                $questionObj = fractal()->collection($questionObjAr)->transformWith(new QuestionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
                            }    
                        }
                    }

                    $optionArr[] = [
                        'id'=> $option->udid,
                        'optionId'=> $option->questionOptionId,
                        'option'=> $option->options,
                        'defaultOption'=>$option->defaultOption,
                        'answer'=>$option->answer,
                        'score'=> $option->score,
                        'program'=>$option->program? fractal()->collection($option->program)->transformWith(new QuestionOptionProgramTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():'',
                        'question'=> $questionObj?$questionObj:[]
                        ];

                }else{
                    // $question = [];
                    $questionArr = [];
                }

                
            }
        }

        return[
            'id'=> $q->udid,
            'questionId'=> $data->questionId,
            'question'=>$dataObj->question,
            'isAssign'=>$isAssign,
            'dataTypeId'=>$dataObj->dataTypeId,
            'dataType'=>(!empty($dataType->name))?$dataType->name:'',
            'questionTypeId'=>(!empty($dataObj->questionType))?$dataObj->questionType:'',
            'questionType'=>(!empty($questionType->name))?$questionType->name:'',
            'isActive'  => $data->isActive ? True : False,
            'score'=>(!empty($data->score))? $data->score:'',
            'options'=> (!empty($optionArr))?$optionArr:[],
            'questionnaireCustomField'=> $customFieldDataObj,
            'tags'=>isset($data->tags)?$data->tags:[]
        ];
      
    }
}
