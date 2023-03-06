<?php

namespace App\Models\Conversation;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Conversation\ConversationMessage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conversation extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'conversations';
    use HasFactory;
	protected $guarded = [];


    public function sender()
	{
		return $this->belongsTo(User::class,'senderId','id');
	}

    public function receiver()
	{
		return $this->belongsTo(User::class,'receiverId','id');
	}

    public function conversationMessages()
    {
        return $this->hasMany(ConversationMessage::class,'conversationId');
    }
}
