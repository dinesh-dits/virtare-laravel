<?php

namespace App\Models\Questionnaire;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientResponseAnswer extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'clientResponseAnswer';
    use HasFactory;
	protected $guarded = [];

    public function programScore()
    {
        return $this->hasOne(ClientQuestionScore::class,  'referenceId','clientFillupQuestionnaireQuestionId')->where("isActive","1");
    }
}
