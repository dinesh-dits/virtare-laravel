<?php

namespace App\Models\Client;

use App\Models\Client\AssignProgram\AssignProgram;
use App\Models\Contact\Contact;
use App\Models\Staff\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static create(array $data)
 * @method static where(array $array)
 * @method static whereIn(string $string, $careTeams)
 * @method static insert(array $data)
 */
class CareTeamMember extends Model
{
    use SoftDeletes;

    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = true;
    protected $guarded = [];
    protected $table = 'care_team_members';

    // Relationship with Assign Program of contract
    public function assignProgram()
    {
        return $this->hasMany(AssignProgram::class, 'referenceId', 'udid')
            ->where('entityType', 'CareTeamMember');
    }

    // Relationship with CareTeam id
    public function careTeam()
    {
        return $this->belongsTo(CareTeam::class, 'careTeamId', 'udid');
    }

    // Relationship with CareTeam id
    public function contact()
    {
        return $this->belongsTo(Staff::class, 'contactId', 'userId');
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

    public function dataSoftDeleteByCareTeamId($id, array $input)
    {
        return self::where(['careTeamId' => $id])->update($input);
    }

    public function insertData(array $data)
    {
        return self::insert($data);
    }
}
