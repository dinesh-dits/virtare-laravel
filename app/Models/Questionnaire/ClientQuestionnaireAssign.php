<?php

namespace App\Models\Questionnaire;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Questionnaire\QuestionnaireField;
use App\Models\Questionnaire\QuestionnaireTemplate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Questionnaire\ClientQuestionnaireTemplate;

class ClientQuestionnaireAssign extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'clientQuestionnaireAssign';
    use HasFactory;
	protected $guarded = [];

    public function questionnaireTemplate()
    {
        return $this->hasOne(QuestionnaireTemplate::class,'questionnaireTemplateId','questionnaireTemplateId')->where("isActive",1);
    }

    public function clientQuestionnaireTemplate()
    {
        return $this->hasOne(ClientQuestionnaireTemplate::class,'clientQuestionnaireAssignId','clientQuestionnaireAssignId')->where("isActive",1);
    }

    public function questionniareField()
    {
        return $this->hasOne(QuestionnaireField::class,'referenceId','questionnaireTemplateId')->where("isActive",1);
    }

}
