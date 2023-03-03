<?php

namespace App\Models\QuestionnaireSection;

use App\Models\GlobalCode\GlobalCode;
use App\Models\Questionnaire\Question;
use Illuminate\Database\Eloquent\Model;
use App\Models\Questionnaire\QuestionScore;
use App\Models\Questionnaire\QuestionOption;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuestionSection extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'questionSections';
    use HasFactory;
	protected $guarded = [];

    public function question(){
        return $this->hasMany(Question::class, 'questionId', 'questionId')->where("isActive",1);
    }

    public function questionnaireSection(){
        return $this->hasMany(QuestionnaireSection::class, 'questionnaireSectionId', 'questionnaireSectionId')->where("isActive",1);
    }

    public function questionsDataType()
    {
        return $this->hasOne(GlobalCode::class,'id', 'dataTypeId');
    }
   
    public function questionOption()
    {
        return $this->hasMany(QuestionOption::class,'questionId','questionId');
    }

    public function score()
    {
        return $this->hasOne(QuestionScore::class,  'referenceId','questionId',)->where('entityType',255);
    }
}
