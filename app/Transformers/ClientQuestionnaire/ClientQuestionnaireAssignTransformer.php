<?php

namespace App\Transformers\ClientQuestionnaire;

use App\Models\Staff\Staff;
use App\Models\Patient\Patient;
use League\Fractal\TransformerAbstract;
use App\Models\Questionnaire\ClientQuestionnaireTemplate;

class ClientQuestionnaireAssignTransformer extends TransformerAbstract
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
        if($data->clientFillUpQuestionnaireId){
            $cqt = ClientQuestionnaireTemplate::where("udid",$data->clientFillUpQuestionnaireUdid)->first();
            if(isset($cqt->status)){
                $status = $cqt->status;
            }else{
                $status = "Pending";
            }
        }else{
            $status = "Pending";
        }
        $fillUpUserId = null;
        $fillUpEntityType = null;
        if(isset($data->fillUpReferenceId) && !empty($data->fillUpReferenceId)){
            if($data->fillUpEntityType == "246"){
                // staff
                $staff = Staff::where("id",$data->fillUpReferenceId)->first();
                $fillUpUserId = $staff->udid;
                $fillUpEntityType = "Staff";
            }elseif($data->fillUpEntityType == "247"){
                //patient
                $patient = Patient::where("id",$data->fillUpReferenceId)->first();
                $fillUpUserId = $patient->udid;
                $fillUpEntityType = "Patient";
            }
        }
        
        return[ 
            'id'=> $data->udid,
            'questionnaireTemplateId'=>$data->questionnaireTemplateUdid,
            'clientQuestionnaireTemplateId'=>$data->clientFillUpQuestionnaireUdid,
            'fillUpUserId'=>$fillUpUserId,
            'fillUpUserEntityTypeId'=>$data->fillUpEntityType,
            'fillUpUserEntityType'=>$fillUpEntityType,
            'templateName'=>$data->templateName,
            'templateTypeId'=>$data->templateTypeId,
            'templateType'=>$data->templateType,
            'userId'=> $data->assignToUdid,
            'userName'=>$data->assignTo,
            'createdBy' =>$data->assignByUdid,
            'assignBy' =>$data->assignBy,
            'referenceId'=>$data->referenceId, 
            'entityType'=>$data->entityType, 
            'entity'=>$data->udid,
            'status'=> $status,
            "createdAt" => strtotime($data->createdAt),
            'isActive' => $data->isActive ? True : False,
        ];
      
    }
}
