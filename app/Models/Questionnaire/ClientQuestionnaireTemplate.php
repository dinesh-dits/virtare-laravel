<?php

namespace App\Models\Questionnaire;

use App\Models\User\User;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Questionnaire\Questions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Questionnaire\QuestionnaireTemplate;
use App\Models\Questionnaire\ClientQuestionResponse;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientQuestionnaireTemplate extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'clientFillUpQuestionnaire';
    use HasFactory;
	protected $guarded = [];

    public function questionnaireTemplate()
    {
        return $this->hasOne(QuestionnaireTemplate::class,'questionnaireTemplateId','questionnaireTemplateId')->where("isActive",1);
    } 

    public function templateType()
    {
        return $this->hasOne(GlobalCode::class,'id','templateTypeId');
    }

    public function clientQuestionResponse()
    {
        return $this->hasMany(ClientQuestionResponse::class,'clientFillUpQuestionnaireId','clientFillUpQuestionnaireId')->where("isActive","1");
    }

}
