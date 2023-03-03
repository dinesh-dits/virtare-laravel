<?php

namespace App\Models\Questionnaire;

use App\Models\Questionnaire\Question;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientQuestionResponse extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'clientFillUpQuestionnaireQuestions';
    use HasFactory;
	protected $guarded = [];

    public function questionnaireQuestion()
    {
        return $this->hasOne(Question::class,'questionId','questionnaireQuestionId');
    }
}
