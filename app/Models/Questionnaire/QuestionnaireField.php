<?php

namespace App\Models\Questionnaire;

use App\Models\Tag\Tags;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Questionnaire\Question;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\QuestionnaireSection\QuestionnaireQuestionSection;

class QuestionnaireField extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'questionnaireFields';
    use HasFactory;
    protected $guarded = [];
 
    public function getOptionName()
    {
        return $this->hasOne(GlobalCode::class,'id','parameterValue');
    }

    public static function getQuestionnaireField($entityType,$referenceId,$parameterKey){
        return QuestionnaireField::with("getOptionName")
            ->where("referenceId",$referenceId)
            ->where("entityType",$entityType)
            ->where("parameterKey",$parameterKey)
            ->first();
    }

    public static function getAlQuestionnaireField($entityType,$referenceId){
        return QuestionnaireField::with("getOptionName")
            ->where("referenceId",$referenceId)
            ->where("entityType",$entityType)
            ->get();
    }
}
