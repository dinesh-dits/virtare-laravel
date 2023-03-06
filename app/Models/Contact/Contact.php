<?php

namespace App\Models\Contact;

use App\Models\Client\CareTeamMember;
use App\Models\Dashboard\Timezone;
use App\Models\GlobalCode\GlobalCode;
use App\Models\Role\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @method static create(array $data)
 * @method static where(array $array)
 */
class Contact extends Model
{
    use SoftDeletes;

    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = true;
    protected $table = 'contacts';
    use HasFactory;

    protected $guarded = [];


    // Relationship with global code of state
    public function state()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'stateId');
    }

    // Relationship with global code of gender
    public function gender()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'genderId');
    }

    // Relationship with global code of role
    public function role()
    {
        return $this->hasOne(Role::class, 'id', 'roleId');
    }

    // Relationship with global code of specialization
    public function specialization()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'specializationId');
    }

    // Relationship with global code of timezone
    public function timeZone()
    {
        return $this->hasOne(Timezone::class, 'id', 'timeZoneId');
    }

    // Relationship with careTeamMember
    public function careTeamMember()
    {
        return $this->hasMany(CareTeamMember::class, 'contactId', 'id');
    }

    // Insert Contact into database
    public function contactAdd(array $data)
    {
        return self::create($data);
    }

    // Delete Contact from database
    public function dataSoftDelete($id, array $input)
    {
        return self::where(['udid' => $id])->update($input);
    }
}
