<?php

namespace App\Services\Api;

use App\Http\Controllers\Controller;
use App\Helper;
use App\Models\ConfigMessage\ConfigMessage;
use Exception;
use Illuminate\Support\Str;

class ConfigMsgService extends Controller
{
    public function getTemplates($request)
    {
        try {
            //  $type = $request->header('type');
            $type = $request->get('type');
            $templates = ConfigMessage::where('entityType', $type)->where('isActive', 1)->whereNull('deletedAt')->get();
            $responseArray = array();
            if ($templates->count() > 0) {
                foreach ($templates as $key => $template) {
                    $responseArray[$key]['templateId'] = $template->udid;
                    $responseArray[$key]['templateName'] = $template->templateName;
                }
                return $this->sendRespoce($responseArray, 200);
            }
            return $this->sendRespoce('No Active template found', 200);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function gettemplateDetail($id)
    {
        try {
            $template = ConfigMessage::where('udid', $id)->where('isActive', 1)->whereNull('deletedAt')->first();
            $responseArray = array();
            if ($template) {
                $responseArray['templateId'] = $template->udid;
                $responseArray['templateName'] = $template->templateName;
                $responseArray['subject'] = $template->subject;
                $responseArray['messageBody'] = $template->messageBody;
                if ($template->entityType != 'commMail' && $template->entityType != 'commSms')
                    $responseArray['messageBodyParameter'] = $template->messageBodyParameter;
                return $this->sendRespoce($responseArray, 200);
            }
            return $this->sendRespoce('Template not found', 404);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    public function UpdateTemplate($request)
    {
        try {
            $template = ConfigMessage::where('udid', $request->templateId)->where('isActive', 1)->whereNull('deletedAt')->first();
            if (isset($template->id)) {
                $update['messageBody'] = $request->messageBody;
                $update['subject'] = $request->subject;
                $update['templateName'] = $request->templateName;

                $template->update($update);
                return $this->sendRespoce('Template Updated succesfully.', 200);
            }
            return $this->sendRespoce('Template not found.', 404);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }


    public function create_communication_template($request)
    {
        try {
            $data['udid'] = Str::uuid()->toString();
            $data['subject'] = $request->subject;
            $data['messageBody'] = $request->messageBody;
            $data['templateName'] = $request->templateName;
            $data['entityType'] = $request->entityType;
            $data['type'] = str_replace(' ', '', $request->templateName);
            $template = ConfigMessage::create($data);
            if ($template->id) {
                return $this->sendRespoce('Template created succesfully.', 201);
            }
            return $this->sendRespoce('Unable to create template, please try again later', 400);
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
