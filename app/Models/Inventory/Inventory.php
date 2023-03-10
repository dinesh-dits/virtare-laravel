<?php

namespace App\Models\Inventory;

use App\Models\GlobalCode\GlobalCode;
use App\Models\Patient\PatientInventory;
use App\Models\Device\DeviceModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use SoftDeletes;

    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'inventories';
    use HasFactory;

    protected $guarded = [];

    public function model()
    {
        return $this->hasOne(DeviceModel::class, 'id', 'deviceModelId');
    }

    public function inventory()
    {
        return $this->hasOne(PatientInventory::class, 'inventoryId', 'id');
    }

    public function manufacture()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'manufactureId');
    }

    public function device()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'deviceId');
    }

    public function network()
    {
        return $this->hasOne(GlobalCode::class, 'id', 'networkId');
    }

    public function storeData($id, $data)
    {
        return self::where(['id' => $id])->update($data);
    }
}
