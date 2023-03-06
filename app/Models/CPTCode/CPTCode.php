<?php

namespace App\Models\CPTCode;

use App\Models\GlobalCode\GlobalCode;
use App\Models\Provider\Provider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CPTCode extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'cptCodes';
    use HasFactory;
    protected $guarded = [];


    public function provider()
    {
        return $this->belongsTo(Provider::class,'providerId');
    }

    public function service()
    {
        return $this->belongsTo(Service::class,'serviceId');
    }

    public function duration()
    {
        return $this->belongsTo(GlobalCode::class,'durationId');
    }

}
