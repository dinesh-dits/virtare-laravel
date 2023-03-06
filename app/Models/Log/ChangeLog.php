<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChangeLog extends Model
{
    use SoftDeletes;

    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    public $timestamps = false;
    protected $table = 'changeLogs';
    use HasFactory;

    protected $guarded = [];

    public function makeLog($data): ChangeLog
    {
        return self::create($data);
    }

}
