<?php

namespace App\Models\Questionnaire;

use App\Models\Tag\Tags;
use App\Models\User\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Questionnaire\Question;
use Illuminate\Database\Eloquent\Model;
use App\Models\Questionnaire\QuestionOption;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuestionChanges extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'questionUpdate';
    use HasFactory;
	protected $guarded = [];

    public function tags()
    {
        return $this->hasMany(Tags::class,'referenceId','questionId')->where("entityType","253")->where("isActive",1);
    }

    public function questionsDataType()
    {
        return $this->hasOne(GlobalCode::class,'id', 'dataTypeId');
    }
    
    public function questionsType()
    {
        return $this->hasOne(GlobalCode::class,'id', 'questionType');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public function getQuestion()
    {
        return $this->hasMany(Question::class,'questionId','questionId');
    }

    public function questionOption()
    {
        return $this->hasMany(QuestionOption::class,'questionId','questionId');
    }

    public function score()
    {
        return $this->hasOne(QuestionScore::class,  'referenceId','questionId',)->where('entityType',253);
    }

    public function questionnaireQuestion()
    {
        return $this->hasMany(QuestionnaireQuestion::class,'questionId','questionId');
    }

    public function questionnaireField()
    {
        return $this->hasMany(QuestionnaireField::class,'referenceId','questionId')
        ->where("entityType","questions")
        ->where("isActive",1);
    }

    public static function insertQuestionChanges($data,$templateId,$sectionId,$parentId,$childId,$postArr)
    {
        $insertObj = [
            'udid' => Str::uuid()->toString(),
            'createdBy' => Auth::id(),
            'providerId' => $data["provider"],
            'programId' => $data["programId"],
            'providerLocationId' => $data["providerLocation"],
            'questionId' => $data["questionId"],
            'entityType' => $data["entityType"],
            'dataObj'    => json_encode($postArr)
        ];

        if($templateId > 0){
            $insertObj["templateId"] = $templateId;
        }

        if($sectionId > 0){
            $insertObj["sectionId"] = $sectionId;
        }

        if($parentId > 0){
            $insertObj["parentId"] = $parentId;
        }

        if($parentId > 0){
            $insertObj["childId"] = $childId;
        }

        return QuestionChanges::insertGetId($insertObj);
    }
    
    public static function updateQuestionChanges($questionChangeId,$postArr)
    {
        $insertObj = [
            'dataObj'    => json_encode($postArr)
        ];

        return QuestionChanges::where("udid",$questionChangeId)->update($insertObj);
    }

    public static function cloneQuestoinOptionChangesFromQuestionBank($data,$questionId){
        $insertObj = array();
        $insertOptionQestionObj = array();
        $lastId = 0;

        // clone parent question option
        if(isset($data["currentSectionId"]) && !empty($data["currentSectionId"])){
            if(isset($data["currentSectionId"])){
                $questionCHange = QuestionChanges::where("sectionId",$data["currentSectionId"])
                ->where("entityType","templateOption")
                ->where("isActive",1)
                ->get();
            }else{
                $questionCHange = QuestionChanges::where("questionId",$questionId)
                ->where("entityType","questionBankOption")
                ->where("isActive",1)
                ->get();
            }

            if(!empty($questionCHange) && count($questionCHange) > 0){
                foreach($questionCHange as $questionCH){
                    $insertObj = [
                        'udid' => Str::uuid()->toString(),
                        'createdBy' => Auth::id(),
                        'providerId' => $questionCH->providerId,
                        'programId' => $questionCH->programId,
                        'providerLocationId' => $questionCH->providerLocationId,
                        'questionId' => $questionCH->questionId,
                        'entityType' => $questionCH->entityType,
                        'parentId' => $questionCH->parentId,
                        'childId' => $questionCH->childId,
                        'dataObj'    => $questionCH->dataObj
                    ];

                    if($data["templateId"] > 0){
                        $insertObj["templateId"] = $data["templateId"];
                    }
            
                    if($data["sectionId"] > 0){
                        $insertObj["sectionId"] = $data["sectionId"];
                    }

                    QuestionChanges::insertGetId($insertObj);
                }
            }

            $questionChildOption = QuestionChanges::where("parentId",$questionId)
                ->where("entityType","questionBankOption")
                ->where("isActive",1)
                ->get();

            if(!empty($questionChildOption) && count($questionChildOption) > 0){
                foreach($questionChildOption as $questionCHild){
                    $insertObj = [
                        'udid' => Str::uuid()->toString(),
                        'createdBy' => Auth::id(),
                        'providerId' => $questionCHild->providerId,
                        'programId' => $questionCHild->programId,
                        'providerLocationId' => $questionCHild->providerLocationId,
                        'questionId' => $questionCHild->questionId,
                        'entityType' => $questionCHild->entityType,
                        'parentId' => $questionCHild->parentId,
                        'childId' => $questionCHild->childId,
                        'dataObj'    => $questionCHild->dataObj
                    ];

                    if($data["templateId"] > 0){
                        $insertObj["templateId"] = $data["templateId"];
                    }
            
                    if($data["sectionId"] > 0){
                        $insertObj["sectionId"] = $data["sectionId"];
                    }

                    QuestionChanges::insertGetId($insertObj);
                }
            }

        }
    }

    public static function cloneQuestoinChangesFromQuestionBank($data,$questionId){
        $insertObj = array();
        $insertOptionQestionObj = array();
        $lastId = 0;
        if(isset($data["editType"]) && !empty($data["editType"])){
            if(isset($data["currentSectionId"])){
                $questionCH = QuestionChanges::where("questionId",$questionId)
                ->where("entityType","template")
                ->where("isActive",1)
                ->first();
            }else{
                $questionCH = QuestionChanges::where("questionId",$questionId)
                ->where("entityType",$data["editType"])
                ->where("isActive",1)
                ->first();
            }
            // echo $questionId;
            // print_r($data);
            // echo "aaa";
            // print_r($questionCH);
            // die;
            if(isset($questionCH->udid)){
                
                $questionExists = QuestionChanges::where("questionId", $questionId);
                                if(isset($data["currentSectionId"])){
                                    $questionExists->where("sectionId",$data["currentSectionId"]);    
                                }elseif($data["sectionId"] > 0){
                                    $questionExists->where("sectionId",$data["sectionId"]);
                                    // $questionExists->where("entityType",$data["entityType"]);
                                }
                                $questionExists->where("entityType",$data["entityType"]);
                $questionExists =  $questionExists->first();

                if(isset($questionExists->udid)  && $data["cloneType"] == "update"){
                    $insertObj = [
                        'dataObj'    => $questionCH->dataObj
                    ];
                    QuestionChanges::where("udid",$questionExists->udid)->update($insertObj);
                    
                }else{
                    $insertObj = [
                        'udid' => Str::uuid()->toString(),
                        'createdBy' => Auth::id(),
                        'providerId' => $questionCH->providerId,
                        'programId' => $questionCH->programId,
                        'providerLocationId' => $questionCH->providerLocationId,
                        'questionId' => $questionCH->questionId,
                        'entityType' => $data["entityType"],
                        'dataObj'    => $questionCH->dataObj
                    ];

                    if($data["templateId"] > 0){
                        $insertObj["templateId"] = $data["templateId"];
                    }
            
                    if($data["sectionId"] > 0){
                        $insertObj["sectionId"] = $data["sectionId"];
                    }
                    $lastId = QuestionChanges::insertGetId($insertObj);
                }
            }
            if(isset($data["currentSectionId"])){
                $questionOptionCH = QuestionChanges::where("parentId",$questionId)
                ->where("entityType",$data["entityType"])
                ->where("isActive",1)
                ->get();
            }else{
                $questionOptionCH = QuestionChanges::where("parentId",$questionId)
                ->where("entityType",$data["editType"])
                ->where("isActive",1)
                ->get();
            }

            if(!empty($questionOptionCH) && count($questionOptionCH->toArray()) > 0){
                
                foreach($questionOptionCH as $questionOption){
                    $questionOptionCheck = QuestionChanges::where("questionId", $questionOption->questionId);
                    $questionOptionCheck->where("entityType","template");
                    $questionOptionCheck->where("parentId",$questionOption->parentId);
                    $questionOptionCheck->where("childId",$questionOption->childId);
                    $questionOptionCheck->where("sectionId",$data["sectionId"]);
                    $questionOptionCheck = $questionOptionCheck->first();
                    
                    if(isset($questionOptionCheck->udid)){
                        $insertObj = [
                            'dataObj'    => $questionOption->dataObj,
                            'parentId'    => $questionOption->questionId,
                            'parentId'    => $questionOption->parentId,
                            'childId'    => $questionOption->childId,
                        ];
                        QuestionChanges::where("udid",$questionOptionCheck->udid)->update($insertObj);
                    }else{
                        $insertOptionQestionObj = [
                            'udid' => Str::uuid()->toString(),
                            'providerId' => $questionOption->providerId,
                            'programId' => $questionOption->programId,
                            'providerLocationId' => $questionOption->providerLocationId,
                            'questionId' => $questionOption->questionId,
                            'parentId' => $questionOption->parentId,
                            'childId' => $questionOption->childId,
                            'entityType' => $data["entityType"],
                            'dataObj'    => $questionOption->dataObj
                        ];
        
                        if($data["templateId"] > 0){
                            $insertOptionQestionObj["templateId"] = $data["templateId"];
                        }
                
                        if($data["sectionId"] > 0){
                            $insertOptionQestionObj["sectionId"] = $data["sectionId"];
                        }
                        QuestionChanges::insertGetId($insertOptionQestionObj);
                    }
                }
            }
            return $lastId;
        }

    }


    public static function cloneQuestoinInOptionFromQuestionBank($data,$questionId){
        $insertObj = array();
        $insertOptionQestionObj = array();
        $lastId = 0;
        if(isset($data["editType"]) && !empty($data["editType"])){
            // clone main question
            $questionCH = QuestionChanges::where("questionId",$questionId)
            ->where("entityType",$data["editType"])
            ->where("isActive",1)
            ->first();

            if(isset($questionCH->udid)){
                
                $questionExists = QuestionChanges::where("questionId", $questionId);
                                    if($data["sectionId"] > 0){
                                        $questionExists->where("sectionId",$data["sectionId"]);
                                    }
                                    if(isset($data["parentId"])){
                                        $questionExists->where("parentId",$data["parentId"]);
                                    }
                                    if(isset($data["childId"])){
                                        $questionExists->where("childId",$data["childId"]);
                                    }
                                    $questionExists->where("entityType","template");
                                    $questionExists->where("entityType",$data["entityType"]);
                                    
                $questionExists =  $questionExists->first();
                if(isset($questionExists->udid)){
                    return true;
                }else{
                    $insertObj = [
                        'udid' => Str::uuid()->toString(),
                        'createdBy' => Auth::id(),
                        'providerId' => $questionCH->providerId,
                        'programId' => $questionCH->programId,
                        'providerLocationId' => $questionCH->providerLocationId,
                        'questionId' => $questionCH->questionId,
                        'entityType' => $data["entityType"],
                        'dataObj'    => $questionCH->dataObj
                    ];

                    if($data["templateId"] > 0){
                        $insertObj["templateId"] = $data["templateId"];
                    }
            
                    if($data["sectionId"] > 0){
                        $insertObj["sectionId"] = $data["sectionId"];
                    }
            
                    if($data["parentId"] > 0){
                        $insertObj["parentId"] = $data["parentId"];
                    }
            
                    if($data["childId"] > 0){
                        $insertObj["childId"] = $data["childId"];
                    }
                    $lastId = QuestionChanges::insertGetId($insertObj);
                }
            }
        }
        return $lastId;
    }

}
