<?php

namespace App\Models\Communication;

use App\Models\User\User;
use App\Models\Staff\Staff;
use App\Models\Patient\Patient;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Conversation\ConversationMessage;
use App\Models\Communication\CommunicationMessage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Communication extends Model
{

    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'communications';
    use HasFactory;
    protected $guarded = [];


    // Relationship with Communication Message Table
    public function communicationMessage()
    {
        return $this->hasMany(CommunicationMessage::class, 'communicationId', 'id');
    }

    // Relationship with Staff Table
    public function staff()
    {
        return $this->hasOne(Staff::class, 'id', 'from');
    }

    // Relationship with Patient Table
    public function patient()
    {
        return $this->hasOne(Patient::class, 'id', 'referenceId');
    }

    // Relationship with Staff Table
    public function staffs()
    {
        return $this->hasOne(Staff::class, 'id', 'referenceId');
    }

    // Relationship with Global Code Table for Message Category
    public function globalCode()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'messageCategoryId');
    }

    // Relationship with Global Code for priority
    public function priority()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'priorityId');
    }

    // Relationship with Global Code for Message Type
    public function type()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'messageTypeId');
    }

    // Relationship with User Table for From (Sender)
    public function sender()
    {
        return $this->belongsTo(User::class, 'from', 'id');
    }

    // Relationship with User Table for reference (Receiver)
    public function receiver()
    {
        return $this->belongsTo(User::class, 'referenceId', 'id');
    }

    // Relationship with User Table for reference (Receiver)
    public function conversationMessages()
    {
        return $this->hasMany(ConversationMessage::class, 'communicationId');
    }
    

    public function scopeSms($query)
    {
        //return $query->whereHas('conversationMessages');
       return $query->whereRaw('((messageTypeId = 102 AND exists (select * from `messages` where `communications`.`id` = `messages`.`communicationId` and `messages`.`deletedAt` is null)) OR messageTypeId != 102)');
    }

    // Relationship with Communication Call Record Table
    public function communicationCallRecord()
    {
        return $this->hasMany(CommunicationCallRecord::class, 'communicationId');
    }
}
