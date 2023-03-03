<?php

namespace App\Models\ScreenAction;

use App\Models\Action\Action;
use App\Models\Screen\Screen;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScreenAction extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'screenActions';
    use HasFactory;
    protected $guarded = [];


    public function action()
    {
        return $this->belongsTo(Action::class, 'actionId');
    }    

    public function user()
    {
        return $this->belongsTo(User::class,'userId');
    }
}
