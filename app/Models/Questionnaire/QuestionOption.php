<?php

namespace App\Models\Questionnaire;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Questionnaire\AssignOptionQuestion;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuestionOption extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'questionOptions';
    use HasFactory;
	protected $guarded = [];

    public function score()
    {
        return $this->hasOne(QuestionScore::class,  'referenceId','questionOptionId',)->where('entityType',254);
    }

    public function program()
    {
        return $this->hasMany(QuestionOptionProgram::class,'questionOptionId','questionOptionId');
    }

    public function programScore()
    {
        return $this->hasMany(QuestionScore::class,'referenceId','questionOptionProgramId')->where('entityType',255);
    }

    public function assignQuestion()
    {
        return $this->hasMany(AssignOptionQuestion::class,'referenceId','questionOptionId')->where('entityType',"questionOption")->where('isActive',1);
    }

}
