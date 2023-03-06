<?php

namespace App\Models\Group;

use App\Models\Group\Group;
use App\Models\Widget\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupWidget extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'groupWidgets';
    use HasFactory;
    protected $guarded = [];

    public function widget()
    {
        return $this->belongsTo(Widget::class,'widgetId');
    }
}
