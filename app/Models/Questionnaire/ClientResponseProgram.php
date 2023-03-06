<?php

namespace App\Models\Questionnaire;

use App\Models\Program\Program;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientResponseProgram extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'clientResponsePrograms';
    use HasFactory;
	protected $guarded = [];

    public function programScore()
    {
        return $this->hasOne(ClientQuestionScore::class,  'referenceId','clientResponseProgramId')->where('entityType',257);
    }

    public function programs()
    {
        return $this->hasOne(Program::class,  'id','program');
    }
}
