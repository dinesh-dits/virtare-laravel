<?php

namespace App\Models\AuditLogs;

use App\Models\Flag\Flag;
use App\Models\User\User;
use App\Models\CPTCode\CptCodeActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChangeAuditLog extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'changeAuditLogs';
    use HasFactory;
    protected $guarded = [];

    // Relationship with User Table
    public function user()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    // Relationship with Flag Table
    public function flag()
    {
        return $this->belongsTo(Flag::class, 'flagId');
    }

    // Relationship with CPT Code Activity Table
    public function cptCode()
    {
        return $this->hasOne(CptCodeActivity::class, 'cptCodeId');
    }
}
