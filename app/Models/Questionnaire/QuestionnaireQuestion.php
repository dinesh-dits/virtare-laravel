<?php

namespace App\Models\Questionnaire;

use App\Models\GlobalCode\GlobalCode;
use App\Models\Questionnaire\Question;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Questionnaire\QuestionnaireTemplate;
use App\Models\QuestionnaireSection\QuestionSection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\QuestionnaireSection\QuestionnaireSection;

class QuestionnaireQuestion extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'questionnaireQuestions';
    use HasFactory;
    protected $guarded = [];

    public function question()
    {
        return $this->belongsTo(Question::class,'referenceId','questionId');
    }
    
    public function questionnaireTemplate()
    {
        return $this->hasOne(QuestionnaireTemplate::class, 'questionnaireTemplateId','questionnaireQuestionId');
    }

    public function questionnaireSection()
    {
        return $this->belongsTo(QuestionnaireSection::class,'referenceId','questionnaireSectionId');
    }
    
    public function questionSection(){
        return $this->hasMany(QuestionSection::class, 'questionnaireSectionId', 'questionnaireSectionId')->where("isActive",1);
    }
}
