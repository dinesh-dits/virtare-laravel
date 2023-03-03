<?php

namespace App\Models\CustomForm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomFields extends Model
{
    use SoftDeletes;
    protected $softDelete = true;    
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
	protected $table = 'customFields';
}
