<?php

namespace App\Models\Action;

use App\Models\Screen\Screen;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Action extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'actions';
    use HasFactory;
    protected $guarded = [];

    // Relationship With Screen Table
    public function screen(){
        return $this->belongsTo(Screen::class,'screenId');
    }
}
