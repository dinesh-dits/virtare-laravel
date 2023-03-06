<?php

namespace App\Models\Provider;

use App\Models\Program\Program;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubLocation extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'subLocations';
    use HasFactory;
    protected $guarded = [];

    protected $parentColumn = 'subLocationParent';

    public function parentName()
    {
        return $this->belongsTo(SubLocation::class, $this->parentColumn);
    }

    public function childName()
    {
        return $this->belongsTo(SubLocation::class, $this->parentColumn)->where('entityType', 'subLocation');
    }

    public function city()
    {
        return $this->HasOne(ProviderLocationCity::class, 'id', 'subLocationParent');
    }

    public function level()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'subLocationLevel');
    }

    public function program()
    {
        return $this->hasOne(Program::class, 'id', 'programId');
    }

    public function providerLocationProgram()
    {
        return $this->hasMany(ProviderLocationProgram::class, 'referenceId', 'id')->where('entityType', 'subLocation');
    }
}
