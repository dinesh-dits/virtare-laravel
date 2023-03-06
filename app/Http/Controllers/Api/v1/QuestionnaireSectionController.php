<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\QuestionnaireSectionService;
use App\Http\Requests\QuestionnaireSection\QuestionnaireSectionRequest;

class QuestionnaireSectionController extends Controller
{
    public function addQuestionnaireSection(QuestionnaireSectionRequest $request)
    {
        return (new QuestionnaireSectionService)->questionnaireSectionAdd($request);
    }

    public function updateQuestionnaireSection(Request $request,$id)
    {
        return (new QuestionnaireSectionService)->questionnaireSectionUpdate($request,$id);
    }

    public function listQuestionnaireSection(Request $request,$id=null)
    {
        return (new QuestionnaireSectionService)->questionnaireSectionList($request,$id);
    }

    public function deleteQuestionnaireSection(Request $request,$id)
    {
        return (new QuestionnaireSectionService)->questionnaireSectionDelete($request,$id);
    }

    public function deleteQuestionInSection(Request $request,$id)
    {
        return (new QuestionnaireSectionService)->deleteQuestionInSection($request,$id);
    }

    public function assignQuestionSection(Request $request,$id=null)
    {
        return (new QuestionnaireSectionService)->assignQuestionSection($request,$id);
    }

    public function updateAssignQuestionSection(Request $request,$id=null)
    {
        return (new QuestionnaireSectionService)->updateAssignQuestionSection($request,$id);
    }

    public function assignQuestionnaireSection(Request $request,$id=null)
    {
        return (new QuestionnaireSectionService)->assignQuestionnaireSection($request,$id);
    }

    public function updateAssignQuestionnaireSection(Request $request,$id=null)
    {
        return (new QuestionnaireSectionService)->updateAssignQuestionnaireSection($request,$id);
    }
}