<?php

namespace App\Models\Widget;

use App\Models\GlobalCode\GlobalCode;
use App\Models\WidgetModule\WidgetModule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Widget extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'widgets';
    use HasFactory;
    protected $guarded = [];


    public function widgetType()
    {
        return $this->belongsTo(GlobalCode::class, 'widgetTypeId');
    }

    public function typeWidget()
    {
        return $this->belongsTo(GlobalCode::class, 'type');
    }

    public function widgets()
    {
        return $this->hasMany(WidgetAccess::class,'widgetId');
    }  
}
