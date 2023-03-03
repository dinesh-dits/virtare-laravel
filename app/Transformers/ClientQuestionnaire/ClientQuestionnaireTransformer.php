<?php

namespace App\Transformers\ClientQuestionnaire;

use App\Models\User\User;
use App\Models\Staff\Staff;
use App\Models\Patient\Patient;
use App\Models\GlobalCode\GlobalCode;
use League\Fractal\TransformerAbstract;
use App\Services\Api\ClientQuestionnaireService;
use App\Models\Questionnaire\ClientResponseAnswer;
use App\Models\Questionnaire\ClientQuestionnaireAssign;
use App\Transformers\ClientQuestionnaire\ClientQuestionResponseTransformer;

class ClientQuestionnaireTransformer extends TransformerAbstract
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
        $entity = Null;
        if(isset($data->entityType)){
            $entity = GlobalCode::where("id",$data->entityType)->first();
            if($entity){
                $entity = $entity->name;
            }
        }
        
        $templateType = Null;
        if(isset($data->questionnaireTemplate) && !empty($data->questionnaireTemplate)){
            $templateType = GlobalCode::where("id",$data->questionnaireTemplate->templateTypeId)->first();
            if($templateType){
                $templateType = $templateType->name;
            }
        }

        $userId = "";
        $fullName = "";
        $fillUpUser = "";
        $fillUpUserId = "";
        $fillUpEntityType = "";

        if($data->referenceId){
            $user = User::where("id",$data->createdBy)->first();
            if(isset($user->roleId) && $user->roleId == "1" || isset($user->roleId) && $user->roleId == "3"){
                $staff = Staff::where("userId",$user->id)->first();
                $fullName = $staff->firstName." ".$staff->lastName;
                $userId = $staff->udid;
            }elseif(isset($user->roleId) && $user->roleId == "4"){
                $patient = Patient::where("userId",$user->id)->first();
                $fullName = $patient->firstName." ".$patient->lastName;
                $userId = $patient->udid;
            }

            if($data->entityType == "246"){
                // for staff
                $assignStaff = Staff::where("id",$data->referenceId)->first();
                if(isset($assignStaff->udid)){
                    $fillUpUser = $assignStaff->firstName." ".$assignStaff->lastName;
                    $fillUpUserId = $assignStaff->udid;
                    $fillUpEntityType = "Staff";
                }
            }elseif($data->entityType == "247"){
                // for Patient
                $assignPatient = Patient::where("id",$data->referenceId)->first();
                if(isset($assignPatient->udid)){
                    $fillUpUser = $assignPatient->firstName." ".$assignPatient->lastName;
                    $fillUpUserId = $assignPatient->udid;
                    $fillUpEntityType = "Patient";
                }

            }


        }

        $totalFillupAns = 0;
        $cleintQuestionAns = array();
        if(isset($data->clientQuestionResponse) && !empty($data->clientQuestionResponse)){
            foreach($data->clientQuestionResponse as $v){
                if(isset($v->clientFillupQuestionnaireQuestionId) && !empty($v->clientFillupQuestionnaireQuestionId)){
                    $cleintQuestionAns = ClientResponseAnswer::where("clientFillupQuestionnaireQuestionId",$v->clientFillupQuestionnaireQuestionId)->where("isActive","1")->first();
                    if(!empty($cleintQuestionAns)){
                        $totalFillupAns++;
                    }
                }
            }
        }

        $totalQuestion = 0;
        if(count($data->clientQuestionResponse) > 0){
            $totalQuestion = count($data->clientQuestionResponse);
        }


        if(isset($data->udid)){
            $score = (new ClientQuestionnaireService)->getQuestionnaireTemplateScore($data->udid);
        }else{
            $score = "";
        }

        // getting assign by or fillup by user detail.
        $assignTo = "";
        $assignToId = "";
        $assignToEntityType = "";
        $assignBy = "";
        $assignById = "";
        $assignByEntityType = "";

        if(isset($data->clientQuestionnaireAssignId)){
            $clientQuestionnaireAssign = ClientQuestionnaireAssign::where("clientQuestionnaireAssignId",$data->clientQuestionnaireAssignId)->first();
            // get fillup by
            if($clientQuestionnaireAssign->entityType == "246"){
                // for staff
                $staff = Staff::where("id",$clientQuestionnaireAssign->referenceId)->first();
                if(isset($staff->udid)){
                    $assignTo = $staff->firstName." ".$staff->lastName;
                    $assignToId = $staff->udid;
                    $assignToEntityType = "Staff";
                }
            }elseif($clientQuestionnaireAssign->entityType == "247"){
                // for Patient
                $patient = Patient::where("id",$clientQuestionnaireAssign->referenceId)->first();
                if(isset($patient->udid)){
                    $assignTo = $patient->firstName." ".$patient->lastName;
                    $assignToId = $patient->udid;
                    $assignToEntityType = "Patient";
                }

            }

            if(isset($clientQuestionnaireAssign->createdBy)){
                // user
                $user = User::where("id",$clientQuestionnaireAssign->createdBy)->first();
                if(isset($user->udid)){
                    if($user->roleId == "3" || $user->roleId == "1"){
                        // for staff
                        $staff = Staff::where("userId",$user->id)->first();
                        if(isset($staff->udid)){
                            $assignBy = $staff->firstName." ".$staff->lastName;
                            $assignById = $staff->udid;
                            $assignByEntityType = "Staff";
                        }
                    }elseif($user->roleId == "4"){
                        // for Patient
                        $patient = Patient::where("userId",$user->id)->first();
                        if(isset($patient->udid)){
                            $assignBy = $patient->firstName." ".$patient->lastName;
                            $assignById = $patient->udid;
                            $assignByEntityType = "Patient";
                        }
                    }
                }
            }
        }
        
        $objArr = [ 
            'id'=> $data->udid,
            'questionnaireTemplateId'=>(!empty($data->questionnaireTemplate))?$data->questionnaireTemplate->udid:"",
            'clientQuestionnaireAssignId'=>(!empty($clientQuestionnaireAssign))?$clientQuestionnaireAssign->udid:"",
            'templateName'=>(!empty($data->questionnaireTemplate))?$data->questionnaireTemplate->templateName:"",
            'templateTypeId'=>(!empty($data->questionnaireTemplate))?$data->questionnaireTemplate->templateTypeId:"",
            'templateType'=>(!empty($data->questionnaireTemplate))?$templateType:"",
            'fillUpUser' =>  $fillUpUser? $fillUpUser:"",
            'fillUpUserId' => $fillUpUserId? $fillUpUserId:"",
            'fillUpEntityType' => $fillUpEntityType? $fillUpEntityType:"",
            'assignTo' => $assignTo? $assignTo:"",
            'assignToId' => $assignToId? $assignToId:"",
            'assignToEntityType' => $assignToEntityType? $assignToEntityType:"",
            'assignBy' => $assignBy? $assignBy:"",
            'assignById' => $assignById? $assignById:"",
            'assignByEntityType' => $assignByEntityType? $assignByEntityType:"",
            'status'=> $data->status? $data->status:"pending",
            'percentageStatus'=> $data->percentage."%",
            'userId'=> $userId,
            'userName'=>$fullName,
            'referenceId'=>$data->referenceId,
            'entityType'=>$data->entityType, 
            'entity'=>$entity,
            'score' => (!empty($score))?$score:'',
            "createdAt" => strtotime($data->createdAt),
            'isActive' => $data->isActive ? True : False,
            "totalFillupAns" => $totalFillupAns,
            "totalQuestion" => $totalQuestion
        ];

        if(isset($data["id"])){
            $objArr["clientQuestionResponse"] = isset($data->clientQuestionResponse) && !empty($data->clientQuestionResponse)?fractal()->collection($data->clientQuestionResponse)->transformWith(new ClientQuestionResponseTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray():[];
        }
        return $objArr;
    }
}
