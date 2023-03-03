<?php

namespace App\Models\Client\Site;

use App\Models\Client\AssignProgram\AssignProgram;
use App\Models\Client\CareTeam;
use App\Models\Client\Client;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Models\Staff\Staff;
/**
 * @method static where(array $array)
 * @method static find(\Illuminate\Http\JsonResponse $siteId)
 */
class Site extends Model
{
    use SoftDeletes;

    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = true;
    protected $table = 'sites';
    use HasFactory;

    protected $guarded = [];


    // Relationship with global code of state
    public function state()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'stateId');
    }

    // Relationship with global code of status
    public function status()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'statusId');
    }

    // Relationship with Care Team Id
    public function care_teams()
    {
        return $this->hasMany(CareTeam::class, 'siteId', 'udid');
    }

    // Relationship with Care Team Id
    public function client()
    {
        return $this->belongsTo(Client::class, 'clientId', 'udid');
    }
    public function head()
    {
        return $this->belongsTo(Staff::class, 'siteHead', 'userId');
    }

    // Relationship with Assign Program of contract
    public function assignProgram()
    {
        return $this->hasMany(AssignProgram::class, 'referenceId', 'udid')->where('entityType', 'Site');
    }

    public function getSiteHead($id)
    {
        $care = CareTeam::leftJoin('care_team_members', 'care_team_members.careTeamId', '=', 'care_teams.id')
            ->leftJoin('users', 'users.id', '=', 'care_team_members.contactId')
            ->leftJoin('staffs', 'staffs.userId', '=', 'users.id')
            ->where(['care_teams.siteId' => $id, 'care_team_members.isHead' => 1])
            ->select(DB::raw("CONCAT('staffs.firstName', 'staffs.lastName') AS name"))
            ->first();
        return ($care->name ?? NUll);
    }

    // Insert Site into database
    public function siteAdd(array $data)
    {
        return self::create($data);
    }

    // Delete Site from database
    public function dataSoftDelete($id, array $input)
    {
        return self::where(['udid' => $id])->update($input);
    }
    public static function updateDetails($id, array $input)
    {
        return self::where(['id' => $id])->update($input);
    }

}
