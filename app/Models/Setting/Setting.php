<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
    protected $table = 'settings';
    use HasFactory;
    protected $guarded = [];


    public static function getValue($key){
        if($key){
            $data = Setting::where('key',$key)->where('isActive',1)->first();
            return isset($data->value)?$data->value:0;
        }else{
            return false;
        }
    }
}
