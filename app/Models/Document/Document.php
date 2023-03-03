<?php

namespace App\Models\Document;

use App\Models\Tag\Tag;
use App\Models\User\User;
use App\Models\Patient\Patient;
use App\Models\GlobalCode\GlobalCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use SoftDeletes;
    protected $softDelete = true;
    const DELETED_AT = 'deletedAt';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    public $timestamps = false;
	protected $table = 'documents';
    use HasFactory;
	protected $guarded = [];
    
    public function documentType()
    {
        return $this->hasOne(GlobalCode::class,'id','documentTypeId');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'referanceId');
    }

    public function tag()
    {
        return $this->hasMany(Tag::class, 'documentId');
    }
}
