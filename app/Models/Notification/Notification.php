<?php

namespace App\Models\Notification;

use App\Models\User\User;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{

    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'notifications';
    use HasFactory;
    protected $guarded = [];

    public function notificationType(){
        return $this->belongsTo(GlobalCode::class, 'referenceId');
    }

    public function created_user(){
        return $this->belongsTo(User::class,'created_by');
    }
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
