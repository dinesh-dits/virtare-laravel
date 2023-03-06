<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLogs extends Model
{
    protected $table = 'loginLogs';
    protected $guarded = [];
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    
}
