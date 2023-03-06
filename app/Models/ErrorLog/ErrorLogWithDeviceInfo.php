<?php

namespace App\Models\ErrorLog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErrorLogWithDeviceInfo extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'errorLogWithDeviceInfo';
    use HasFactory;
    protected $guarded = [];
}
