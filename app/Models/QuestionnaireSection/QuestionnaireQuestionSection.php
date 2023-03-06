<?php

namespace App\Models\QuestionnaireSection;

use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\QuestionnaireSection\QuestionSection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\QuestionnaireSection\QuestionnaireSection;

class QuestionnaireQuestionSection extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'questionnaireQuestionSection';
    use HasFactory;
	protected $guarded = [];

    public function questionSection(){
        return $this->hasMany(QuestionSection::class, 'questionnaireSectionId', 'questionnaireSectionId')->where("isActive",1);
    }

    public function questionnaireSection(){
        return $this->hasMany(QuestionnaireSection::class, 'questionnaireSectionId', 'questionnaireSectionId')->where("isActive",1);
    }

}
