<?php

namespace App\Transformers\Staff;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use League\Fractal\TransformerAbstract;
use App\Transformers\Role\RoleTransformer;
use App\Transformers\Group\GroupTransformer;
use App\Transformers\Role\UserRoleTransformer;
use App\Transformers\Staff\StaffNoteTransformer;
use App\Transformers\Staff\StaffDetailBasicTransformer;
use App\Transformers\Staff\StaffAvailabilityTransformer;

class StaffTransformer extends TransformerAbstract
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
        $detail = fractal()->item($data)->transformWith(new StaffDetailBasicTransformer())->toArray();
        //   print_r($detail); die;
        if (auth()->user()) {
            $otherDetail = [
                'sipId' => "UR" . $data->userId,
                'title' => ucfirst($data->firstName),
                'summary' => $data->summary ? $data->summary : '',
                'expertise' => $data->expertise ? $data->expertise->name : '',
                'isPrimary' => (!empty($data->patientStaff[0])) ? $data->patientStaff[0]->isPrimary : 0,
                // 'type' => (!empty($data->roles->roles)) ? $data->roles->roles : $data->roles,
                'uuid' => $data->udid,
                'username' => $data->email,
                'email' => @$data->user->email,
                'first_login' => 0,
                'nickname' => $data->nickname ? ucfirst($data->nickname) : '',
                'gender' => isset($data->gender) ? $data->gender->name : '',
                'genderId' => isset($data->gender) ? $data->gender->id : '',
                'phoneNumber' => $data->phoneNumber,
                'profile_photo' => (!empty($data->user->profilePhoto)) && (!is_null($data->user->profilePhoto)) ? Storage::disk('s3')->temporaryUrl($data->user->profilePhoto, Carbon::now()->addDays(5)) : "",
                'profilePhoto' => (!empty($data->user->profilePhoto)) && (!is_null($data->user->profilePhoto)) ? Storage::disk('s3')->temporaryUrl($data->user->profilePhoto, Carbon::now()->addDays(5)) : "",
                'network' => isset($data->network) ? $data->network->name : '',
                'networkId' => isset($data->network) ? $data->network->id : '',
                'specialization' => isset($data->specialization) ? $data->specialization->name : '',
                'specializationId' => isset($data->specialization) ? $data->specialization->id : '',
                'createdAt' => strtotime($data->createdAt),
                'isActive' => $data->isActive ? true : false,
                'designation' => (!empty($data->designation)) ? $data->designation->name : '',
                'designationId' => (!empty($data->designation)) ? $data->designation->id : '',
                'role' => (!empty($data->userRole)) ? fractal()->collection($data->userRole)->transformWith(new UserRoleTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : new \stdClass(),
                'vital' => (!empty($data->userFamilyAuthorization)) ? $data->userFamilyAuthorization->vital == 0 ? 0 : $data->userFamilyAuthorization->vital : '',
                'message' => (!empty($data->userFamilyAuthorization)) ? $data->userFamilyAuthorization->message == 0 ? 0 : $data->userFamilyAuthorization->message : '',
                'availability' => $data->availability ? fractal()->collection($data->availability)->transformWith(new StaffAvailabilityTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : array(),
                'notes' => $this->showData && (!empty($data->appointment)) ? fractal()->collection($data->appointment)->transformWith(new StaffNoteTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : array(),
                'groupId' => (!empty($data->group)) ? (!empty($data->group->group)) ? $data->group->group->udid : '' : '',
                'groupName' => (!empty($data->group)) ? (!empty($data->group->group)) ? $data->group->group->group : '' : '',
                'organisation' => $data->organisation,
                'location' => $data->location,
            ];
        } else {
            $otherDetail = [
                'sipId' => "UR" . $data->userId,
                'title' => $data->firstName,
                'summary' => $data->summary ? $data->summary : '',
                'expertise' => $data->expertise ? $data->expertise->name : '',
                'type' => (!empty($data->roles->roles)) ? $data->roles->roles : $data->roles,
                'uuid' => $data->udid,
                'username' => $data->email,
                'email' => @$data->user->email,
                'first_login' => 0,
                'nickname' => $data->nickname ? $data->nickname : '',
                'role' => $this->showData && (!empty($data->roles->roles)) ? fractal()->item($data->roles)->transformWith(new RoleTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : new \stdClass(),
                'roleCustom' => (!empty($data->userRole)) ? fractal()->collection($data->userRole)->transformWith(new UserRoleTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : new \stdClass(),

            ];
        }
        return array_merge($detail['data'], $otherDetail);
    }
}
