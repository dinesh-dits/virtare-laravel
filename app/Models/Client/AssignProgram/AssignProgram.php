<?php

namespace App\Models\Client\AssignProgram;

use App\Models\Client\CareTeam;
use App\Models\Client\CareTeamMember;
use App\Models\Client\Client;
use App\Models\Client\Site\Site;
use App\Models\Program\Program;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static create(array $programData)
 * @method static where(array $array)
 * @method static insert(array $programData)
 */
class AssignProgram extends Model
{
    use SoftDeletes;

    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = true;
    protected $table = 'assignPrograms';
    use HasFactory;

    protected $guarded = [];


    // Relationship with global code of state
    public function program()
    {
        return $this->hasOne(Program::class, 'id', 'programId');
    }

    // Relationship with global code of status
    public function client()
    {
        return $this->hasOne(Client::class, 'udid', 'referenceId')
            ->where('entityType', 'Client');
    }

    // Relationship with global code of status
    public function site()
    {
        return $this->hasOne(Site::class, 'udid', 'referenceId')
            ->where('entityType', 'Site');
    }

    // Relationship with global code of CareTeam
    public function care_team()
    {
        return $this->hasOne(CareTeam::class, 'udid', 'referenceId')
            ->where('entityType', 'CareTeam');
    }

    // Relationship with global code of CareTeamMember
    public function care_team_members()
    {
        return $this->hasOne(CareTeamMember::class, 'udid', 'referenceId')
            ->where('entityType', 'CareTeamMember');
    }

    public function addData(array $programData)
    {
        return self::insert($programData);
    }

}
