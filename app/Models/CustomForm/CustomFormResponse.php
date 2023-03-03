<?php

namespace App\Models\CustomForm;

use Illuminate\Database\Eloquent\Model;
use App\Models\CustomForm\CustomFormResponseData;
use App\Models\User\User;
class CustomFormResponse extends Model
{
    protected $guarded = [];
    protected $table = 'customFormResponses';
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    public function responses()
    {        
        return $this->hasMany(CustomFormResponseData::class,'responseId');
    }

    public function user(){
        return $this->belongsTo(User::class,'submittedBy');
    }
}
