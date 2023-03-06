<?php

namespace App\Models\Widget;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WidgetAccess extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'widgetAccesses';
    use HasFactory;
    protected $guarded = [];

    public function widget()
    {
        return $this->belongsTo(Widget::class,'widgetId');
    }
}
