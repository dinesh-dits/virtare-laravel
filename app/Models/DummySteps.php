<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CustomForm\customFormAssignedToUser;
use App\Models\User\User;
use App\Models\CustomForm\CustomFormResponse;
use Illuminate\Database\Eloquent\SoftDeletes;

class DummySteps extends Model
{
    protected $guarded = [];
	protected $table = 'DummySteps';
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    use SoftDeletes;
    protected $softDelete = true;  

    public function assignedForms()
    {
        return $this->belongsTo(customFormAssignedToUser::class,'customFormAssignedId','id');
    }
    
   
}