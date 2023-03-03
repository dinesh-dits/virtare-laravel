<?php

namespace App\Models\ToolTip;

use App\Models\Screen\Screen;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'forms';
    use HasFactory;
    protected $guarded = [];


    public function screen()
    {
        return $this->belongsTo(Screen::class,'screenId');
    }

    public function formLable()
    {
        return $this->hasMany(FormLable::class,'refrenceId');
    }
}
