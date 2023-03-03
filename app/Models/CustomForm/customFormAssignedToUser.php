<?php

namespace App\Models\CustomForm;

use Illuminate\Database\Eloquent\Model;
use App\Models\CustomForm\CustomForms;
use App\Models\User\User;
use App\Models\CustomForm\CustomFormResponse;
use Illuminate\Database\Eloquent\SoftDeletes;

class customFormAssignedToUser extends Model
{
    protected $guarded = [];
	protected $table = 'customFormAssignedToUsers';
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    use SoftDeletes;
    protected $softDelete = true;  

    public function customform()
    {
        return $this->belongsTo(CustomForms::class,'customFormId','id');
    }
    public function user(){
        return $this->belongsTo(User::class,'userId');
    }
    public function response(){
        return $this->hasOne(CustomFormResponse::class,'assignedId','id');
    }
}
