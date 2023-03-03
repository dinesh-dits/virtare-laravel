<?php

namespace App\Models\Conversation;

use App\Models\Communication\Communication;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConversationMessage extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'messages';
    use HasFactory;
	protected $guarded = [];

    public function communication(){
        return $this->belongsTo(Communication::class,'communicationId','id');
    }
}
