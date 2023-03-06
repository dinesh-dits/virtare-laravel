<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\QuestionnaireService;
use App\Http\Requests\Questionnaire\QuestionRequest;

class QuestionnaireController extends Controller
{

    public function addQuestionnaire(Request $request)
    {
        return (new QuestionnaireService)->questionnaireAdd($request);
    }

    public function updateQuestionnaire(Request $request,$id)
    {
        return (new QuestionnaireService)->questionnaireUpdate($request,$id);
    }

    public function listQuestionnaire(Request $request,$id=null)
    {
        return (new QuestionnaireService)->questionnaireList($request,$id);
    }

    public function deleteQuestionnaire(Request $request,$id)
    {
        return (new QuestionnaireService)->questionnaireDelete($request,$id);
    }

    public function listQuestion(Request $request, $id = null)
    {
        return (new QuestionnaireService)->listQuestion($request, $id);
    }

    public function deleteQuestion(Request $request, $id)
    {
        return (new QuestionnaireService)->questionDelete($request, $id);
    }

    public function addQuestion(QuestionRequest $request, $id = null)
    {
        return (new QuestionnaireService)->createQuestion($request, $id);
    }
    
    public function updateQuestion(Request $request, $id = null)
    {
        return (new QuestionnaireService)->updateQuestion($request, $id);
    }

    public function addQuestionOption(Request $request, $id = null)
    {
        return (new QuestionnaireService)->createQuestionOption($request, $id);
    }

    public function updateQuestionOption(Request $request, $id = null)
    {
        return (new QuestionnaireService)->updateQuestionOption($request, $id);
    }

    public function deleteQuestionOption(Request $request, $id = null)
    {
        return (new QuestionnaireService)->deleteQuestionOption($request, $id);
    }
    
    public function assignOptionQuestion(Request $request)
    {
        return (new QuestionnaireService)->assignOptionQuestion($request);
    }
    
    public function assignQuestion(Request $request,$id)
    {
        return (new QuestionnaireService)->assignQuestion($request,$id);
    }
    
    public function deleteNestedQuestion(Request $request,$id)
    {
        return (new QuestionnaireService)->deleteNestedQuestion($request,$id);
    }
    
    public function updateAssignQuestion(Request $request,$id)
    {
        return (new QuestionnaireService)->updateAssignQuestion($request,$id);
    }

    public function listAssignQuestion(Request $request,$id)
    {
        return (new QuestionnaireService)->assignQuestionList($request,$id);
    }
    public function getQuestionnaireDataType(Request $request,$id)
    {
        return (new QuestionnaireService)->getQuestionnaireDataType($request,$id);
    }

    public function getQuestionnaireScoreType(Request $request,$id)
    {
        return (new QuestionnaireService)->getQuestionnaireScoreType($request,$id);
    }
    
    public function getTemplateQuestionnaire(Request $request,$id=null)
    {
        return (new QuestionnaireService)->getTemplateQuestionnaire($request,$id);
    }

    public function getAssignedTemplateUserList(Request $request,$id=null)
    {
        return (new QuestionnaireService)->getAssignedTemplateUserList($request,$id);
    }
    
    public function getQuestionnaireCustomField(Request $request,$id=null)
    {
        return (new QuestionnaireService)->getQuestionnaireCustomField($request,$id);
    }
    
    public function getQuestionnaireGlobalCode(Request $request,$id=null)
    {
        return (new QuestionnaireService)->getQuestionnaireGlobalCode($request,$id);
    }
    
    public function getSectionBasedQuestion(Request $request,$id=null)
    {
        return (new QuestionnaireService)->getSectionBasedQuestion();
    }

}
