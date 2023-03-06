<?php

namespace App\Models\Domain;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Log\ChangeLog;
class Domain extends Model
{
	use SoftDeletes;
    use HasFactory;
	protected $guarded = [];

	/**
	* Method for add hook for change in database
	*/

	public static function boot() {
  
        parent::boot();
  
        static::created(function($item) {  
            $changeLog = new ChangeLog;

            $changeLog->table=$item->getTable();
            $changeLog->table_id=$item->id;
            $changeLog->value=json_encode($item);
            $changeLog->type='created';
            $changeLog->ip=request()->ip();
            $changeLog->is_active=1;
            $changeLog->created_by=1;
            $changeLog->save();

        });

        static::updated(function($item) {  
            $changeLog = new ChangeLog;

            $changeLog->table=$item->getTable();
            $changeLog->table_id=$item->id;
            $changeLog->value=json_encode($item);
            $changeLog->type='updated';
            $changeLog->ip=request()->ip();
            $changeLog->is_active=1;
            $changeLog->created_by=1;
            $changeLog->save();
        });

        static::deleted(function($item) {            
            $changeLog = new ChangeLog;

            $changeLog->table=$item->getTable();
            $changeLog->table_id=$item->id;
            $changeLog->value=json_encode($item);
            $changeLog->type='deleted';
            $changeLog->ip=request()->ip();
            $changeLog->is_active=1;
            $changeLog->created_by=1;
            $changeLog->save();
        });
        
        static::restored(function($item) {            
            $changeLog = new ChangeLog;

            $changeLog->table=$item->getTable();
            $changeLog->table_id=$item->id;
            $changeLog->value=json_encode($item);
            $changeLog->type='restored';
            $changeLog->ip=request()->ip();
            $changeLog->is_active=1;
            $changeLog->created_by=1;
            $changeLog->save(); 
        });
    }
}
