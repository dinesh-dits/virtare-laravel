<?php

namespace App\Models\ToolTip;

use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormLable extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'formLables';
    use HasFactory;
    protected $guarded = [];

    public function types()
    {
        return $this->belongsTo(GlobalCode::class,'type');
    }
}
