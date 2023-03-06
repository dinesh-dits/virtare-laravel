<?php

namespace App\Models\GlobalCode;

use App\Models\GlobalCode\GlobalCode;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GlobalCodeCategory extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'globalCodeCategories';
    use HasFactory;
    protected $guarded = [];


    public function globalCode()
    {
        if (request()->active) {
            return $this->hasMany(GlobalCode::class, 'globalCodeCategoryId')->where("predefined", 0);
        } else {
            return $this->hasMany(GlobalCode::class, 'globalCodeCategoryId')->where('isActive', '1');
        }
    }
}
