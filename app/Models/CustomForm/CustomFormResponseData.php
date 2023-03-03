<?php

namespace App\Models\CustomForm;

use Illuminate\Database\Eloquent\Model;

class CustomFormResponseData extends Model
{
    protected $guarded = [];
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    protected $table = 'customFormResponsesData';
}
