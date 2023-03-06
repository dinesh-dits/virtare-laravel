<?php

namespace App\Models\Role;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    use HasFactory;
	protected $guarded = [];

    const DELETED_AT = 'deletedAt';
}
