<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Api\ConfigMsgService;
use App\Http\Requests\UpdateTemplatesRequest;
use App\Http\Requests\CommunicationTemplatesRequest;
use App\Http\Requests\GetTemplatesRequest;

class ConfigMsgController extends Controller
{
    public function gettemplates(Request $request)
    {

        //$type = $request->header('type');
        //$allowedtypes = array('sendMail', 'sendSMS','commMail','commSms');        
        return (new ConfigMsgService)->getTemplates($request);
    }
    public function gettemplateDetail($id = NULL)
    {
        if ($id) {
            return (new ConfigMsgService)->gettemplateDetail($id);
        } else {
            return $this->sendRespoce('Required parameters missing.', 422);
        }
    }
    public function update_template(UpdateTemplatesRequest $request)
    {
        return (new ConfigMsgService)->UpdateTemplate($request);
    }
    public function get_communication_template($id = NULL)
    {
        if ($id) {
            return (new ConfigMsgService)->gettemplateDetail($id);
        } else {
            return $this->sendRespoce('Required parameters missing.', 422);
        }
    }
    public function create_communication_template(CommunicationTemplatesRequest $request)
    {
        return (new ConfigMsgService)->create_communication_template($request);
    }

    public function update_communication_template()
    {
    }
}
