<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\ExcelGeneratorService;
use App\Services\Api\ClientQuestionnaireService;
use App\Services\Api\ExportReportRequestService;
use App\Http\Requests\Questionnaire\AssignQuestionnaireClientRequest;

class ClientQuestionnaireController extends Controller
{
    public function assignQuestionnaireTemplate(Request $request)
    {
        return (new ClientQuestionnaireService)->addClientQuestionnaireTemplate($request);
    }
    
    public function getAssignQuestionnaireTemplate(Request $request,$id="")
    {
        return (new ClientQuestionnaireService)->getAssignQuestionnaireTemplate($request,$id);
    }

    public function questionnaireTemplateByUser(Request $request,$id="")
    {
        return (new ClientQuestionnaireService)->questionnaireTemplateByUser($request,$id);
    }

    public function addQuestionnaireTemplateByUsers(Request $request,$id="")
    {
        return (new ClientQuestionnaireService)->addQuestionnaireTemplateByUsers($request,$id);
    }

    public function getQuestionnaireScore(Request $request,$id="")
    {
        return (new ClientQuestionnaireService)->getQuestionnaireScore($request,$id);
    }

    public function getfillUpQuestionnaire(Request $request,$id="")
    {
        return (new ClientQuestionnaireService)->getfillUpQuestionnaire($request,$id);
    }

    public function getfillUpQuestionnaireForApp(Request $request,$id="")
    {
        return (new ClientQuestionnaireService)->getfillUpQuestionnaireForApp($request,$id);
    }

    public function getNextQuestion(Request $request,$id="")
    {
        return (new ClientQuestionnaireService)->getNextQuestion($request,$id);
    }

}