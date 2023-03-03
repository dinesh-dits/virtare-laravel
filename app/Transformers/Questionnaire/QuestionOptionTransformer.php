<?php

namespace App\Transformers\Questionnaire;

use Illuminate\Support\Facades\DB;
use App\Models\Questionnaire\Question;
use League\Fractal\TransformerAbstract;
use App\Models\Questionnaire\QuestionChanges;
use App\Models\Questionnaire\AssignOptionQuestion;
 

class QuestionOptionTransformer extends TransformerAbstract
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
        if(isset($data->questionOptionId)){

            // from assignQuestionOPtion
            $questionArr = [];
            $questionObjAr = [];
            $questionObj = [];
            $assignQuestion = $data->assignQuestion->toArray();
          
            if(isset($data->assignQuestion) && !empty($assignQuestion)){
                $questionIdArr = [];
                foreach($data->assignQuestion as $q){
                    $questionIdArr[] = $q->questionId;
                }

                if(!empty($questionIdArr)){
                    $questionObjAr = Question::whereIn("questionId",$questionIdArr)
                        ->where("isActive",1)->get();
                    if(isset($data["questionnaireSectionIdfdfdf"])){
                        $questionUpdate = QuestionChanges::where("questionId",$questionIdArr);
                        //                     if($parentId){
                        //                         $questionUpdate->where("parentId",$parentId);
                        //                     }
                        //                     if($childId){
                        //                         $questionUpdate->where("childId",$childId);
                        //                     }
                                        $questionUpdate->where("sectionId",$data["questionnaireSectionId"]);
                                        $questionUpdate->where("entityType","template");
                        $questionUpdate = $questionUpdate->first();
                        if($questionUpdate){
                            $questionDataObj["questionUpdate"] = $questionUpdate;
                            $questionDataObj["question"] = $questionUpdate->getQuestion;
                            $questionObj = !empty($questionDataObj)?fractal()->item($questionDataObj)->transformWith(new QuestionChangesTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"";
                        }else{
                            $questionObj = fractal()->collection($questionObjAr)->transformWith(new QuestionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
                        }
                    }else{
                        $questionObj = fractal()->collection($questionObjAr)->transformWith(new QuestionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
                    }    
                }
            }

            // $question = Question::where("referenceId",$data->questionOptionId)
            // ->where("entityType","questionOptions")
            // ->get();
        }else{
            // $question = [];
            $questionArr = [];
        }

        return[
            'id'=> $data->udid,
            'optionId'=> $data->questionOptionId,
            'option'=> $data->options,
            'defaultOption'=>$data->defaultOption,
            'answer'=>$data->answer,
            'score'=> $data->score,
            'program'=>$data->program? fractal()->collection($data->program)->transformWith(new QuestionOptionProgramTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():'',
            'question'=> $questionObj?$questionObj:[],
            // 'question'=> $questionArr?$questionArr:[],
        ];
    }
}
