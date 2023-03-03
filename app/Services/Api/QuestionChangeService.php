<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use App\Models\User\User;
use App\Models\Staff\Staff;
use Illuminate\Support\Str;
use App\Models\Program\Program;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Questionnaire\Question;
use App\Models\Questionnaire\QuestionOption;
use App\Models\Questionnaire\QuestionChanges;
use App\Models\QuestionnaireSection\QuestionSection;
use App\Transformers\Questionnaire\QuestionTransformer;
use App\Transformers\Questionnaire\QuestionChangesTransformer;
use App\Transformers\Questionnaire\QuestionOptionProgramTransformer;

class QuestionChangeService
{
    public static function getQuestion($dataArr)
    {
        try {
            $obbArr = array();

            //     print_r($obj["question"]);
            // die;
            $q = $dataArr["question"];
            $questionnaireSectionId = "";
            if (isset($obj["questionnaireSectionId"])) {
                $questionnaireSectionId = $obj["questionnaireSectionId"];
            }

            foreach ($dataArr["questionUpdate"] as $data) {
                if (isset($data->dataObj)) {
                    $dataObj = json_decode($data->dataObj);
                }

                $isAssign = false;
                if (isset($data->sectionId) && $data->sectionId > 0) {
                    $isAssign = true;
                }

                $dataType = [];
                if (isset($dataObj->dataTypeId)) {
                    $dataType = GlobalCode::where("id", $dataObj->dataTypeId)->first();
                }

                $questionType = [];
                if (isset($dataObj->questionType)) {
                    $questionType = GlobalCode::where("id", $dataObj->questionType)->first();
                }

                // print_r($dataObj->questionnaireCustomField);
                // die;
                $customFieldDataObj = array();
                if (isset($dataObj->questionnaireCustomField)) {
                    foreach ($dataObj->questionnaireCustomField as $k => $v) {
                        $customFieldDataObj[$k] = $v;
                    }
                }

                $optionArr = [];
                if (isset($data->questionOption)) {
                    foreach ($data->questionOption as $option) {

                        if (isset($option->questionOptionId)) {
                            // from assignQuestionOPtion
                            $questionArr = [];
                            $questionObjAr = [];
                            $questionObj = [];
                            $assignQuestion = $option->assignQuestion->toArray();

                            if (isset($option->assignQuestion) && !empty($assignQuestion)) {
                                $questionIdArr = [];
                                foreach ($option->assignQuestion as $q) {
                                    $questionIdArr[] = $q->questionId;
                                }

                                if (!empty($questionIdArr)) {
                                    $questionObjAr = Question::whereIn("questionId", $questionIdArr)
                                        ->where("isActive", 1)->get();
                                    if (!empty($questionnaireSectionId)) {
                                        $questionUpdate = QuestionChanges::whereIn("questionId", $questionIdArr);
                                        //                     if($parentId){
                                        //                         $questionUpdate->where("parentId",$parentId);
                                        //                     }
                                        //                     if($childId){
                                        //                         $questionUpdate->where("childId",$childId);
                                        //                     }
                                        $questionUpdate->where("sectionId", $questionnaireSectionId);
                                        $questionUpdate->where("entityType", "template");

                                        $questionUpdate = $questionUpdate->get();
                                        $question = Question::where("questionId", $option->questionId)->first();

                                        if ($questionUpdate) {
                                            $questionDataObj["questionUpdate"] = $questionUpdate;
                                            $questionDataObj["question"] = $question;
                                            $questionObj = !empty($questionDataObj) ? fractal()->collection($questionDataObj)->transformWith(new QuestionChangesTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "";
                                        } else {
                                            $questionObj = fractal()->collection($questionObjAr)->transformWith(new QuestionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
                                        }
                                    } else {
                                        $questionObj = fractal()->collection($questionObjAr)->transformWith(new QuestionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
                                    }
                                }
                            }

                            $optionArr[] = [
                                'id' => $option->udid,
                                'optionId' => $option->questionOptionId,
                                'option' => $option->options,
                                'defaultOption' => $option->defaultOption,
                                'answer' => $option->answer,
                                'score' => $option->score,
                                'program' => $option->program ? fractal()->collection($option->program)->transformWith(new QuestionOptionProgramTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : '',
                                'question' => $questionObj ? $questionObj : []
                            ];

                        } else {
                            $questionArr = [];
                        }


                    }
                }

                $obbArr[] = [
                    // 'id'=> $q->udid,
                    'questionId' => $data->questionId,
                    'question' => $dataObj->question,
                    'isAssign' => $isAssign,
                    'dataTypeId' => $dataObj->dataTypeId,
                    'dataType' => (!empty($dataType->name)) ? $dataType->name : '',
                    'questionTypeId' => (!empty($dataObj->questionType)) ? $dataObj->questionType : '',
                    'questionType' => (!empty($questionType->name)) ? $questionType->name : '',
                    'isActive' => $data->isActive ? True : False,
                    'score' => (!empty($data->score)) ? $data->score : '',
                    'options' => (!empty($optionArr)) ? $optionArr : [],
                    'questionnaireCustomField' => $customFieldDataObj,
                    'tags' => isset($data->tags) ? $data->tags : []
                ];
            }
            return $obbArr;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public static function getAllOptionQuestion($dataArr, $sectionId, $editType)
    {
        try {
            $optionArr = array();
            $optionId = "";
            foreach ($dataArr as $data) {
                if (isset($data["questionOptionId"])) {
                    $optionId = $data["questionOptionId"];
                }
                if (isset($data->questionOptionId)) {

                    // from assignQuestionOPtion
                    $questionObjAr = [];
                    $questionObj = [];
                    $assignQuestion = $data->assignQuestion->toArray();
                    $questionIdArr = [];
                    if (isset($data->assignQuestion) && !empty($assignQuestion)) {
                        foreach ($data->assignQuestion as $q) {
                            $questionIdArr[] = $q->questionId;
                        }
                    } else {
                        $question = Question::where("referenceId", $data->questionOptionId)
                            ->where("entityType", "questionOptions")
                            ->get();
                        $questionObj = QuestionChangeService::getAllQuestion($optionId, $question, $sectionId, $editType);
                    }

                    if (!empty($questionIdArr)) {
                        $questionObjAr = Question::whereIn("questionId", $questionIdArr);
                        if ($sectionId) {
                            $questionObjAr->with('questionOption.assignQuestion', function ($q) use ($sectionId) {
                                $q->where("sectionId", $sectionId);
                            });
                        }
                        $questionObjAr = $questionObjAr->where("isActive", 1)->get();
                        $questionObj = QuestionChangeService::getAllQuestion($optionId, $questionObjAr, $sectionId, $editType);
                        // $questionObj = $this->getAllQuestion($questionObjAr,$sectionId);
                        // $questionObj = fractal()->collection($questionObjAr)->transformWith(new QuestionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
                    }
                }

                $optionArr[] = [
                    'id' => $data["udid"],
                    'sectionId' => $sectionId,
                    'questionId' => $data["questionId"],
                    'optionId' => $data["questionOptionId"],
                    'option' => $data["options"],
                    'defaultOption' => $data["defaultOption"],
                    'answer' => $data["answer"],
                    'score' => $data["score"],
                    'program' => $data["program"] ? fractal()->collection($data["program"])->transformWith(new QuestionOptionProgramTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : '',
                    'question' => $questionObj ? $questionObj : [],
                    // 'question'=> $questionArr?$questionArr:[],
                ];
            }
            return $optionArr;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public static function getAllQuestion($optionId, $dataArr, $sectionId, $editType)
    {
        try {
            $objQuestionArr = array();
            foreach ($dataArr as $data) {
                $dataObj = array();
                $questionOption = array();
                if (isset($data->questionnaireField)) {
                    foreach ($data->questionnaireField as $v) {
                        $dataObj[$v["parameterKey"]] = $v["parameterValue"];
                    }
                }


                $isAssign = false;
                if (isset($data->questionId)) {
                    $questionSection = QuestionSection::where("questionId", $data->questionId)->first();
                    if (isset($questionSection->questionId)) {
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

                $questionOption = $data->questionOption;

                $questionUpdate = QuestionChanges::where("questionId", $data->questionId);
                if (isset($sectionId) && !empty($sectionId)) {
                    $questionUpdate->where("sectionId", $sectionId);
                }

                if (isset($optionId) && !empty($optionId)) {
                    $questionUpdate->where("childId", $optionId);
                }
                $questionUpdate->where("entityType", $editType);
                $questionUpdate = $questionUpdate->first();

                $dataObj = "";
                if (isset($questionUpdate->dataObj)) {
                    $dataObj = json_decode($questionUpdate->dataObj);
                }

                $dataType = [];
                if (isset($dataObj->dataTypeId)) {
                    $dataType = GlobalCode::where("id", $dataObj->dataTypeId)->first();
                }

                $questionType = [];
                if (isset($dataObj->questionType)) {
                    $questionType = GlobalCode::where("id", $dataObj->questionType)->first();
                }

                $optionObjaRR = [];
                $optionCustomData = [];
                if ($sectionId) {
                    $optionObjaRR = QuestionChanges::where("sectionId", $sectionId);
                    $optionObjaRR->where("questionId", $data->questionId);
                    if ($optionId > 0) {
                        $optionObjaRR->where("childId", $optionId);
                    }
                    $optionObjaRR->where("entityType", "templateOption");
                    $optionObjaRR = $optionObjaRR->first();
                }

                if (isset($optionObjaRR->udid)) {
                    $optionCustomData = QuestionChangeService::getAllCustomOption($optionObjaRR, $sectionId, $editType);
                } else {
                    $optionObjData = QuestionChangeService::getAllOptionQuestion($questionOption, $sectionId, $editType);
                }

                $objQuestionArr[] = [
                    'id' => $data->udid,
                    'questionId' => $data->questionId,
                    'optionId' => $optionId,
                    'question' => (!empty($dataObj)) ? $dataObj->question : $data->question,
                    'isAssign' => $isAssign,
                    'dataTypeId' => (!empty($dataObj)) ? $dataObj->dataTypeId : $data->dataTypeId,
                    'dataType' => (!empty($dataType)) ? $dataType->name : $data->questionsDataType->name,
                    'questionTypeId' => (!empty($dataObj->questionType)) ? $dataObj->questionType : $data->questionType,
                    'questionType' => (isset($questionType->name)) ? $questionType->name : (isset($data->questionsType->name)) ? $data->questionsType->name : '',
                    'isActive' => $data->isActive ? True : False,
                    'score' => (!empty($data->score)) ? $data->score->score : '',
                    // 'options'=> $data->questionOption?fractal()->collection($questionOption)->transformWith(new QuestionOptionTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():[],
                    'options' => $optionObjaRR ? $optionCustomData : $optionObjData,
                    // 'options'=> $optionObjData,
                    'questionnaireCustomField' => $dataObj,
                    'tags' => isset($data->tags) ? $data->tags : []
                ];
            }
            return $objQuestionArr;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public static function getAllCustomOption($optionObjaRR, $sectionId, $editType)
    {
        try {
            if (isset($optionObjaRR->dataObj)) {
                $optionData = json_decode($optionObjaRR->dataObj);
                // print_r($optionData);
                // die;
                $optionArr = [];
                if (!empty($optionData->option)) {
                    $i = 0;
                    foreach ($optionData->option as $data) {
                        if (isset($data->id)) {
                            // from assignQuestionOPtion
                            $questionOption = QuestionOption::where("udid", $data->id)->first();
                            if (isset($questionOption->udid)) {
                                $questionObjAr = [];
                                $questionObj = [];
                                $optionId = $questionOption->questionOptionId;
                                $assignQuestion = $questionOption->assignQuestion->toArray();
                                $questionIdArr = [];
                                if (isset($questionOption->assignQuestion) && !empty($assignQuestion)) {
                                    foreach ($questionOption->assignQuestion as $q) {
                                        $questionIdArr[] = $q->questionId;
                                    }
                                }

                                if (!empty($questionIdArr)) {
                                    $questionObjAr = Question::whereIn("questionId", $questionIdArr);
                                    if ($sectionId) {
                                        $questionObjAr->with('questionOption.assignQuestion', function ($q) use ($sectionId) {
                                            $q->where("sectionId", $sectionId);
                                        });
                                    }
                                    $questionObjAr = $questionObjAr->where("isActive", 1)->get();
                                    $questionObj = QuestionChangeService::getAllQuestion($optionId, $questionObjAr, $sectionId, $editType);
                                }
                            }

                        }

                        if (!empty($data->program)) {
                            $program = QuestionChangeService::getCustomProgram($data->program);
                        } else {
                            $program = "";
                        }

                        if (isset($data->id)) {
                            $optionArr[$i] = [
                                'id' => $data->id,
                                'sectionId' => $sectionId,
                                'optionId' => $optionId,
                                'option' => $data->labelName,
                                'defaultOption' => $data->defaultOption,
                                'answer' => $data->answer,
                                'score' => (!empty($data->labelScore)) ? $data->labelScore : '',
                                'program' => (!empty($program)) ? $program : [],
                                'question' => $questionObj ? $questionObj : [],
                            ];
                        } else {
                            $optionArr[$i] = [];
                        }

                        $i++;
                    }
                }
                return $optionArr;
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public static function getCustomProgram($programs)
    {
        try {
            $proObj = [];
            $programObj = [];
            $programName = "";
            if (count($programs) > 0) {
                $i = 1;
                foreach ($programs as $program) {
                    $programObj = Program::where("id", $program->programId)->first();
                    if (isset($programObj->udid)) {
                        $programName = $programObj->name;
                    }

                    $proObj[] = [
                        'id' => $i,
                        'programId' => $program->programId,
                        'program' => $programName,
                        'score' => $program->programScore ? $program->programScore : ''
                    ];
                    $i++;
                }
            }
            return $proObj;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
