<?php

namespace App\Models\Provider;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProviderCommunicationMode extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'providerCommunicationModes';
    use HasFactory;
    protected $guarded = [];

    protected $parentColumn = 'parent';

    public function parentName()
    {
        return $this->belongsTo(ProviderLocation::class, $this->parentColumn);
    }
}
