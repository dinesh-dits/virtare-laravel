<?php

namespace App\Models\Contact;

use App\Models\GlobalCode\GlobalCode;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequestCall extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'requestCalls';
    use HasFactory;
	protected $guarded = [];
    protected $casts = [
        'contactTiming' => 'array'
    ];

    public function contactTime()
    {
        return $this->belongsTo(GlobalCode::class,'contactTimeId');
    }

    public function messageStatus()
    {
        return $this->belongsTo(GlobalCode::class,'messageStatusId');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'userId');
    }
}
