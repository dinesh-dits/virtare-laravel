<?php

namespace App\Models\BugReport;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BugReport\BugReportDocument;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Screen\Screen;
use Illuminate\Database\Eloquent\SoftDeletes;

class BugReport extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'bugReports';
    use HasFactory;
	protected $guarded = [];


    // Relationship with Bug Report Document Table
    public function bugDocument(){
        return $this->hasMany(BugReportDocument::class, 'bugReportId', 'bugReportId')->where("isActive",1);
    }

    // Relationship with Screen Table
    public function bugScreen(){
        return $this->belongsTo(Screen::class,'screenId');
    }

    // Relationship with Global Code for Category
    public function bugCategory()
    {
        return $this->belongsTo(GlobalCode::class,'categoryId');
    }
    // Relationship with Global Code for Priority
    public function bugPriority()
    {
        return $this->belongsTo(GlobalCode::class,'priority');
    }
}


