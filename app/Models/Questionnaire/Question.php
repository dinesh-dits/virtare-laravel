<?php

namespace App\Models\Questionnaire;

use App\Models\Tag\Tags;
use App\Models\User\User;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use App\Models\Questionnaire\QuestionOption;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'questions';
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

}
