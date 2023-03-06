<?php

namespace App\Models\Dashboard;

use App\Models\GlobalCode\GlobalCode;
use App\Models\Role\Role;
use App\Models\Staff\Staff;
use App\Models\Widget\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DashboardWidgetByRole extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'dashboardWidgetByRoles';
    use HasFactory;
    protected $guarded = [];

    public function widgetType()
    {
        return $this->belongsTo(GlobalCode::class, 'widgetTypeId');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'roleId','id');
    }   

    public function widget()
    {
        return $this->belongsTo(Widget::class, 'widgetId');
    }

    
}
