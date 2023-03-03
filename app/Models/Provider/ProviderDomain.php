<?php

namespace App\Models\Provider;

use App\Models\Provider\Provider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProviderDomain extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'providerDomains';
    use HasFactory;
    protected $guarded = [];


    public function provider()
    {
        return $this->HasOne(Provider::class, 'providerId');
    }
}
