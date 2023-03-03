<?php

namespace App\Models\Escalation;

use App\Models\User\User;
use App\Models\Escalation\Escalation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EscalationAudit extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'escalationAudits';
    use HasFactory;
    protected $guarded = [];

    public function user()
    {
        return $this->hasOne(User::class,'id', 'createdBy');
    }

    public function escalation()
    {
        return $this->hasOne(Escalation::class,'escalationId', 'escalationId');
    }
}
