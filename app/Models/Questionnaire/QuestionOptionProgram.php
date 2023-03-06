<?php

namespace App\Models\Questionnaire;

use App\Models\User\User;
use App\Models\Program\Program;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuestionOptionProgram extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'questionOptionPrograms';
    use HasFactory;
	protected $guarded = [];

    public function questionsDataType()
    {
        return $this->belongsTo(GlobalCode::class, 'dataTypeId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public function program()
    {
        return $this->hasOne(Program::class,'id', 'programId');
    }

    public function score()
    {
        return $this->hasOne(QuestionScore::class,  'referenceId','questionOptionProgramId',)->where('entityType',255);
    }
}
