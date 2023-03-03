<?php

namespace App\Models\Questionnaire;

use App\Models\GlobalCode\GlobalCode;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuestionScore extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'questionScores';
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
}
