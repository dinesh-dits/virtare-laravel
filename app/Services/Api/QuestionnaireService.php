<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Carbon\Carbon;
use App\Models\Tag\Tag;
use App\Models\Tag\Tags;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Questionnaire\Question;
use Illuminate\Support\Facades\Storage;
use App\Models\Questionnaire\QuestionScore;
use App\Models\Questionnaire\QuestionOption;
use App\Models\Questionnaire\QuestionChanges;
use App\Models\Questionnaire\QuestionnaireField;
use App\Models\Questionnaire\AssignOptionQuestion;
use App\Models\Questionnaire\QuestionnaireQuestion;
use App\Models\Questionnaire\QuestionnaireTemplate;
use App\Models\Questionnaire\QuestionOptionProgram;
use App\Models\QuestionnaireSection\QuestionSection;
use App\Transformers\GlobalCode\GlobalCodeTransformer;
use App\Transformers\Questionnaire\QuestionTransformer;
use App\Models\Questionnaire\ClientQuestionnaireTemplate;
use App\Models\QuestionnaireSection\QuestionnaireSection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Transformers\Questionnaire\QuestionChangesTransformer;
use App\Transformers\Questionnaire\AssignTemplateUserTransformer;
use App\Transformers\Questionnaire\QuestionnaireFieldTransformer;
use App\Transformers\Questionnaire\QuestionnaireTemplateTransformer;

class QuestionnaireService
{
    public function questionnaireAdd($request)
    {
        try {
            $providerId = Helper::providerId();

            $otherData = [
                'udid' => Str::uuid()->toString(),
                'createdBy' => Auth::id(),
                'templateName' => $request->input('templateName'),
                'templateTypeId' => $request->input('templateTypeId')
            ];

            $data = JSON_ENCODE(
                $otherData
            );

            $dataInput = DB::select(
                "CALL addQuestionnaireTemplate('" . $data . "')"
            );

            if (isset($dataInput[0]->questionnaireTemplateId)) {
                if (isset($request->questionnaireCustomField)) {
                    foreach ($request->questionnaireCustomField as $k => $input) {
                        // echo $k;
                        $insertArr = array(
                            'udid' => Str::uuid()->toString(),
                            "questionnaireId" => $dataInput[0]->questionnaireTemplateId,
                            "parameterKey" => $k,
                            "parameterValue" => $input,
                            "entityType" => "questionnaireTemplate",
                            "referenceId" => $dataInput[0]->questionnaireTemplateId,
                            "createdBy" => Auth::id()
                        );
                        QuestionnaireField::insertGetId($insertArr);
                    }
                }
            }
            // die;
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'questionnaireTemplate', 'tableId' => $dataInput[0]->questionnaireTemplateId,
                'value' => json_encode($otherData), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);

            $provider = [
                'udid' => Str::uuid()->toString(),
                'createdBy' => Auth::id(),
                'providerId' => $providerId,
                'questionnaireTemplateId' => $dataInput[0]->questionnaireTemplateId
            ];
            $prodiverData = JSON_ENCODE(
                $provider
            );
            $templateProvider = DB::select(
                "CALL addQuestionnaireTemplateProvider('" . $prodiverData . "')"
            );
            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'questionnaireTemplateProvider', 'tableId' => $templateProvider[0]->questionnaireTemplateProviderId,
                'value' => json_encode($provider), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);

            if ($request->tags) {
                $tagsData = $request->input('tags');
                foreach ($tagsData as $value) {
                    $tags = [
                        'udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'tag' => $value, 'entityType' => '252',
                        'referenceId' => $dataInput[0]->questionnaireTemplateId, 'providerId' => $providerId
                    ];
                    $tagData = DB::select(
                        "CALL addQuestionnaireTags('" . JSON_ENCODE(
                            $tags
                        ) . "')"
                    );
                    $tagId = $tagData[0]->tagId;
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'tag', 'tableId' => $tagId,
                        'value' => json_encode($tags), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                }
            }
            if ($request->duration) {
                $timer = ['udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'duration' => $request->duration, 'entityType' => '252', 'referenceId' => $dataInput[0]->questionnaireTemplateId];
                $timerData = DB::select(
                    "CALL addQuestionTimer('" . JSON_ENCODE(
                        $timer
                    ) . "')"
                );
                $changeLog = [
                    'udid' => Str::uuid()->toString(), 'table' => 'questionTimer', 'tableId' => $timerData[0]->questionTimerId,
                    'value' => json_encode($tags), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                ];
                ChangeLog::create($changeLog);
            }
            $data = QuestionnaireTemplate::where('questionnaireTemplateId', $dataInput[0]->questionnaireTemplateId)->first();
            $userdata = fractal()->item($data)->transformWith(new QuestionnaireTemplateTransformer(false))->toArray();
            $message = ['message' => trans('messages.createdSuccesfully')];
            $endData = array_merge($message, $userdata);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function listQuestion($request, $id)
    {
        try {
            if ($id) {
                // $currentQuestion =  Question::where("udid",$id)->first();
                // $checkUpdate = $this->checkUpdateStatus($request,$currentQuestion);
                // if(isset($checkUpdate->udid)){
                //     if(isset($request->sectionId) && $request->sectionId != "undefined"){
                //         $questionnaireSection = QuestionnaireSection::where("udid",$request->sectionId)->first();
                //     }else{
                //         $questionnaireSection = "";
                //     }

                //     $questionUpdate = QuestionChanges::where("questionId",$currentQuestion->questionId);
                //     //                     if($parentId){
                //     //                         $questionUpdate->where("parentId",$parentId);
                //     //                     }
                //     //                     if($childId){
                //     //                         $questionUpdate->where("childId",$childId);
                //     //                     }
                //     if(isset($questionnaireSection->questionnaireSectionId)){
                //         $questionUpdate->where("sectionId",$questionnaireSection->questionnaireSectionId);
                //     }
                //                 $questionUpdate->where("entityType","template");
                //     $questionUpdate = $questionUpdate->first();
                //     if($questionUpdate){
                //         $questionDataObj["questionUpdate"] = $questionUpdate;
                //         $questionDataObj["question"] = $currentQuestion;
                //         return !empty($questionDataObj)?fractal()->item($questionDataObj)->transformWith(new QuestionChangesTransformer)->toArray():"";
                //     }
                // }else{
                $data = Question::where('udid', $id);
                $data->where("entityType", "question");
                if ($request->questionType) {
                    $data->whereHas('questionnaireField', function ($q) use ($request) {
                        $q->where('parameterKey', 'LIKE', "questionType");
                        $q->where('parameterValue', '=', $request->questionType);
                    });
                }
                $data->orWhere("entityType", "questionOptions");
                $data->where("udid", $id);
                if ($request->questionType) {
                    $data->whereHas('questionnaireField', function ($q) use ($request) {
                        $q->where('parameterKey', 'LIKE', "questionType");
                        $q->where('parameterValue', '=', $request->questionType);
                    });
                }
                $data = $data->first();
                if ($data) {
                    if (isset($request->sectionId) && $request->sectionId != 'undefined') {
                        $findCurrent = QuestionnaireSection::where('udid', $request->sectionId)->first();
                        if (isset($findCurrent->udid)) {
                            $data["questionnaireSectionId"] = $findCurrent->questionnaireSectionId;
                            $data["editType"] = "template";
                        }
                    }

                    return fractal()->item($data)->transformWith(new QuestionTransformer())->toArray();
                } else {
                    return ["data" => []];
                }
            } else {

                $data = Question::with("questionsType");
                $data->where("entityType", "question");
                $data->where('question', 'LIKE', "%" . $request->search . "%");
                if ($request->scoreTypeId || $request->questionType) {
                    $data->whereHas('questionnaireField', function ($q) use ($request) {
                        if ($request->scoreTypeId) {
                            $q->where('parameterKey', 'LIKE', "scoreTypeId");
                            $q->where('parameterValue', '=', $request->scoreTypeId);
                        }

                        if ($request->questionType) {
                            $q->where('parameterKey', 'LIKE', "questionType");
                            $q->where('parameterValue', '=', $request->questionType);
                        }
                    });
                }

                if ($request->search) {
                    $data->orWherehas('tags', function ($q) use ($request) {
                        $q->where("tag", 'LIKE', "%" . $request->search . "%");
                    });
                }

                $data->orderBy('createdAt', 'DESC');
                if ($request->limit == "all") {
                    $data = $data->get();
                    return fractal()->collection($data)->transformWith(new QuestionTransformer())->toArray();
                } else {
                    $data = $data->paginate(env('PER_PAGE', 20));
                    return fractal()->collection($data)->transformWith(new QuestionTransformer())->paginateWith(new IlluminatePaginatorAdapter($data))->toArray();
                }
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function checkUpdateStatus($request, $question)
    {
        try {
            $checkUpdate = 0;
            if (isset($request->sectionId) && $request->sectionId != "undefined") {
                $questionnaireSection = QuestionnaireSection::where("udid", $request->sectionId)->first();
            } else {
                $questionnaireSection = "";
            }

            if (isset($question->questionId)) {
                $questionUpdate = QuestionChanges::where("questionId", $question->questionId);
                if (isset($questionnaireSection->questionnaireSectionId)) {
                    $questionUpdate->where("sectionId", $questionnaireSection->questionnaireSectionId);
                    $questionUpdate->where("entityType", "template");
                } else {
                    $questionUpdate->where("entityType", "question");
                }
                $questionUpdate = $questionUpdate->first();
                return $questionUpdate;
            } else {
                return response()->json(['message' => "Invalid Question Id."], 400);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function checkAssignStatus($request, $questoinId)
    {
        try {
            $checkAssign = 0;
            $currentQuestion = Question::where("udid", $questoinId)->first();
            if (isset($request->optionId) && !empty($request->optionId)) {
                $questionOption = QuestionOption::where("udid", $request->optionId)->first();
                // Check question is used another option in same section.
                // If yes we need to create new question with updated value data.
                $getExistingQuestion = AssignOptionQuestion::where("questionId", $currentQuestion->questionId)
                    ->where("referenceId", "!=", $questionOption->questionOptionId)
                    ->where("entityType", "questionOption")
                    ->first();
                $childId = $questionOption->questionOptionId;
            } else {
                $childId = 0;
            }

            if (isset($request->sectionId) && !empty($request->sectionId)) {
                $QuestionnaireSection = QuestionnaireSection::where("udid", $request->sectionId)->first();
                // In section table we need to check question is used another section when updating question.
                $getQuestionFromSection = QuestionSection::where("questionId", $currentQuestion->questionId)
                    ->where("questionnaireSectionId", "!=", $QuestionnaireSection->questionnaireSectionId)
                    ->first();
                $sectionId = $QuestionnaireSection->questionnaireSectionId;
            } else {
                $sectionId = 0;
            }

            if (isset($getExistingQuestion->udid)) {
                $checkAssign = 1;
            }

            if (isset($getQuestionFromSection->udid)) {
                $checkAssign = 1;
            }
            return $checkAssign;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function createQuestion($request, $id)
    {
        try {
            $post = $request->all();
            // print_r($post);
            // die;
            if ($request->question) {
                $question = $request->question;
            } else {
                $question = "";
            }

            if ($request->dataTypeId) {
                $dataTypeId = $request->dataTypeId;
            } else {
                $dataTypeId = "";
            }

            if (isset($post["sectionId"])) {
                $sectionId = $request->sectionId;
                $section = QuestionnaireSection::where('udid', $sectionId)->first();
                if (isset($section->questionnaireSectionId)) {
                    $sectionId = $section->questionnaireSectionId;
                } else {
                    $sectionId = 0;
                }
            } else {
                $sectionId = 0;
            }

            if (isset($post["referenceId"])) {
                $referenceId = $request->referenceId;
                $questionOption = QuestionOption::where("udid", $referenceId)->first();
                if (isset($questionOption->questionOptionId)) {
                    $referenceId = $questionOption->questionOptionId;
                } else {
                    return response()->json(['message' => "Invalid ReferenceId."], 400);
                }
            } else {
                $referenceId = "0";
            }

            if (isset($post["entityType"])) {
                $entityType = $request->entityType;
            } else {
                $entityType = "question";
            }

            if (isset($post["templateId"])) {
                $template = QuestionnaireTemplate::where('udid', $post["templateId"])->first();
                if (isset($template->questionnaireTemplateId)) {
                    $templateId = $template->questionnaireTemplateId;
                }
            } else {
                $templateId = 0;
            }


            if (isset($post["questionType"])) {
                $questionType = $request->questionType;
            } else {
                $questionType = "";
            }

            if (isset($post["parent"])) {
                $parent = $request->parent;
                $questionData = Question::where("udid", $parent)->first();
                if (isset($questionData->udid)) {
                    $parent = $questionData->questionId;
                } else {
                    return response()->json(['message' => "Invalid Parent Id."], 400);
                }
            } else {
                $parent = "0";
            }

            if (isset($post["parentId"])) {
                $parentId = $request->parentId;
                $questionData = Question::where("udid", $parentId)->first();
                if (isset($questionData->udid)) {
                    $parentId = $questionData->questionId;
                } else {
                    return response()->json(['message' => "Invalid Parent Id."], 400);
                }
            } else {
                $parentId = "0";
            }

            $otherData = [
                'udid' => Str::uuid()->toString(),
                'createdBy' => Auth::id(),
                'question' => $question,
                'questionType' => $questionType,
                'dataTypeId' => $dataTypeId
            ];

            $data = JSON_ENCODE(
                $otherData
            );

            $questionId = Question::insertGetId($otherData);

            if (isset($questionId)) {
                // set timer
                Question::where("questionId", $questionId)->update(
                    [
                        "referenceId" => $referenceId,
                        "entityType" => $entityType,
                        "parent" => $parent
                    ]
                );

                // auto assign question under option if referenceId for nested else normal question.
                if ($referenceId) {
                    $this->insertAssignOptionQuestion($templateId, $questionId, $referenceId, $sectionId, $parentId);
                }

                if (isset($post["questionnaireCustomField"])) {
                    foreach ($request->questionnaireCustomField as $k => $input) {
                        $insertArr = array(
                            'udid' => Str::uuid()->toString(),
                            "parameterKey" => $k,
                            "parameterValue" => $input,
                            "entityType" => "questions",
                            "referenceId" => $questionId,
                            "createdBy" => Auth::id()
                        );

                        QuestionnaireField::insertGetId($insertArr);
                    }
                }

                // add set timer
                if (isset($post["duration"]) && !empty($post["duration"])) {
                    $timer = ['udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'duration' => $post["duration"], 'entity' => '253', 'referenceId' => $questionId];
                    DB::select(
                        "CALL addQuestionTimer('" . JSON_ENCODE(
                            $timer
                        ) . "')"
                    );
                }
                // tags
                if (isset($post["tags"]) && !empty($post["tags"])) {
                    $tagsData = $post["tags"];
                    $providerId = Helper::providerId();
                    foreach ($tagsData as $value) {
                        $tags = [
                            'udid' => Str::uuid()->toString(),
                            'createdBy' => Auth::id(),
                            'tag' => $value,
                            'entityType' => '253',
                            'providerId' => $providerId,
                            'referenceId' => $questionId
                        ];
                        DB::select(
                            "CALL addQuestionnaireTags('" . JSON_ENCODE(
                                $tags
                            ) . "')"
                        );
                    }
                }


                $data = Question::where('questionId', $questionId)->first();
                $question = fractal()->item($data)->transformWith(new QuestionTransformer())->toArray();
                $message = ['message' => trans('messages.createdSuccesfully')];
                $endData = array_merge($message, $question);
                return $endData;
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function insertAssignOptionQuestion($templateId, $questionId, $questionOptionId, $sectionId, $parentId, $addQuestion = 0)
    {
        try {
            $programId = Helper::programId();
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $objArr = array();
            if (!empty($questionId) && !empty($questionOptionId)) {
                $this->deleteAssignOptionQuestion($questionId, $questionOptionId, $programId, $provider, $providerLocation);
                $assignQuestion = [
                    'udid' => Str::uuid()->toString(),
                    'questionId' => $questionId,
                    'sectionId' => $sectionId,
                    'parentId' => $parentId,
                    'referenceId' => $questionOptionId,
                    'entityType' => "questionOption",
                    "createdBy" => Auth::id(),
                    'providerId' => $provider,
                    'programId' => $programId,
                    'providerLocationId' => $providerLocation
                ];
                // print_r($assignQuestion);
                // die;
                AssignOptionQuestion::create($assignQuestion);
                $objArr["sectionId"] = $sectionId;
                $objArr["parentId"] = $parentId;
                $objArr["childId"] = $questionOptionId;

                if ($templateId) {
                    $objArr["templateId"] = $templateId;
                }

                $objArr["editType"] = "question";
                $objArr["entityType"] = "template";
                $objArr["addQuestion"] = $addQuestion;
                QuestionChanges::cloneQuestoinInOptionFromQuestionBank($objArr, $questionId);
                // die;
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function deleteAssignOptionQuestion($questionId, $questionOptionId, $programId, $provider, $providerLocation)
    {
        try {
            $question = [
                'isActive' => 0,
                'isDelete' => 1,
                'deletedBy' => Auth::id(),
                'deletedAt' => Carbon::now()
            ];
            AssignOptionQuestion::where("questionId", $questionId)
                ->where("referenceId", $questionOptionId)
                ->where("entityType", "questionOption")
                ->where("programId", $programId)
                ->where("providerId", $provider)
                ->where("providerLocationId", $providerLocation)
                ->update($question);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function assignOptionQuestion($request)
    {
        try {
            $post = $request->all();
            // print_r($post);
            // die;
            $sectionId = "";
            $templateId = "";
            $parentId = "";
            foreach ($post["data"] as $value) {
                $questionOption = QuestionOption::where('udid', $value["optionId"])->first();
                $question = Question::where('udid', $value["questionId"])->first();
                if (isset($question->questionId) && isset($questionOption->questionOptionId)) {
                    $questionId = $question->questionId;
                    $parentId = "";
                    if (isset($post["sectionId"])) {
                        $section = QuestionnaireSection::where('udid', $post["sectionId"])->first();
                        if (isset($section->questionnaireSectionId)) {
                            $sectionId = $section->questionnaireSectionId;
                        } else {
                            $sectionId = 0;
                        }
                    } else {
                        $sectionId = 0;
                    }


                    if (isset($post["templateId"])) {
                        $template = QuestionnaireTemplate::where('udid', $post["templateId"])->first();
                        if (isset($template->questionnaireTemplateId)) {
                            $templateId = $template->questionnaireTemplateId;
                        }
                    } else {
                        $templateId = 0;
                    }

                    if (isset($value["parentId"]) && !empty($value["parentId"])) {
                        $parentId = $value["parentId"];
                        $questionData = Question::where("udid", $parentId)->first();
                        if (isset($questionData->udid)) {
                            $parentId = $questionData->questionId;
                        } else {
                            $parentId = 0;
                        }
                    } else {
                        $parentId = 0;
                    }
                    $addQuestion = 1;
                    $this->insertAssignOptionQuestion($templateId, $questionId, $questionOption->questionOptionId, $sectionId, $parentId, $addQuestion);
                }
            }
            $message = ['message' => trans('messages.createdSuccesfully')];
            return $message;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function assignOptionQuestionOld($request)
    {
        try {
            $programId = Helper::programId();
            $provider = Helper::providerId();
            $providerLocation = Helper::providerLocationId();
            $post = $request->all();
            $questionStr = "";
            foreach ($post as $value) {
                $questionOption = QuestionOption::where('udid', $value["optionId"])->first();
                $question = Question::where('udid', $value["questionId"])->first();
                if (isset($question->questionId) && isset($questionOption->questionOptionId)) {
                    $questionId = $question->questionId;
                    if (isset($value["question"]) && !empty($value["question"])) {
                        $questionStr = $value["question"];
                    } else {
                        $questionStr = $question->question;
                    }

                    $question = [
                        'udid' => Str::uuid()->toString(),
                        'parent' => $questionOption->questionId,
                        'question' => $questionStr,
                        'referenceId' => $questionOption->questionOptionId,
                        'entityType' => "questionOptions",
                        'questionType' => $question->questionType,
                        'dataTypeId' => $question->dataTypeId,
                        "createdBy" => Auth::id()
                    ];

                    $assignQuestion = [
                        'udid' => Str::uuid()->toString(),
                        'questionId' => $questionId,
                        'referenceId' => $questionOption->questionOptionId,
                        'entityType' => "questionOption",
                        "createdBy" => Auth::id(),
                        'providerId' => $provider,
                        'programId' => $programId,
                        'providerLocationId' => $providerLocation
                    ];


                    Question::create($question);
                } elseif (isset($value["question"]) && !empty($value["question"])) {
                    $questionStr = $value["question"];
                    if (isset($value["dataTypeId"]) && !empty($value["dataTypeId"])) {
                        $dataTypeId = $value["dataTypeId"];
                    } else {
                        $dataTypeId = "";
                    }

                    if (isset($value["questionType"]) && !empty($value["questionType"])) {
                        $questionType = $value["questionType"];
                    } else {
                        $questionType = "";
                    }

                    $question = [
                        'udid' => Str::uuid()->toString(),
                        'parent' => $questionOption->questionId,
                        'question' => $questionStr,
                        'referenceId' => $questionOption->questionOptionId,
                        'entityType' => "questionOptions",
                        'questionType' => $questionType,
                        'dataTypeId' => $dataTypeId,
                        "createdBy" => Auth::id()
                    ];

                    $lastId = Question::insertGetId($question);

                    $assignQuestion = [
                        'udid' => Str::uuid()->toString(),
                        'questionId' => $lastId,
                        'referenceId' => $questionOption->questionOptionId,
                        'entityType' => "questionOption",
                        "createdBy" => Auth::id(),
                        'providerId' => $provider,
                        'programId' => $programId,
                        'providerLocationId' => $providerLocation
                    ];

                }

                AssignOptionQuestion::create($assignQuestion);

                $message = ['message' => trans('messages.createdSuccesfully')];
            }
            return $message;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function createQuestionOptionOld($request, $id)
    {
        try {
            $post = $request->all();

            if ($request->dataTypeId) {
                $dataTypeId = $request->dataTypeId;
            } else {
                $dataTypeId = "";
            }

            if ($id) {
                $question = Question::where('udid', $id)->first();
                $questionId = $question->questionId;
                $global = GlobalCode::where('id', $dataTypeId)->first();
                // $dataTypeId = "label";
                // die;
                if (isset($global->name) && $global->name == "Label" || isset($global->name) && $global->name == "Single Choice" || isset($global->name) && $global->name == "Multiple Choice") {
                    if ($request->option) {
                        $option = $request->option;
                    } else {
                        return response()->json(['message' => "option required."], 400);
                    }
                    if ($option > 0) {
                        $this->questionOptionInsertOld($request, $option, $questionId);
                    }
                }
                if (isset($global->name) && $global->name == "Textbox" || isset($global->name) && $global->name == "Number") {
                    if (isset($post["score"]) && !empty($post["score"])) {
                        $scoreData = [
                            'udid' => Str::uuid()->toString(),
                            'score' => $post["score"],
                            'referenceId' => $questionId,
                            'entityType' => "253",
                            'questionId' => $questionId
                        ];
                        QuestionScore::insert($scoreData);
                    }

                    if (isset($post["questionnaireCustomField"])) {
                        foreach ($request->questionnaireCustomField as $k => $input) {
                            $insertArr = array(
                                'udid' => Str::uuid()->toString(),
                                "parameterKey" => $k,
                                "parameterValue" => $input,
                                "entityType" => "questions",
                                "referenceId" => $questionId,
                                "createdBy" => Auth::id()
                            );
                            QuestionnaireField::insertGetId($insertArr);
                        }
                    }
                }


                $data = Question::where('questionId', $questionId)->first();
                $question = fractal()->item($data)->transformWith(new QuestionTransformer())->toArray();
                $message = ['message' => trans('messages.createdSuccesfully')];
                $endData = array_merge($message, $question);
                return $endData;
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function updateQuestion($request, $id)
    {

        try {
            $post = $request->all();
            $data = array();
            if ($request->dataTypeId) {
                $dataTypeId = $request->dataTypeId;
            } else {
                $dataTypeId = "";
            }

            $question = [
                'updatedBy' => Auth::id(),
            ];

            if ($request->dataTypeId) {
                $question["dataTypeId"] = $request->dataTypeId;
            }

            $templateId = 0;
            if ($request->templateId) {
                $template = QuestionnaireTemplate::where("udid", $request->templateId)->first();
                if (isset($template->questionnaireTemplateId)) {
                    $templateId = $template->questionnaireTemplateId;
                }
            }

            $parentId = 0;
            if ($request->parentId) {
                $parentQ = Question::where("udid", $request->parentId)->first();
                if (isset($parentQ->questionId)) {
                    $parentId = $parentQ->questionId;
                }
            }

            if ($request->editType) {
                $editType = $request->editType;
            } else {
                $editType = 0;
            }

            if ($request->question) {
                $question["question"] = $request->question;
            }

            if ($request->questionType) {
                $question["questionType"] = $request->questionType;
            }

            // $dataInput = DB::select(
            //     "CALL UpdateQuestions('" . $question . "')"
            // );
            $checkAssign = 0;
            $currentQuestion = Question::where("udid", $id)->first();
            if (isset($request->optionId) && !empty($request->optionId)) {
                $questionOption = QuestionOption::where("udid", $request->optionId)->first();
                // print_r($questionOption);
                // die;
                // Check question is used another option in same section.
                // If yes we need to create new question with updated value data.
                $getExistingQuestion = AssignOptionQuestion::where("questionId", $currentQuestion->questionId)
                    ->where("referenceId", $questionOption->questionOptionId)
                    ->where("entityType", "questionOption")
                    ->where("isActive", 1)
                    ->first();
                $childId = $questionOption->questionOptionId;
            } else {
                $childId = 0;
            }


            if (isset($request->sectionId) && !empty($request->sectionId)) {
                $QuestionnaireSection = QuestionnaireSection::where("udid", $request->sectionId)->first();
                // In section table we need to check question is used another section when updating question
                $sectionId = $QuestionnaireSection->questionnaireSectionId;
            } else {
                $sectionId = 0;
            }


            if (isset($getExistingQuestion->sectionId) && !empty($getExistingQuestion->sectionId)) {
                $checkAssign = 1;
            }

            // this is for question bank check.
            $getQuestionFromSection = QuestionSection::where("questionId", $currentQuestion->questionId)->first();
            if (isset($getQuestionFromSection->udid)) {
                $checkAssign = 1;
            }

            if ($checkAssign) {
                $data["programId"] = Helper::programId();
                $data["provider"] = Helper::providerId();
                $data["providerLocation"] = Helper::providerLocationId();
                $data["questionId"] = $currentQuestion->questionId;
                $data["entityType"] = $editType;

                $questionUpdate = QuestionChanges::where("questionId", $currentQuestion->questionId);
                if ($parentId) {
                    $questionUpdate->where("parentId", $parentId);
                }
                if ($childId) {
                    $questionUpdate->where("childId", $childId);
                }
                if ($sectionId) {
                    $questionUpdate->where("sectionId", $sectionId);
                }
                $questionUpdate->where("entityType", $editType);
                $questionUpdate = $questionUpdate->first();
                if (isset($questionUpdate->questionId)) {
                    // Update question changes
                    QuestionChanges::updateQuestionChanges($questionUpdate->udid, $post);

                } else {
                    // Insert question changes
                    QuestionChanges::insertQuestionChanges($data, $templateId, $sectionId, $parentId, $childId, $post);
                }

            } else {
                Question::where("udid", $id)->update($question);
            }

            $q = Question::where("udid", $id)->first();
            $questionId = $q->questionId;
            if ($questionId) {
                // set timer
                if (isset($post["tags"])) {

                    $tag = [
                        'isActive' => 0,
                        'isDelete' => 1,
                        'deletedBy' => Auth::id(),
                        'deletedAt' => Carbon::now()
                    ];

                    Tags::updateTag($tag, $questionId, "253");

                    // $dataInput = DB::select(
                    //     "CALL deleteQuestionnaireTags('" . $questionId . "','" . '253' . "')"
                    // );

                    $tagsData = $request->input('tags');

                    foreach ($tagsData as $value) {

                        $tags = ['udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'tag' => $value, 'entityType' => '253', 'referenceId' => $questionId];
                        DB::select(
                            "CALL addQuestionnaireTags('" . JSON_ENCODE(
                                $tags
                            ) . "')"
                        );
                    }
                }
                if (isset($post["duration"])) {
                    $duration = [
                        'duration' => $request->input('duration'),
                        'updatedBy' => Auth::id(),
                        'referenceId' => $questionId,
                        'entity' => '253'
                    ];
                    DB::select(
                        "CALL updateQuestionTimer('" . JSON_ENCODE(
                            $duration
                        ) . "')"
                    );
                }

                if ($dataTypeId) {
                    $global = GlobalCode::where('id', $dataTypeId)->first();
                }

                if (isset($post["questionnaireCustomField"])) {
                    foreach ($request->questionnaireCustomField as $k => $input) {
                        $questionnaireField = QuestionnaireField::where("parameterKey", $k)
                            ->where("entityType", "questions")
                            ->where("referenceId", $questionId)
                            ->first();

                        if (isset($questionnaireField->udid)) {
                            $insertArr = array(
                                "parameterValue" => $input,
                            );

                            QuestionnaireField::where("udid", $questionnaireField->udid)->update($insertArr);
                        } else {
                            $insertArr = array(
                                'udid' => Str::uuid()->toString(),
                                "parameterKey" => $k,
                                "parameterValue" => $input,
                                "entityType" => "questions",
                                "referenceId" => $questionId,
                                "createdBy" => Auth::id()
                            );

                            QuestionnaireField::insertGetId($insertArr);
                        }
                    }
                }

                $message = ['message' => trans('messages.updatedSuccesfully')];
                // $endData = array_merge($message, $userdata);
                return $message;
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function questionOptionInsertOld($request, $option, $questionId)
    {
        try {
            foreach ($option as $val) {
                $otherData = [
                    'udid' => Str::uuid()->toString(),
                    'createdBy' => Auth::id(),
                    'options' => $val["labelName"],
                    'questionId' => $questionId
                ];

                if (isset($val["defaultOption"]) && !empty($val["defaultOption"])) {
                    $otherData["defaultOption"] = $val["defaultOption"];
                }

                if (isset($val["answer"]) && !empty($val["answer"])) {
                    $otherData["answer"] = $val["answer"];
                }

                $optionId = QuestionOption::insertGetId($otherData);

                // $data = JSON_ENCODE(
                //     $otherData
                // );

                // $dataInputQuestionOption = DB::select(
                //     "CALL addQuestionOption('" . $data . "')"
                // );
                if (isset($val["labelScore"]) && !empty($val["labelScore"])) {
                    $scoreData = [
                        'udid' => Str::uuid()->toString(),
                        'score' => $val["labelScore"],
                        'referenceId' => $optionId,
                        'entityType' => "254",
                        'questionId' => $questionId
                    ];

                    QuestionScore::insert($scoreData);
                }

                if (isset($optionId) && !empty($optionId)) {

                    if (isset($post["questionnaireCustomField"])) {
                        foreach ($request->questionnaireCustomField as $k => $input) {
                            $insertArr = array(
                                'udid' => Str::uuid()->toString(),
                                "parameterKey" => $k,
                                "parameterValue" => $input,
                                "entityType" => "questionOption",
                                "referenceId" => $optionId,
                                "createdBy" => Auth::id()
                            );
                            QuestionnaireField::insertGetId($insertArr);
                        }
                    }

                    if (isset($val["program"]) && count($val["program"])) {
                        foreach ($val["program"] as $programOption) {
                            if (isset($programOption["programScore"]) && !empty($programOption["programScore"]) && isset($programOption["programId"]) && !empty($programOption["programId"])) {

                                $otherProgram = [
                                    'udid' => Str::uuid()->toString(),
                                    'createdBy' => Auth::id(),
                                    'questionOptionId' => $optionId,
                                    'programId' => $programOption["programId"],
                                    'questionId' => $questionId
                                ];

                                // $dataProgramScoring = JSON_ENCODE(
                                //     $otherDataProgram
                                // );

                                $programOptionId = QuestionOptionProgram::insertGetId($otherProgram);

                                if ($programOptionId) {
                                    $scoreData = [
                                        'udid' => Str::uuid()->toString(),
                                        'score' => $programOption["programScore"],
                                        'referenceId' => $programOptionId,
                                        'entityType' => "255",
                                        'questionId' => $questionId
                                    ];

                                    QuestionScore::insert($scoreData);
                                }

                                // $dataInput2 = DB::select( QuestionOptionProgram
                                //     "CALL addQuestionProgramScore('" . $otherDataProgram . "')"
                                // );

                            } elseif (isset($programOption["programId"]) && !empty($programOption["programId"]) && empty($programOption["score"])) {

                                $otherProgram = [
                                    'udid' => Str::uuid()->toString(),
                                    'createdBy' => Auth::id(),
                                    'questionOptionId' => $optionId,
                                    'programId' => $programOption["programId"],
                                    'questionId' => $questionId
                                ];

                                QuestionOptionProgram::insertGetId($otherProgram);

                                // $dataProgramScoring = JSON_ENCODE(
                                //     $otherDataProgramScoring
                                // );

                                // $dataInput2 = DB::select(
                                //     "CALL addQuestionProgramScore('" . $dataProgramScoring . "')"
                                // );
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function createQuestionOption($request, $id)
    {
        try {
            $post = $request->all();

            if ($id) {
                $questionId = $id;
                $q = Question::where("udid", $questionId)->first();
                if (empty($q)) {
                    return response()->json(['message' => "Question Id Invalid."], 404);
                } else {
                    $questionId = $q->questionId;
                }
            } else {
                return response()->json(['message' => "Question Id Invalid."], 404);
            }

            if ($request->dataTypeId) {
                $dataTypeId = $request->dataTypeId;
            } else {
                $dataTypeId = "";
            }

            $global = GlobalCode::where('id', $dataTypeId)->first();
            // $dataTypeId = "label";
            if ((@$global->name && $global->name == "Label") || (@$global->name && $global->name == "Single Choice") || (@$global->name && $global->name == "Multiple Choice")) {
                if ($request->option) {
                    $option = $request->option;
                } else {
                    return response()->json(['message' => "option required."], 400);
                }
                if ($option > 0) {
                    // QuestionOption::where("questionId",$questionId)->update(["defaultOption"=> "0","answer"=> "0"]);
                    foreach ($option as $val) {
                        $optionArr = [
                            'updatedBy' => Auth::id(),
                            'updatedAt' => Carbon::now(),
                        ];

                        if ($val["labelName"]) {
                            $optionArr["options"] = $val["labelName"];
                        }

                        if (isset($val["defaultOption"])) {
                            $optionArr["defaultOption"] = $val["defaultOption"];
                        }

                        if (isset($val["answer"])) {
                            $optionArr["answer"] = $val["answer"];
                        }

                        if (isset($val["id"]) && !empty($val["id"])) {
                            // TOD
                            die("use put method for it.");
                            // update option if eisting option id
                            // QuestionOption::where("udid",$val["id"])->update($optionArr);
                            // $option = QuestionOption::where("udid",$val["id"])->first();
                            // if (isset($option->questionOptionId) > 0) {
                            //     $this->questionOptionUpdate($request,$val,$option->questionOptionId, $questionId);
                            // }
                        } else {
                            // create new option
                            $this->questionOptionInsert($request, $val, $option, $questionId);
                        }

                    }
                }
            }

            if ((@$global->name && $global->name == "Textbox") || (@$global->name && $global->name == "Number")) {
                if (isset($post["score"]) && !empty($post["score"])) {
                    $scoreData = [
                        'udid' => Str::uuid()->toString(),
                        'score' => $post["score"],
                        'referenceId' => $questionId,
                        'entityType' => "253",
                        'questionId' => $questionId
                    ];
                    QuestionScore::insert($scoreData);
                }

                if (isset($post["questionnaireCustomField"])) {
                    foreach ($request->questionnaireCustomField as $k => $input) {
                        $insertArr = array(
                            'udid' => Str::uuid()->toString(),
                            "parameterKey" => $k,
                            "parameterValue" => $input,
                            "entityType" => "questions",
                            "referenceId" => $questionId,
                            "createdBy" => Auth::id()
                        );
                        QuestionnaireField::insertGetId($insertArr);
                    }
                }
            }

            $message = ['message' => trans('messages.updatedSuccesfully')];
            // $endData = array_merge($message, $userdata);
            return $message;

        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }

    }

    public function updateQuestionOption($request, $id)
    {
        try {
            $post = $request->all();
            if ($id) {
                $questionId = $id;
                $q = Question::where("udid", $questionId)->first();
                if (empty($q)) {
                    return response()->json(['message' => "Question Id Invalid."], 404);
                } else {
                    $questionId = $q->questionId;
                }

                if (isset($post["option"])) {
                    $newOptionArray = array();
                    $i = 0;
                    // insert new option if new object existing in post array using check id in object array
                    // if id not exist in post($post["option"]) array, it means it is a new option.
                    $isNew = false;
                    $returnNewOptionId = 0;
                    foreach ($post["option"] as $optionObj) {
                        if (isset($optionObj["id"])) {
                            $newOptionArray[$i] = $optionObj;
                        } else {
                            $isNew = true;
                            $returnNewOptionId = $this->questionOptionInsert($request, $optionObj, $post["option"], $questionId);
                            if ($returnNewOptionId > 0) {
                                $questionOptionObj = QuestionOption::where("questionOptionId", $returnNewOptionId)->first();
                                if (isset($questionOptionObj->udid)) {
                                    $newOptionArray[$i] = $optionObj;
                                    $newOptionArray[$i]["id"] = $questionOptionObj->udid;
                                }
                            }
                        }
                        $i++;
                    }
                    if ($isNew == true && count($newOptionArray) > 0) {
                        $post["option"] = $newOptionArray;
                    }
                }
            } else {
                return response()->json(['message' => "Question Id Invalid."], 404);
            }

            if ($request->dataTypeId) {
                $dataTypeId = $request->dataTypeId;
            } else {
                $dataTypeId = "";
            }

            $global = GlobalCode::where('id', $dataTypeId)->first();
            if ($id) {
                $programId = Helper::programId();
                $provider = Helper::providerId();
                $providerLocation = Helper::providerLocationId();
                $insertObjArr = array();
                $insertObjArr = array(
                    'udid' => Str::uuid()->toString(),
                    "createdBy" => Auth::id()
                );

                $insertObjArr["programId"] = $programId;
                $insertObjArr["providerId"] = $provider;
                $insertObjArr["providerLocationId"] = $providerLocation;
                $insertObjArr["questionId"] = $questionId;
                $insertObjArr["entityType"] = "templateOption";
                $insertObjArr["dataObj"] = json_encode($post);

                $find = QuestionChanges::where("questionId", $questionId);
                $find->where("entityType", "templateOption");
                if (isset($post["sectionId"]) && $post["sectionId"] != "undefined") {
                    $sectionFind = QuestionnaireSection::where('udid', $post["sectionId"])->first();
                    if (isset($sectionFind->questionnaireSectionId)) {
                        $insertObjArr["sectionId"] = $sectionFind->questionnaireSectionId;
                        $find->where("sectionId", $sectionFind->questionnaireSectionId);
                    }
                }

                if (isset($post["templateId"]) && $post["templateId"] != "undefined") {
                    $templateFind = QuestionnaireTemplate::where('udid', $post["templateId"])->first();
                    if (isset($templateFind->questionnaireTemplateId)) {
                        $insertObjArr["templateId"] = $templateFind->questionnaireTemplateId;
                        $find->where("templateId", $templateFind->questionnaireTemplateId);
                    }
                }

                if (isset($post["parentId"]) && $post["parentId"] != "undefined") {
                    $questionFind = Question::where('udid', $post["parentId"])->first();
                    if (isset($questionFind->questionId)) {
                        if ($questionFind->questionId == $questionId) {
                            $insertObjArr["parentId"] = 0;
                        } else {
                            $insertObjArr["parentId"] = $questionFind->questionId;
                            $find->where("parentId", $questionFind->questionId);
                        }
                    }
                } else {
                    $insertObjArr["parentId"] = 0;
                }

                if (isset($post["optionId"]) && $post["optionId"] != "undefined") {

                    $questionOption = QuestionOption::where("udid", $post["optionId"])->first();
                    if (isset($questionOption->udid)) {
                        $insertObjArr["childId"] = $questionOption->questionOptionId;
                    } else {
                        $insertObjArr["childId"] = 0;
                    }
                } else {
                    $insertObjArr["childId"] = 0;
                }

                $findChanges = $find->first();
                if (isset($findChanges->udid)) {
                    $updateObjArr["dataObj"] = json_encode($post);
                    QuestionChanges::where("udid", $findChanges->udid)->update($updateObjArr);
                } else {
                    QuestionChanges::insertGetId($insertObjArr);
                }
                $message = ['message' => trans('messages.updatedSuccesfully')];
                return $message;
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function questionOptionUpdate($request, $val, $optionId, $questionId)
    {
        try {
            $post = $request->all();
            if (isset($optionId) && !empty($optionId)) {

                if (isset($val["labelScore"]) && !empty($val["labelScore"])) {
                    // option score update
                    $questionS = QuestionScore::where("referenceId", $optionId)
                        ->where("entityType", "254")
                        ->first();
                    if (!empty($questionS)) {
                        QuestionScore::where("udid", $questionS->udid)->update(
                            [
                                "score" => $val["labelScore"],
                                'updatedBy' => Auth::id(),
                                'updatedAt' => Carbon::now(),
                            ]
                        );
                    } else {
                        $scoreData = [
                            'udid' => Str::uuid()->toString(),
                            'score' => $val["labelScore"],
                            'referenceId' => $optionId,
                            'entityType' => "254",
                            'questionId' => $questionId
                        ];
                        QuestionScore::insert($scoreData);
                    }
                }

                if (isset($post["questionnaireCustomField"])) {
                    foreach ($request->questionnaireCustomField as $k => $input) {
                        // echo $k;
                        $questionnaireField = QuestionnaireField::where("parameterKey", $k)
                            ->where("entityType", "questionOption")
                            ->where("referenceId", $optionId)
                            ->first();

                        if (isset($questionnaireField->udid)) {
                            $insertArr = array(
                                "parameterValue" => $input,
                            );

                            QuestionnaireField::where("udid", $questionnaireField->udid)->update($insertArr);
                        } else {
                            $insertArr = array(
                                'udid' => Str::uuid()->toString(),
                                "parameterKey" => $k,
                                "parameterValue" => $input,
                                "entityType" => "questionOption",
                                "referenceId" => $optionId,
                                "createdBy" => Auth::id()
                            );

                            QuestionnaireField::insertGetId($insertArr);
                        }
                    }
                }

                if (isset($val["program"]) && count($val["program"])) {
                    $optionObj = [
                        'isActive' => "0",
                        'isDelete' => "1",
                        'deletedBy' => Auth::id(),
                        'deletedAt' => Carbon::now()
                    ];

                    $programScore = QuestionOptionProgram::where("questionOptionId", $optionId)
                        ->where("questionId", $questionId)
                        ->get();

                    // delete program
                    QuestionOptionProgram::where("questionOptionId", $optionId)->update($optionObj);

                    foreach ($val["program"] as $programOption) {
                        if (isset($programOption["programScore"]) && !empty($programOption["programScore"]) && isset($programOption["programId"]) && !empty($programOption["programId"])) {
                            // delete program score
                            if (!empty($programScore)) {
                                foreach ($programScore as $s) {
                                    QuestionScore::where("referenceId", $s->questionOptionProgramId)
                                        ->where("entityType", "255")
                                        ->update($optionObj);
                                }
                            }

                            $otherProgram = [
                                'udid' => Str::uuid()->toString(),
                                'createdBy' => Auth::id(),
                                'questionOptionId' => $optionId,
                                'programId' => $programOption["programId"],
                                'questionId' => $questionId
                            ];

                            $programOptionId = QuestionOptionProgram::insertGetId($otherProgram);

                            if ($programOptionId) {
                                $scoreData = [
                                    'udid' => Str::uuid()->toString(),
                                    'score' => $programOption["programScore"],
                                    'referenceId' => $programOptionId,
                                    'entityType' => "255",
                                    'questionId' => $questionId
                                ];

                                QuestionScore::insert($scoreData);
                            }

                        } elseif (isset($programOption["programId"]) && !empty($programOption["programId"]) && empty($programOption["score"])) {

                            $otherProgram = [
                                'udid' => Str::uuid()->toString(),
                                'createdBy' => Auth::id(),
                                'questionOptionId' => $optionId,
                                'programId' => $programOption["programId"],
                                'questionId' => $questionId
                            ];

                            QuestionOptionProgram::insertGetId($otherProgram);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function questionOptionInsert($request, $val, $option, $questionId)
    {
        try {
            $otherData = [
                'udid' => Str::uuid()->toString(),
                'createdBy' => Auth::id(),
                'options' => $val["labelName"],
                'questionId' => $questionId
            ];

            if (isset($val["defaultOption"]) && !empty($val["defaultOption"])) {
                $otherData["defaultOption"] = $val["defaultOption"];
            }

            if (isset($val["answer"]) && !empty($val["answer"])) {
                $otherData["answer"] = $val["answer"];
            }

            $optionId = QuestionOption::insertGetId($otherData);

            if (isset($val["labelScore"]) && !empty($val["labelScore"])) {
                $scoreData = [
                    'udid' => Str::uuid()->toString(),
                    'score' => $val["labelScore"],
                    'referenceId' => $optionId,
                    'entityType' => "254",
                    'questionId' => $questionId
                ];

                QuestionScore::insert($scoreData);
            }

            if (isset($optionId) && !empty($optionId)) {

                if (isset($post["questionnaireCustomField"])) {
                    foreach ($request->questionnaireCustomField as $k => $input) {
                        $insertArr = array(
                            'udid' => Str::uuid()->toString(),
                            "parameterKey" => $k,
                            "parameterValue" => $input,
                            "entityType" => "questionOption",
                            "referenceId" => $optionId,
                            "createdBy" => Auth::id()
                        );
                        QuestionnaireField::insertGetId($insertArr);
                    }
                }

                if (isset($val["program"]) && count($val["program"])) {
                    foreach ($val["program"] as $programOption) {
                        if (isset($programOption["programScore"]) && !empty($programOption["programScore"]) && isset($programOption["programId"]) && !empty($programOption["programId"])) {

                            $otherProgram = [
                                'udid' => Str::uuid()->toString(),
                                'createdBy' => Auth::id(),
                                'questionOptionId' => $optionId,
                                'programId' => $programOption["programId"],
                                'questionId' => $questionId
                            ];

                            // $dataProgramScoring = JSON_ENCODE(
                            //     $otherDataProgram
                            // );

                            $programOptionId = QuestionOptionProgram::insertGetId($otherProgram);

                            if ($programOptionId) {
                                $scoreData = [
                                    'udid' => Str::uuid()->toString(),
                                    'score' => $programOption["programScore"],
                                    'referenceId' => $programOptionId,
                                    'entityType' => "255",
                                    'questionId' => $questionId
                                ];

                                QuestionScore::insert($scoreData);
                            }

                            // $dataInput2 = DB::select( QuestionOptionProgram
                            //     "CALL addQuestionProgramScore('" . $otherDataProgram . "')"
                            // );

                        } elseif (isset($programOption["programId"]) && !empty($programOption["programId"]) && empty($programOption["score"])) {

                            $otherProgram = [
                                'udid' => Str::uuid()->toString(),
                                'createdBy' => Auth::id(),
                                'questionOptionId' => $optionId,
                                'programId' => $programOption["programId"],
                                'questionId' => $questionId
                            ];

                            QuestionOptionProgram::insertGetId($otherProgram);

                            // $dataProgramScoring = JSON_ENCODE(
                            //     $otherDataProgramScoring
                            // );

                            // $dataInput2 = DB::select(
                            //     "CALL addQuestionProgramScore('" . $dataProgramScoring . "')"
                            // );
                        }
                    }
                }
            }
            return $optionId;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function deleteQuestionOption($request, $id)
    {
        try {
            if (!$id) {
                return response()->json(['message' => "option Id Invalid."], 404);
            } else {
                if ($request->questionId) {
                    $this->deleteOption($id, $request->questionId);
                } else {
                    return response()->json(['message' => "Question Id Invalid."], 404);
                }
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function deleteOption($id, $questionId)
    {
        try {
            // get option
            $questionOption = QuestionOption::where("udid", $id)->first();
            if (isset($questionOption->questionOptionId)) {
                $optionObj = [
                    'isActive' => "0",
                    'isDelete' => "1",
                    'deletedBy' => Auth::id(),
                    'deletedAt' => Carbon::now()
                ];

                // get Program score
                $programScore = QuestionOptionProgram::where("questionOptionId", $questionOption->questionOptionId)
                    ->where("questionId", $questionId)
                    ->get();

                //delete option
                QuestionOption::where("udid", $id)->update($optionObj);

                //delete option question
                Question::where("parent", $questionId)
                    ->where("entityType", "questionOptions")
                    ->where("referenceId", $questionOption->questionOptionId)
                    ->update($optionObj);

                //delete option score
                QuestionScore::where("referenceId", $questionOption->questionOptionId)
                    ->where("entityType", "254")
                    ->update($optionObj);

                // delete program
                QuestionOptionProgram::where("questionOptionId", $questionOption->questionOptionId)->update($optionObj);

                // delete program score
                if (!empty($programScore)) {
                    foreach ($programScore as $s) {
                        QuestionScore::where("referenceId", $s->questionOptionProgramId)
                            ->where("entityType", "255")
                            ->update($optionObj);
                    }
                }
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function questionnaireUpdate($request, $id)
    {
        try {
            $templateId = QuestionnaireTemplate::where('udid', $id)->first();
            if ($request->input('templateName')) {
                $templateName = $request->input('templateName');
            } else {
                $templateName = $templateId->templateName;
            }

            if ($request->input('templateTypeId')) {
                $templateTypeId = $request->input('templateTypeId');
            } else {
                $templateTypeId = $templateId->templateTypeId;
            }

            $template = [
                'templateName' => $templateName,
                'templateTypeId' => $templateTypeId,
                'updatedBy' => Auth::id(),
                'udid' => $id
            ];

            DB::select(
                "CALL updateQuestionnaireTemplate('" . JSON_ENCODE(
                    $template
                ) . "')"
            );


            if (isset($templateId->questionnaireTemplateId)) {
                if (isset($request->questionnaireCustomField)) {
                    foreach ($request->questionnaireCustomField as $k => $input) {
                        // echo $k;
                        $questionnaireField = QuestionnaireField::where("parameterKey", $k)
                            ->where("entityType", "questionnaireTemplate")
                            ->where("referenceId", $templateId->questionnaireTemplateId)
                            ->first();

                        if (isset($questionnaireField->udid)) {
                            $insertArr = array(
                                "parameterValue" => $input,
                            );

                            QuestionnaireField::where("udid", $questionnaireField->udid)->update($insertArr);
                        } else {
                            $insertArr = array(
                                'udid' => Str::uuid()->toString(),
                                "parameterKey" => $k,
                                "parameterValue" => $input,
                                "entityType" => "questionnaireTemplate",
                                "referenceId" => $templateId->questionnaireTemplateId,
                                "createdBy" => Auth::id()
                            );

                            QuestionnaireField::insertGetId($insertArr);
                        }
                    }
                }
            }

            $changeLog = [
                'udid' => Str::uuid()->toString(), 'table' => 'questionnaireTemplate', 'tableId' => $templateId->questionnaireTemplateId,
                'value' => json_encode($template), 'type' => 'updated', 'ip' => request()->ip(), 'updatedBy' => Auth::id()
            ];
            ChangeLog::create($changeLog);

            if ($request->tags) {
                $tag = [
                    'isActive' => 0,
                    'isDelete' => 1,
                    'deletedBy' => Auth::id(),
                    'deletedAt' => Carbon::now()
                ];

                Tags::updateTag($tag, $templateId->questionnaireTemplateId, "252");

                // DB::select(
                //     "CALL deleteQuestionnaireTags('" . JSON_ENCODE(
                //         $tag
                //     )  . "')"
                // );

                $tagsData = $request->input('tags');
                foreach ($tagsData as $value) {
                    $tags = ['udid' => Str::uuid()->toString(), 'createdBy' => Auth::id(), 'tag' => $value, 'entityType' => '252', 'referenceId' => $templateId->questionnaireTemplateId];
                    $tagData = DB::select(
                        "CALL addQuestionnaireTags('" . JSON_ENCODE(
                            $tags
                        ) . "')"
                    );

                    $tagId = $tagData[0]->tagId;
                    $changeLog = [
                        'udid' => Str::uuid()->toString(), 'table' => 'tag', 'tableId' => $tagId,
                        'value' => json_encode($tags), 'type' => 'created', 'ip' => request()->ip(), 'createdBy' => Auth::id()
                    ];
                    ChangeLog::create($changeLog);
                }
            }

            if ($request->duration) {
                $duration = [
                    'duration' => $request->input('duration'),
                    'updatedBy' => Auth::id(),
                    'referenceId' => $templateId->questionnaireTemplateId,
                    'entity' => '252'
                ];
                DB::select(
                    "CALL updateQuestionTimer('" . JSON_ENCODE(
                        $duration
                    ) . "')"
                );
            }
            // $dataInput = DB::select("CALL listQuestionnaireTemplate('" . $id . "','" . $request->search . "')");
            // $dataInput = Question::where('questionId', $id)->first();
            $message = ['message' => trans('messages.updatedSuccesfully')];
            // $data =  fractal()->item($dataInput)->transformWith(new QuestionnaireTemplateTransformer())->toArray();
            // $response = array_merge($message, $data);
            return $message;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function questionnaireList($request, $id)
    {
        try {
            if (!$id) {
                $data = QuestionnaireTemplate::with("assignedSection.questionnaireSection.questionSection", "questionnaireField");
                $data->Where('isActive', '1');

                if ($request->search) {
                    $data->Where('templateName', 'LIKE', "%" . $request->search . "%");
                    $data->orWherehas('tags', function ($q) use ($request) {
                        $q->where("tag", 'LIKE', "%" . $request->search . "%");
                    });
                }

                if ($request->orderField == 'templateName') {
                    $data->orderBy($request->orderField, $request->orderBy);
                } elseif ($request->orderField == 'templateType') {
                    $data->join('globalCodes', 'globalCodes.id', '=', 'questionnaireTemplates.templateTypeId')
                        ->orderBy('globalCodes.name', $request->orderBy);
                } else {
                    $data->orderBy('createdAt', 'DESC');
                }
                $data = $data->paginate(env('PER_PAGE', 20));


                if ($request->questionnaireFields) {
                    $data["questionnaireFields"] = $request->questionnaireFields;
                }

                return fractal()->collection($data)->transformWith(new QuestionnaireTemplateTransformer())->toArray();
            } else {
                $data = QuestionnaireTemplate::with("assignedSection", "questionnaireField")
                    ->where('questionnaireTemplates.udid', $id)
                    ->where('questionnaireTemplates.templateName', 'LIKE', "%" . $request->search . "%")->first();


                if ($request->questionnaireFields) {
                    $data["questionnaireFields"] = $request->questionnaireFields;
                }
                $data["id"] = $data->questionnaireTemplateId;
                return fractal()->item($data)->transformWith(new QuestionnaireTemplateTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function questionnaireDelete($request, $id)
    {
        try {
            $templateId = QuestionnaireTemplate::where('udid', $id)->first();
            if (isset($templateId->questionnaireTemplateId)) {
                $template = [
                    'isActive' => "0",
                    'isDelete' => "1",
                    'deletedBy' => Auth::id(),
                    'deletedAt' => Carbon::now()
                    // 'udid' => $id,
                ];

                QuestionnaireTemplate::where("udid", $id)->update($template);
                $timer = [
                    'isActive' => "0",
                    'isDelete' => "1",
                    'deletedBy' => Auth::id(),
                    'referenceId' => $templateId->questionnaireTemplateId,
                    'entityType' => '252',
                ];
                DB::select("CALL deleteQuestionnaireTags('" . JSON_ENCODE($timer) . "')");
                DB::select("CALL deleteQuestionTimer('" . JSON_ENCODE($timer) . "')");
                $provider = [
                    'isActive' => "0",
                    'isDelete' => "1",
                    'deletedBy' => Auth::id(),
                    'questionnaireTempleteId' => $templateId->questionnaireTemplateId,
                ];
                DB::select("CALL deleteQuestionnaireProvider('" . JSON_ENCODE($provider) . "')");
                DB::select("CALL deleteQuestionnaireProgram('" . JSON_ENCODE($provider) . "')");
                DB::select("CALL deleteQuestionnaireQuestion('" . JSON_ENCODE($provider) . "')");
                // QuestionnaireSection
                return response()->json(['message' => trans('messages.deletedSuccesfully')]);
            } else {
                return response()->json(['message' => "Template not found."], 400);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function questionDelete($request, $id)
    {
        try {
            $question = Question::where('udid', $id)->first();
            $template = [
                'isActive' => 0,
                'isDelete' => 1,
                'deletedBy' => Auth::id(),
                'udid' => $id,
                'questionId' => $question->questionId,
            ];
            DB::select("CALL deleteQuestion('" . JSON_ENCODE($template) . "')");
            DB::select("CALL deleteQuestionOption('" . JSON_ENCODE($template) . "')");
            return response()->json(['message' => trans('messages.deletedSuccesfully')]);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function assignQuestion($request, $id)
    {
        try {
            $template = QuestionnaireTemplate::where('udid', $id)->first();

            $data = $request->input('questionId');
            // $questionSectionData = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1];

            // QuestionnaireQuestion::where("questionnaireTempleteId", $template->questionnaireTemplateId)
            // ->where("entityType","question")->update($questionSectionData);

            foreach ($data as $value) {
                $questionId = Question::where('udid', $value)->first();
                $question = [
                    'udid' => Str::uuid()->toString(),
                    'createdBy' => Auth::id(),
                    'questionId' => $questionId->questionId,
                    'referenceId' => $questionId->questionId,
                    'entityType' => "question",
                    'questionnaireTempleteId' => $template->questionnaireTemplateId
                ];
                $input = QuestionnaireQuestion::create($question);
                $input = Question::whereHas('questionnaireQuestion', function ($query) use ($template) {
                    $query->where('questionnaireTempleteId', $template->questionnaireTemplateId);
                })->get();
                $userdata = fractal()->collection($input)->transformWith(new QuestionTransformer(true))->toArray();
                $message = ['message' => trans('messages.createdSuccesfully')];
            }
            $endData = array_merge($message, $userdata);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function updateAssignQuestion($request, $id)
    {
        try {
            $template = QuestionnaireTemplate::where('udid', $id)->first();

            $data = $request->input('questionId');
            $questionSectionData = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1];

            QuestionnaireQuestion::where("questionnaireTempleteId", $template->questionnaireTemplateId)
                ->where("entityType", "question")->update($questionSectionData);

            foreach ($data as $value) {
                $questionId = Question::where('udid', $value)->first();
                $question = [
                    'udid' => Str::uuid()->toString(),
                    'createdBy' => Auth::id(),
                    'questionId' => $questionId->questionId,
                    'referenceId' => $questionId->questionId,
                    'entityType' => "question",
                    'questionnaireTempleteId' => $template->questionnaireTemplateId
                ];
                $input = QuestionnaireQuestion::create($question);
                $input = Question::whereHas('questionnaireQuestion', function ($query) use ($template) {
                    $query->where('questionnaireTempleteId', $template->questionnaireTemplateId);
                })->get();
                $userdata = fractal()->collection($input)->transformWith(new QuestionTransformer(true))->toArray();
                $message = ['message' => trans('messages.createdSuccesfully')];
            }
            $endData = array_merge($message, $userdata);
            return $endData;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function assignQuestionList($request, $id)
    {
        try {
            $template = QuestionnaireTemplate::where('udid', $id)->first();
            if (isset($template->questionnaireTemplateId)) {
                $input = Question::whereHas('questionnaireQuestion', function ($query) use ($template) {
                    $query->where('questionnaireTempleteId', $template->questionnaireTemplateId);
                })->get();
                return fractal()->collection($input)->transformWith(new QuestionTransformer(true))->toArray();
            } else {
                return response()->json(['message' => "Template not found."], 404);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getQuestionnaireDataType($request, $id)
    {
        try {
            $data = GlobalCode::where('globalCodes.isActive', 1)->join('globalCodeCategories as g1', 'globalCodes.globalCodeCategoryId', '=', 'g1.id');
            $data->where('globalCodes.globalCodeCategoryId', $id);
            if (isset($request->globalCodeId) && $request->globalCodeId == "332") {
                $data->whereIn("globalCodes.id", ["243", "244"]);
            } elseif (isset($request->globalCodeId) && $request->globalCodeId == "333") {
                $data->whereIn("globalCodes.id", ["242", "243", "244"]);
            }
            $data = $data->select('globalCodes.*')->get();
            return fractal()->collection($data)->transformWith(new GlobalCodeTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getQuestionnaireScoreType($request, $id)
    {
        try {
            $data = GlobalCode::where('globalCodes.isActive', 1)->join('globalCodeCategories as g1', 'globalCodes.globalCodeCategoryId', '=', 'g1.id');
            $data->where('globalCodes.globalCodeCategoryId', $id);
            if (isset($request->globalCodeId) && $request->globalCodeId == "332") {
                $data->whereIn("globalCodes.id", ["332", "339"]);
            } elseif (isset($request->globalCodeId) && $request->globalCodeId == "333") {
                $data->whereIn("globalCodes.id", ["333", "339"]);
            } elseif (isset($request->globalCodeId) && $request->globalCodeId == "339") {
                $data->whereIn("globalCodes.id", ["339"]);
            }
            $data = $data->select('globalCodes.*')->get();
            return fractal()->collection($data)->transformWith(new GlobalCodeTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function checkQuestionnaire($request, $id)
    {
        try {
            if ($id) {
                $quesiton = QuestionnaireQuestion::where("quetionId", $id)->first();
                if ($quesiton) {
                    $result = ClientQuestionnaireTemplate::where("questionnaireTempleteId", $quesiton->questionnaireTempleteId)->first();
                    if ($result) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getTemplateQuestionnaire($request, $id)
    {
        try {
            if (!$id) {
                $data = QuestionnaireTemplate::with("assignedSection.questionnaireSection.questionSection", "questionnaireField")
                    ->where('templateName', 'LIKE', "%" . $request->search . "%");
                $data->where('isActive', '1');
                if ($request->orderField == 'templateName') {
                    $data->orderBy($request->orderField, $request->orderBy);
                } elseif ($request->orderField == 'templateType') {
                    $data->join('globalCodes', 'globalCodes.id', '=', 'questionnaireTemplates.templateTypeId')
                        ->orderBy('globalCodes.name', $request->orderBy);
                } else {
                    $data->orderBy('templateName', 'ASC');
                }
                $data = $data->paginate(env('PER_PAGE', 20));


                if ($request->questionnaireFields) {
                    $data["questionnaireFields"] = $request->questionnaireFields;
                }

                return fractal()->collection($data)->transformWith(new QuestionnaireTemplateTransformer())->toArray();
            } else {
                $data = QuestionnaireTemplate::with("assignedSection", "questionnaireField")
                    ->where('questionnaireTemplates.udid', $id)
                    ->where('questionnaireTemplates.templateName', 'LIKE', "%" . $request->search . "%")->first();


                if ($request->questionnaireFields) {
                    $data["questionnaireFields"] = $request->questionnaireFields;
                }
                return fractal()->item($data)->transformWith(new QuestionnaireTemplateTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getAssignedTemplateUserList($request, $id)
    {
        try {
            if (isset($request->search)) {
                $search = $request->search;
            } else {
                $search = "";
            }

            if ($id) {
                $template = QuestionnaireTemplate::where("udid", $id)->first();
                if (isset($template->udid)) {
                    $templateId = $template->questionnaireTemplateId;
                    $data = DB::select(
                        "CALL getAssignedTemplateUserList('" . $templateId . "','" . $search . "')",
                    );

                    return fractal()->collection($data)->transformWith(new AssignTemplateUserTransformer())->toArray();
                } else {
                    return response()->json(['message' => "template id invalid."], 400);
                }

            } else {
                return response()->json(['message' => "template id invalid."], 400);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getQuestionnaireCustomField($request, $id)
    {
        try {
            if (!$id) {
                $data = QuestionnaireField::where("parameterKey", $request->parameterKey);

                if ($request->parameterValue) {
                    $data->where("parameterValue", $request->parameterValue);
                }

                if ($request->questionnaireId) {
                    $template = QuestionnaireTemplate::where("udid", $request->questionnaireId)->first();
                    if (isset($template->questionnaireTemplateId)) {
                        $data->where("questionnaireId", $template->questionnaireTemplateId);
                    } else {
                        return response()->json(['message' => "template id invalid."], 400);
                    }
                }

                $data = $data->get();
                return fractal()->collection($data)->transformWith(new QuestionnaireFieldTransformer())->toArray();
            } else {
                $data = QuestionnaireField::where("udid", $id);
                $data = $data->get();
                return fractal()->collection($data)->transformWith(new QuestionnaireFieldTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function deleteNestedQuestion($request, $id)
    {
        try {
            if ($request->optionId && $request->questionId) {
                $option = QuestionOption::where("udid", $request->optionId)->first();
                if (isset($option->udid)) {
                    $question = Question::where('udid', $request->questionId)
                        ->where("referenceId", $option->questionOptionId)
                        ->where("entityType", "questionOptions")
                        ->first();

                    if (isset($question->udid)) {
                        $questionniare = [
                            'isActive' => "0",
                            'isDelete' => "1",
                            'deletedBy' => Auth::id(),
                            'deletedAt' => Carbon::now()
                        ];
                        Question::where('udid', $question->udid)->update($questionniare);

                        AssignOptionQuestion::where('questionId', $question->id)
                            ->where("referenceId", $option->questionOptionId)
                            ->where("entityType", "questionOption")
                            ->update($questionniare);

                        return response()->json(['message' => trans('messages.deletedSuccesfully')]);
                    } else {
                        return response()->json(['message' => "Question not found."], 400);
                    }
                } else {
                    return response()->json(['message' => "Option Id Invalid."], 400);
                }

            } else {
                return response()->json(['message' => "Question not found."], 400);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getQuestionnaireGlobalCode($request, $id)
    {
        try {
            $data = "";
            if ($id == "367") {
                // for Link Escalation
                $data = GlobalCode::where('globalCodes.isActive', 1)->join('globalCodeCategories as g1', 'globalCodes.globalCodeCategoryId', '=', 'g1.id');
                $data->where('globalCodes.globalCodeCategoryId', 81);
                $data = $data->select('globalCodes.*')->get();
            } elseif ($id == "368") {
                // for escalation type
                $data = GlobalCode::where('globalCodes.isActive', 1)->join('globalCodeCategories as g1', 'globalCodes.globalCodeCategoryId', '=', 'g1.id');
                $data->where('globalCodes.globalCodeCategoryId', 74);
                $data = $data->select('globalCodes.*')->get();
            } elseif ($id == "369") {
                // for escalation Action
                $data = GlobalCode::where('globalCodes.isActive', 1)->join('globalCodeCategories as g1', 'globalCodes.globalCodeCategoryId', '=', 'g1.id');
                $data->where('globalCodes.globalCodeCategoryId', 76);
                $data = $data->select('globalCodes.*')->get();
            }
            return fractal()->collection($data)->transformWith(new GlobalCodeTransformer())->toArray();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function getSectionBasedQuestion()
    {
        try {
            $query = DB::select("SELECT *  FROM `questionSections` as qs1
        INNER JOIN questions as q1 ON qs1.questionId = q1.questionId AND q1.isActive = 1 Where qs1.isActive = 1");
            foreach ($query as $row) {
                $queryQ = DB::select("SELECT *  FROM `questions` as q1 where q1.parent = $row->questionId");
                print_r($queryQ);
            }
            // print_r($queryQ);
            // print_r($queryQ);
            die;
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
