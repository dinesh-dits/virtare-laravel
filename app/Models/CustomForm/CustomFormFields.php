<?php

namespace App\Models\CustomForm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomFormFields extends Model
{
   
	protected $guarded = [];
	const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
	protected $table = 'customFormFields';
    
}
