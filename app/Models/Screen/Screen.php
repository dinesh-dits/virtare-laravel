<?php

namespace App\Models\Screen;

use App\Models\Action\Action;
use App\Models\Module\Module;
use App\Models\ToolTip\Form;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Screen extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'screens';
    use HasFactory;
    protected $guarded = [];

    public function action()
    {
        return $this->hasMany(Action::class, 'screenId');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'moduleId');
    }

    public function form()
    {
        return $this->hasMany(Form::class,'screenId');
    }
}
