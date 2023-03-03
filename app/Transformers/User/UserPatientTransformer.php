<?php

namespace App\Transformers\User;

use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use League\Fractal\TransformerAbstract;
use App\Transformers\Role\RoleTransformer;
use App\Transformers\Staff\StaffTransformer;
use App\Transformers\Patient\PatientTransformer;
use App\Transformers\Patient\PatientFamilyMemberTransformer;

class UserPatientTransformer extends TransformerAbstract
{


    protected $showData;

    public function __construct($showData = true)
    {
        $this->showData = $showData;
    }

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];


    public function transform($user): array
    {
        //print_r($user); die;
        return [
            'id' => $user->id,
            'uuid' => $user->udid,
            'sipId' => "UR" . $user->id,
            // 'initials' => ucfirst($user->patient->initials()),
            'name' => ucfirst($user->patient->firstName) . ' ' . ucfirst($user->patient->lastName),
            'firstName' => ucfirst($user->patient->firstName),
            'lastName' => ucfirst($user->patient->lastName),
            'middleName' => ($user->patient->middleName) ? ucfirst($user->patient->middleName) : '',
            'username' => $user->email,
            'email' => $user->email,
            'nickname' => $user->patient->nickName,
            'gender' => @$user->patient->gender->name,
            'age' => @$user->getAgeAttribute($user->patient->dob),
            'dateOfBirth' => date("m/d/Y", strtotime($user->patient->dob)),
            'height' => @$user->patient->height,
            'contactNo' => @$user->patient->phoneNumber,
            'phoneNumber' => @$user->patient->phoneNumber,
            'isDeviceAdded' => @$user->patient->isDeviceAdded,
            'house_no' => @$user->patient->appartment,
            'profile_photo' => (!empty($user->profilePhoto)) && (!is_null($user->profilePhoto)) ? Storage::disk('s3')->temporaryUrl($user->profilePhoto, Carbon::now()->addDays(5)) : "",
            'profilePhoto' => (!empty($user->profilePhoto)) && (!is_null($user->profilePhoto)) ? Storage::disk('s3')->temporaryUrl($user->profilePhoto, Carbon::now()->addDays(5)) : "",
            'city' => @$user->patient->city,
            'state' => @$user->patient->state->name,
            'country' => @$user->patient->country->name,
            'zipCode' => $user->patient->zipCode,
            'deviceType' => $user->deviceType,
            'deviceToken' => $user->deviceToken,
            'firstLogin' => $user->firstLogin,
            'role' => $user->roleId,
            'roleId' => $this->showData ? fractal()->item($user->roles)->transformWith(new RoleTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : new \stdClass(),
            'vital' => (!empty($user->userFamilyAuthorization)) ? $user->userFamilyAuthorization->vital == 0 ? 0 : $user->userFamilyAuthorization->vital : '',
            'message' => (!empty($user->userFamilyAuthorization)) ? $user->userFamilyAuthorization->message == 0 ? 0 : $user->userFamilyAuthorization->message : '',
            'emailverified' => $user->emailVerify ? true : false,
            'patient' => $this->showData ? fractal()->item($user->patient)->transformWith(new PatientTransformer(false))->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : new \stdClass(),
            'staff' => $user->staff ? fractal()->item($user->staff)->transformWith(new StaffTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : new \stdClass(),
            'famailyMember' => $user->familyMember ? fractal()->item($user->familyMember)->transformWith(new PatientFamilyMemberTransformer(false))->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : new \stdClass(),
        ];
    }
}
