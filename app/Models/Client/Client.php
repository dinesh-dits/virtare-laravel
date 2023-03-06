<?php

namespace App\Models\Client;

use App\Models\Staff\Staff;
use App\Models\Contact\Contact;
use App\Models\Client\Site\Site;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Client\AssignProgram\AssignProgram;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @method static create(array $data)
 * @method static where(array $array)
 * @method static find(\Illuminate\Http\JsonResponse $clientId)
 */
class Client extends Model
{
    use SoftDeletes;

    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = true;
    protected $table = 'clients';
    use HasFactory;

    protected $guarded = [];


    // Relationship with global code of state
    public function state()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'stateId');
    }

    // Relationship with Contact
    public function contact()
    {
        return $this->hasOne(Contact::class, 'referenceId', 'udid');
    }

    // Relationship with Staff
    public function staff()
    {
        return $this->hasOne(Staff::class, 'clientId', 'udid')->where('isContact',1);
    }

    // Relationship with global code of status
    public function status()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'statusId');
    }

    // Relationship with global code of contract
    public function contractType()
    {
        return $this->hasOne(GlobalCode::class,'id','contractTypeId');
    }

    // Relationship with Assign Program of contract
    public function assignProgram()
    {
        return $this->hasMany(AssignProgram::class, 'referenceId', 'udid')->where('entityType', 'Client');
    }
    // Relationship with Assign Program of contract
    public function sites()
    {
        return $this->hasMany(Site::class, 'clientId', 'udid');
    }
    public function teams()
    {
        return $this->hasMany(CareTeam::class, 'clientId', 'udid');
    }

    public function clientAdd(array $data)
    {
        return self::create($data);
    }

    public function dataSoftDelete($id, array $input)
    {
        return self::where(['udid' => $id])->update($input);
    }

    public function updateClient($id, array $client)
    {
        return self::where(['udid' => $id])->update($client);
    }


}
