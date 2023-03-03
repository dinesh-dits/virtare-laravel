<?php

namespace App\Models\Staff\StaffProvider;

use App\Models\Provider\Provider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffProvider extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    public $timestamps = false;
    protected $table = 'staffProviders';
    use HasFactory;
    protected $guarded = [];

    public function providers()
    {
        return  $this->belongsTo(Provider::class,'providerId');

    }
}
