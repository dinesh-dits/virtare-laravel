<?php

namespace App\Models\CustomForm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\CustomForm\CustomFormFields;

class CustomForms extends Model
{
    use SoftDeletes;
    protected $softDelete = true;  
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
	protected $table = 'customForms';
    protected $guarded = [];

    public function fields()
    {
        return $this->hasMany(CustomFormFields::class,'customFormId');
    }
}
