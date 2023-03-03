<?php

namespace App\Models\Module;

use App\Models\Action\Action;
use App\Models\Screen\Screen;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'modules';
    use HasFactory;
    protected $guarded = [];


    public function screens()
    {
        return $this->hasMany(Screen::class,'moduleId');
    }
}
