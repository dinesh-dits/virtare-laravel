<?php

namespace App\Transformers\Patient;

use App\Helper;
use App\Models\Patient\PatientFlag;
use League\Fractal\TransformerAbstract;
use App\Transformers\User\UserTransformer;
use App\Transformers\Patient\PatientStaffTransformer;
use App\Transformers\Patient\PatientVitalTransformer;
use App\Transformers\Patient\PatientFamilyMemberTransformer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Models\Patient\PatientProvider;

class PatientTransformer extends TransformerAbstract
{
    protected $showData;

    public function __construct($showData = true)
    {
        $this->showData = $showData;
    }
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function getCareteam($patientId)
    {
        $provider =   PatientProvider::with('careteam')->where('patientId', $patientId)->first();
        $data = array();
        if (isset($provider->providerId) && !empty($provider->providerId)) {
            $data['name'] = (!empty($provider->careteam)) ? $provider->careteam->name : '';
            $data['udid'] = (!empty($provider->careteam)) ? $provider->careteam->udid : '';
        }
        return $data;
    }
    public function transform($data): array
    {
        $flag = PatientFlag::with('flags');
        if ((!empty(request()->input('fromDate')) && !empty(request()->input('toDate')))) {
            $fromDateStr =  Helper::date(request()->input('fromDate'));
            $flag->where(function ($query) use ($fromDateStr) {
                $query->where('patientFlags.createdAt', '>=', $fromDateStr)->orWhereNull('patientFlags.deletedAt');
            })->withTrashed();
        }
        if (
            request()->filter == 'Escalation' || request()->filter == 'Critical' || request()->filter == 'Moderate' || request()->filter == 'WNL'
            || request()->filter == 'Watchlist' || request()->filter == 'Trending' || request()->filter == 'Message' || request()->filter == 'Communication' || request()->filter == 'Work Status'
        ) {
            $flag->whereHas('flag', function ($query) {
                $query->where('name', request()->filter);
            })->withTrashed();
        } else {
            $flag->leftJoin('flags', 'flags.id', '=', 'patientFlags.flagId')->whereIn('flags.id', [7, 8, 9]);
        }

        $flag = $flag->where('patientFlags.patientId', $data->id)->orderBy('patientFlags.id', 'DESC')->first();

        if (empty($flag)) {
            $flag = '';
        }


        $vitals = $data->vitals_list($data->id);
        if ($data->family) {
            if (!$data->family->relationId || !$data->genderId) {
                $relation = '';
            } else {
                $relation = Helper::relation($data->family->relationId, $data->genderId);
            }
        } else {
            $relation = '';
        }
        //print_r($data);
        //$detail = fractal()->item($data)->transformWith(new PatientDetailTransformer())->toArray();
        $detail = fractal()->item($data)->transformWith(new PatientBasicDetailTransformer($this->showData))->toArray();

        $otherDetail = [
            'patientFullName' => str_replace("  ", " ", ucfirst($data->lastName) . ',' . ' ' . ucfirst($data->firstName) . ' ' . ucfirst($data->middleName)),
            'lastReadingDate' => $data ? strtotime(Helper::lastReading($data->id)) : '',
            'lastMessageSent' => $data ? Helper::lastMessage($data->id) : '',
            'isApp' => (isset($data->isApp)) ? $data->isApp : 0,
            'flagName' => (!empty(@$flag)) ? @$flag->flag->name : '',
            'flagColor' => (!empty(@$flag)) ? @$flag->flag->color : '',
            'flagTmeStamp' => (!empty($flag)) ? strtotime($flag->createdAt) : '',
            'insuranceId' => (!empty($data->insurance->insuranceNumber)) ? $data->insurance->insuranceNumber : '',
            'insuranceName' => (!empty($data->insurance->insuranceName->name)) ? $data->insurance->insuranceName->name : '',
            'insuranceExpirationDate' => (!empty($data->insurance->expirationDate)) ? strtotime($data->insurance->expirationDate) : '',
            'relationId' => (!empty($relation['relationId'])) ? $relation['relationId'] : '',
            'relation' => (!empty($relation['relation'])) ? $relation['relation'] : '',
            'user' => $this->showData && $data->user ? fractal()->item($data->user)->transformWith(new UserTransformer(false))->toArray() : [],
            'profile_photo' => (!empty($data->user->profilePhoto)) && (!is_null($data->user->profilePhoto)) ? Storage::disk('s3')->temporaryUrl($data->user->profilePhoto, Carbon::now()->addDays(5)) : "",
            'patientCoordinator' => $this->showData && $data->staffPatient ? fractal()->item($data->staffPatient)->transformWith(new PatientStaffTransformer())->toArray() : [],
            'patientFamilyMember' => $this->showData && $data->family ? fractal()->item($data->family)->transformWith(new PatientFamilyMemberTransformer())->toArray() : [],
            'emergencyContact' => $this->showData && $data->emergency ? fractal()->item($data->emergency)->transformWith(new PatientFamilyMemberTransformer())->toArray() : [],
            'patientFlags' => (!empty(@$flag)) ? @$flag->flag->name : '',
            'patientVitals' => $vitals ? fractal()->collection($vitals)->transformWith(new PatientVitalTransformer())->toArray() : [],
            'provider' => $this->getCareteam($data->id)
        ];
        return array_merge($detail['data'], $otherDetail);
    }
}
