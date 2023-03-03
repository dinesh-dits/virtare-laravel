<?php

namespace App\Models\Questionnaire;

use App\Models\Tag\Tags;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Questionnaire\Question;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Questionnaire\QuestionnaireField;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\QuestionnaireSection\QuestionnaireQuestionSection;
use App\Models\QuestionnaireSection\QuestionSection;

class QuestionnaireTemplate extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'questionnaireTemplates';
    use HasFactory;
    protected $guarded = [];

    public function tags()
    {
        return $this->hasMany(Tags::class,'referenceId','questionnaireTemplateId')->where("entityType","252")->where("isActive",1);
    }

    public function templateType()
    {
        return $this->hasOne(GlobalCode::class,'id','templateTypeId');
    }

    public function questionnaireQuestion()
    {
        return $this->hasMany(QuestionnaireQuestion::class,'questionnaireTempleteId','questionnaireTemplateId')->where("isActive",1);
    }

    public function question()
    {
        return $this->belongsTo(Question::class,'questionId','referenceId')->where("isActive",1);
    }

    public function assignedSection()
    {
        return $this->hasMany(QuestionnaireQuestionSection::class,'referenceId','questionnaireTemplateId')->where("isActive",1);
    }

    public function questionnaireField()
    {
        return $this->hasMany(QuestionnaireField::class,'referenceId','questionnaireTemplateId')
        ->where("entityType","questionnaireTemplate")
        ->where("isActive",1);
    }
    public function templateQuestion()
    {
        /*return $this->hasMany(QuestionSection::class,'referenceId','questionnaireSectionId')
        ->where("entityType","questionnaireTemplate")
        ->where("isActive",1);*/

        return $this->hasManyThrough(
            QuestionnaireQuestion::class,    
            QuestionSection::class,         
            'questionnaireSectionId', // Foreign key on the items table...            
            'referenceId', // Foreign key on the types table...    
            'questionnaireQuestionId', // Local key on the users table...        
            'questionSectionId' // Local key on the categories table...
           
     );
    }
   
}
