<?php

namespace App\Models\WidgetModule;

use App\Models\Widget\Widget;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\Widget\WidgetAccess;

class WidgetModule extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'widgetModules';
    use HasFactory;
    protected $guarded = [];


    public function widgets()
    {
        return $this->hasMany(Widget::class,'widgetModuleId');
    }

    public function dashboardWidgets()
    {
        return $this->hasMany(Widget::class,'widgetModuleId');
    }  

    public function widgetAccess(){
        return $this->hasManyThrough(
            Widget::class,  
            WidgetAccess::class,  
            'widgetId', // Foreign key on the types table...  
            'widgetModuleId', // Foreign key on the items table...            
            'id', // Local key on the users table...        
            'id' // Local key on the categories table...
           
     );
    }
}
