<?php

namespace App\Models\Client;

use App\Models\Client\AssignProgram\AssignProgram;
use App\Models\Client\Site\Site;
use App\Models\Staff\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * @method static create(array $data)
 * @method static where(array $array)
 * @method static whereNotNull(string $string)
 * @method static find($careTeamId)
 */
class CareTeam extends Model
{
    use SoftDeletes;

    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = true;
    protected $guarded = [];
    protected $table = 'care_teams';

    // Relationship with Assign Program of contract
    public function assignProgram()
    {
        return $this->hasMany(AssignProgram::class, 'referenceId', 'udid')
            ->where('entityType', 'CareTeam');
    }

    // Relationship with site id
    public function site()
    {
        return $this->belongsTo(Site::class, 'siteId', 'udid');
    }

    // Relationship with careTeamId
    public function careTeamMember()
    {
        return $this->hasMany(CareTeamMember::class, 'careTeamId', 'udid');
    }

    public function head()
    {
        return $this->hasOne(CareTeamMember::class, 'careTeamId', 'udid')
            ->where(['isHead' => 1, 'isActive' => 1]);
    }

    public function headName($id)
    {
        return Staff::where(['userId' => $id])
            ->select(DB::raw("CONCAT(upper(left(trim(`staffs`.`lastName`),1)),substring(trim(`staffs`.`lastName`),2), ', ', upper(left(trim(`staffs`.`firstName`),1)),substring(trim(`staffs`.`firstName`),2)) as name"), 'udid as teamHeadId')
            ->first();
    }

    // Insert care_team into database
    public function storeData(array $data)
    {
        return self::create($data);
    }

    public function dataSoftDelete($id, array $input)
    {
        return self::where(['udid' => $id])->update($input);
    }

    public function updateCareTeam($id, array $reqData)
    {
        return self::where(['udid' => $id])->update($reqData);
    }
}
