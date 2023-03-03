<?php

namespace App\Models\Escalation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EscalationCarePlan extends Model
{
    use SoftDeletes;
    protected $softDelete = true; 
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'escalationCarePlan';
    use HasFactory;
	protected $guarded = [];
}
