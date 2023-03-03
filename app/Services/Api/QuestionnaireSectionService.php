<?php

namespace App\Services\Api;

use Exception;
use App\Helper;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Log\ChangeLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Questionnaire\AssignOptionQuestion;
use App\Models\Questionnaire\Question;
use App\Models\Questionnaire\QuestionChanges;
use App\Models\Questionnaire\QuestionnaireQuestion;
use App\Models\Questionnaire\QuestionnaireTemplate;
use App\Models\QuestionnaireSection\QuestionSection;
use App\Models\QuestionnaireSection\QuestionnaireSection;
use App\Models\QuestionnaireSection\QuestionnaireQuestionSection;
use App\Transformers\Questionnaire\QuestionnaireTemplateTransformer;
use App\Transformers\QuestionnaireSection\QuestionnaireSectionTransformer;

class QuestionnaireSectionService
{
    public function questionnaireSectionAdd($request)
    {
        try {
            $post = $request->all();
            $insertObj = array(
                'udid' => Str::uuid()->toString(),
                "sectionName" => $post["sectionName"],
                'createdBy' => Auth::id()
            );

            if (isset($post["referenceId"])) {
                $insertObj["entity"] = "";
                $insertObj["referenceId"] = "";
            }

            $lastId = QuestionnaireSection::insertGetId($insertObj);
            if ($lastId) {
                $data = QuestionnaireSection::with("questionSection")->where('questionnaireSectionId', $lastId)->first();
                $questionnaireSection = fractal()->item($data)->transformWith(new QuestionnaireSectionTransformer())->toArray();
                $message = ['message' => trans('messages.createdSuccesfully')];
                $endData = array_merge($message, $questionnaireSection);
                return $endData;
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function questionnaireSectionUpdate($request, $id)
    {
        try {
            $post = $request->all();
            $updateObj = array(
                "sectionName" => $post["sectionName"]
            );

            $result = QuestionnaireSection::where("udid", $id)->update($updateObj);
            if ($result) {
                $data = QuestionnaireSection::with("questionSection")->where('udid', $id)->first();
                $questionnaireSection = fractal()->item($data)->transformWith(new QuestionnaireSectionTransformer())->toArray();
                $message = ['message' => trans('messages.updatedSuccesfully')];
                $endData = array_merge($message, $questionnaireSection);
                return $endData;
            } else {
                return response()->json(['message' => "Section not found."], 400);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function questionnaireSectionList($request, $id)
    {
        try {
            $data = QuestionnaireSection::get();
            if (!$id) {
                $data = QuestionnaireSection::with("questionSection")->where('sectionName', 'LIKE', "%" . $request->search . "%");
                if ($request->orderField == 'sectionName') {
                    $data->orderBy($request->orderField, $request->orderBy);
                } else {
                    $data->orderBy('sectionName', 'ASC');
                }
                $data = $data->paginate(env('PER_PAGE', 20));
                if (!empty($data)) {
                    return fractal()->collection($data)->transformWith(new QuestionnaireSectionTransformer())->toArray();
                }
            } else {
                $data = QuestionnaireSection::with("questionSection")->where('udid', $id)->where('sectionName', 'LIKE', "%" . $request->search . "%")->first();
                return fractal()->item($data)->transformWith(new QuestionnaireSectionTransformer())->toArray();
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function questionnaireSectionDelete($request, $id)
    {
        try {
            $questionnaireSection = QuestionnaireSection::where('udid', $id)->first();
            if (isset($questionnaireSection->questionnaireSectionId)) {
                $questionniare = [
                    'isActive' => 0,
                    'isDelete' => 1,
                    'deletedBy' => Auth::id(),
                    'deletedAt' => Carbon::now()
                ];
                QuestionnaireSection::where('udid', $id)->update($questionniare);

                QuestionnaireQuestion::where('entityType', "questionnaireSection")
                    ->where("referenceId", $questionnaireSection->questionnaireSectionId)
                    ->update($questionniare);

                return response()->json(['message' => trans('messages.deletedSuccesfully')]);
            } else {
                return response()->json(['message' => "Template not found."], 400);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function deleteQuestionInSection($request, $id)
    {
        try {
            $questionSection = QuestionSection::where('udid', $id)->first();
            if (isset($questionSection->questionnaireSectionId)) {
                $questionniare = [
                    'isActive' => "0",
                    'isDelete' => "1",
                    'deletedBy' => Auth::id(),
                    'deletedAt' => Carbon::now()
                ];
                QuestionSection::where('udid', $id)->update($questionniare);

                return response()->json(['message' => trans('messages.deletedSuccesfully')]);
            } elseif ($request->sectionId && $request->questionId) {
                $questionSection = QuestionSection::where('questionnaireSectionId', $request->sectionId)
                    ->where("questionId", $request->questionId)
                    ->where("isActive", 1)
                    ->first();
                if (isset($questionSection->questionnaireSectionId)) {
                    $questionniare = [
                        'isActive' => "0",
                        'isDelete' => "1",
                        'deletedBy' => Auth::id(),
                        'deletedAt' => Carbon::now()
                    ];
                    QuestionSection::where('udid', $id)->update($questionniare);

                    return response()->json(['message' => trans('messages.deletedSuccesfully')]);
                } else {
                    return response()->json(['message' => "Question not found."], 400);
                }
            } else {
                return response()->json(['message' => "Question not found."], 400);
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function assignQuestionSection($request, $id)
    {
        try {
            $post = $request->all();
            $find = QuestionnaireSection::where('udid', $id)->first();
            $objArr = array();
            if (isset($post["currentSectionId"]) && $post["templateId"] != "undefined") {
                $findCurrent = QuestionnaireSection::where('udid', $post["currentSectionId"])->first();
            }

            foreach ($post["questionId"] as $k => $v) {
                $insertObj = array(
                    'udid' => Str::uuid()->toString(),
                    'createdBy' => Auth::id(),
                    "questionnaireSectionId" => $find->questionnaireSectionId
                );

                $ques = Question::where("udid", $v)->first();
                if (isset($ques->questionId)) {
                    $insertObj["questionId"] = $ques->questionId;
                    $objArr["sectionId"] = $find->questionnaireSectionId;
                    if (isset($findCurrent->questionnaireSectionId)) {
                        $objArr["currentSectionId"] = $findCurrent->questionnaireSectionId;
                    }
                    $objArr["editType"] = "question";
                    $objArr["entityType"] = "template";
                    $objArr["cloneType"] = "Add";
                    $templateFind = array();
                    if (isset($post["templateId"]) && $post["templateId"] != "undefined") {
                        $templateFind = QuestionnaireTemplate::where('udid', $post["templateId"])->first();
                        $objArr["templateId"] = $templateFind->questionnaireTemplateId;
                    } else {
                        $objArr["templateId"] = "";
                    }
                    QuestionSection::insertGetId($insertObj);
                    QuestionChanges::cloneQuestoinChangesFromQuestionBank($objArr, $ques->questionId);
                    QuestionChanges::cloneQuestoinOptionChangesFromQuestionBank($objArr, $ques->questionId);
                }
            }

            // check if create question under options need to clone for it.
            if (isset($post["currentSectionId"]) && $post["currentSectionId"] != "undefined") {
                $findCurrent = QuestionnaireSection::where('udid', $post["currentSectionId"])->first();
                $assignQuestion = AssignOptionQuestion::where("sectionId", $findCurrent->questionnaireSectionId)->get();
                $inObj = [];
                foreach ($assignQuestion as $q) {
                    $inObj = array(
                        'udid' => Str::uuid()->toString(),
                        'createdBy' => Auth::id(),
                        "sectionId" => $find->questionnaireSectionId
                    );

                    $inObj["udid"] = Str::uuid()->toString();
                    $inObj["providerId"] = $q->providerId;
                    $inObj["providerLocationId"] = $q->providerLocationId;
                    $inObj["questionId"] = $q->questionId;
                    $inObj["programId"] = $q->programId;
                    $inObj["parentId"] = $q->parentId;
                    $inObj["referenceId"] = $q->referenceId;
                    $inObj["entityType"] = $q->entityType;
                    AssignOptionQuestion::insertGetId($inObj);
                }
            }

            $data = QuestionnaireSection::with("questionSection")->where('udid', $id)->first();

            if ($data) {
                $questionnaireSection = fractal()->item($data)->transformWith(new QuestionnaireSectionTransformer())->toArray();
                $message = ['message' => trans('messages.createdSuccesfully')];
                $endData = array_merge($message, $questionnaireSection);
                return $endData;
            } else {
                $data["data"] = [];
                return $data;
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function updateAssignQuestionSection($request, $id)
    {
        try {
            $post = $request->all();
            $find = QuestionnaireSection::where('udid', $id)->first();
            $objArr = array();
            $questionSectionData = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1];
            QuestionSection::where("questionnaireSectionId", $find->questionnaireSectionId)->update($questionSectionData);
            // QuestionChanges::where("sectionId", $find->questionnaireSectionId)->update($questionSectionData);
            foreach ($post["questionId"] as $k => $v) {
                $ques = Question::where("udid", $v)->first();
                if (isset($ques->questionId)) {

                    $insertObj = array(
                        'udid' => Str::uuid()->toString(),
                        'createdBy' => Auth::id(),
                        "questionnaireSectionId" => $find->questionnaireSectionId
                    );

                    $insertObj["questionId"] = $ques->questionId;
                    QuestionSection::insertGetId($insertObj);
                    $objArr["sectionId"] = $find->questionnaireSectionId;
                    $objArr["editType"] = "question";
                    $objArr["entityType"] = "template";
                    $objArr["cloneType"] = "update";
                    $templateFind = array();
                    if (isset($post["templateId"]) && $post["templateId"] != "undefined") {
                        $templateFind = QuestionnaireTemplate::where('udid', $post["templateId"])->first();
                        $objArr["templateId"] = $templateFind->questionnaireTemplateId;
                    } else {
                        $objArr["templateId"] = 0;
                    }
                    QuestionChanges::cloneQuestoinChangesFromQuestionBank($objArr, $ques->questionId);
                }
            }

            $data = QuestionnaireSection::with("questionSection")->where('udid', $id)->first();

            if ($data) {
                $questionnaireSection = fractal()->item($data)->transformWith(new QuestionnaireSectionTransformer())->toArray();
                $message = ['message' => trans('messages.createdSuccesfully')];
                $endData = array_merge($message, $questionnaireSection);
                return $endData;
            } else {
                $data["data"] = [];
                return $data;
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function assignQuestionnaireSection($request, $id)
    {
        try {
            $post = $request->all();
            $find = QuestionnaireTemplate::where('udid', $id)->first();
            foreach ($post["sectionId"] as $k => $v) {
                $insertObj = array(
                    'udid' => Str::uuid()->toString(),
                    'createdBy' => Auth::id(),
                    "questionnaireTempleteId" => $find->questionnaireTemplateId,
                    "entityType" => "questionnaireSection"
                );

                $ques = QuestionnaireSection::where("udid", $v)->first();
                if (isset($ques->questionnaireSectionId)) {
                    $insertObj["referenceId"] = $ques->questionnaireSectionId;
                    QuestionnaireQuestion::insertGetId($insertObj);
                }
            }
            return response()->json(['message' => trans('messages.createdSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function updateAssignQuestionnaireSection($request, $id)
    {
        try {
            $post = $request->all();
            $find = QuestionnaireTemplate::where('udid', $id)->first();

            $questionSectionData = ['deletedBy' => Auth::id(), 'isActive' => 0, 'isDelete' => 1];
            QuestionnaireQuestion::where("questionnaireTempleteId", $find->questionnaireTemplateId)
                ->where("entityType", "questionnaireSection")->update($questionSectionData);

            foreach ($post["sectionId"] as $k => $v) {
                $insertObj = array(
                    'udid' => Str::uuid()->toString(),
                    'createdBy' => Auth::id(),
                    "questionnaireTempleteId" => $find->questionnaireTemplateId,
                    "entityType" => "questionnaireSection"
                );

                $ques = QuestionnaireSection::where("udid", $v)->first();
                if (isset($ques->questionnaireSectionId)) {
                    $insertObj["referenceId"] = $ques->questionnaireSectionId;
                    QuestionnaireQuestion::insertGetId($insertObj);
                }
            }
            return response()->json(['message' => trans('messages.createdSuccesfully')], 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
