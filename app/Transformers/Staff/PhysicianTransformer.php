<?php

namespace App\Transformers\Staff;

use League\Fractal\TransformerAbstract;
use App\Transformers\Role\RoleTransformer;
use Illuminate\Support\Facades\URL;

class PhysicianTransformer extends TransformerAbstract
{


    protected $showData;

	public function __construct($showData = true)
	{
		$this->showData = $showData;
	}
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
    ];
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        //
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($data): array
    {
        return [
            'id'=>$data->udid,
			'patientId'=>$data->patientId,
            "user_id"=>$data->userId,
            'sipId' => "UR".$data->userId,
            'uuid'=>$data->udid,
            'fullName'=>$data->name,
            'designation'=>$data->designation->name,
            'username'=>$data->user->email,
            'email'=>$data->user->email,
            "first_login"=> 0,
            "nickname"=> $data->nickname ? $data->nickname : '',
            'age'=>$data->getAgeAttribute($data->date_of_birth) ? $data->getAgeAttribute($data->date_of_birth) : '',
            'date_of_birth' => date("m/d/Y",strtotime($data->dob)) ? date("m/d/Y",strtotime($data->dob)) :'',
			'height' => $data->height ? $data->height :'',
            'phoneNumber'=>$data->phoneNumber,
			'house_no' => $data->house_no,
			'profile_photo' => (!empty($data->user->profilePhoto))&&(!is_null($data->user->profilePhoto)) ? URL::to('/').'/'.$data->user->profilePhoto : "",
            'profilePhoto' => (!empty($data->user->profilePhoto))&&(!is_null($data->user->profilePhoto)) ? URL::to('/').'/'.$data->user->profilePhoto : "",
			'city' => $data->city,
			'state' => $data->state,
			'country' => $data->country,
            'fax'=>$data->fax,
            'sameAsReferal'=>$data->sameAsReferal,
			'zip_code' => $data->zip_code,
            'isPrimary'=>$data->isPrimary,
            'role' => $this->showData ? fractal()->item($data->user->roles)->transformWith(new RoleTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : new \stdClass(),
            'vital'=>(!empty($data->userFamilyAuthorization)) ? $data->userFamilyAuthorization->vital==0 ? 0 :$data->userFamilyAuthorization->vital : '',
		    'message'=>(!empty($data->userFamilyAuthorization))? $data->userFamilyAuthorization->message==0 ? 0 :$data->userFamilyAuthorization->message : '',
            'availability' => $data->availability ? $data->availability:array(),
            'notes' =>$data->notes ? $data->notes:array(),
            'today_appointment' => $data->appointment ? $data->appointment:array(),
		];
    }
}
