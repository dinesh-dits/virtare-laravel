<?php

namespace App\Transformers\QuestionnaireSection;

use App\Models\Questionnaire\Question;
use League\Fractal\TransformerAbstract;
use App\Models\Questionnaire\QuestionChanges;
use App\Transformers\Questionnaire\QuestionTransformer;
use App\Transformers\Questionnaire\QuestionChangesTransformer;
 

class QuestionSectionTransformer extends TransformerAbstract
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
        $questionDataObj  = array();
        $question  = array();
        if(isset($data->questionId)){
            $questionUpdate = QuestionChanges::where("questionId",$data->questionId);
            //                     if($parentId){
            //                         $questionUpdate->where("parentId",$parentId);
            //                     }
            //                     if($childId){
            //                         $questionUpdate->where("childId",$childId);
            //                     }
                            $questionUpdate->where("sectionId",$data->questionnaireSectionId);
                            $questionUpdate->where("entityType","template1");
            $questionUpdate = $questionUpdate->first();
            $question = Question::where("questionId",$data->questionId);
                        if(isset($data->questionnaireSectionId)){
                            $question->with('questionOption.assignQuestion', function($q) use($data){
                                $q->where("sectionId", $data->questionnaireSectionId);
                            });
                        }
            $question = $question->first();
            if($questionUpdate){
                $questionDataObj["questionUpdate"] = $questionUpdate;
                $questionDataObj["question"] = $question;
                $questionDataObj["questionnaireSectionId"] = $data->questionnaireSectionId;
                $questionDataObj["editType"] = "template";
                $questionObj = !empty($questionDataObj)?fractal()->item($questionDataObj)->transformWith(new QuestionChangesTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"";
            }else{
                $question["questionnaireSectionId"] = $data->questionnaireSectionId;
                $question["editType"] = "template";
                if(isset($question->udid)){
                    $questionObj = !empty($question)?fractal()->item($question)->transformWith(new QuestionTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():"";
                }else{
                    $questionObj = "";
                }
            }
        }

        return[ 
            'id'=> $data->udid,
            'questionnaireSectionId'=> $data->questionnaireSectionId,
            'questionId'=> $data->questionId,
            'isActive' => $data->isActive ? True : False,
            'question' => !empty($questionObj)?$questionObj:""
        ];
      
    }
}
