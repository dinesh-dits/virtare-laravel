<?php

namespace App\Models\Escalation;

use App\Models\GlobalCode\GlobalCode;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EscalationVital extends Model
{
    use SoftDeletes;
    protected $softDelete = true; 
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'escalationVitals';
    use HasFactory;
	protected $guarded = [];
}
