<?php

namespace App\Models;
use App\Models\User\User;
use App\Models\Escalation\Escalation;
use Illuminate\Database\Eloquent\Model;

class EmailStats extends Model
{
    public $fillable = ['email','message_id','user_id','type','status','sent_on'];

    protected $table = 'email_stats';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function escalation()
    {
        return $this->hasOne(Escalation::class, 'escalationId','refrence_id');
    }
}
