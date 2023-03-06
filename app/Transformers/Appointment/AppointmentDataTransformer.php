<?php

namespace App\Transformers\Appointment;

use App\Models\Dashboard\Timezone;
use League\Fractal\TransformerAbstract;
use App\Transformers\Staff\StaffTransformer;
use App\Transformers\Patient\PatientTransformer;
use App\Transformers\Staff\StaffDetailTransformer;
use App\Transformers\Dashboard\TimezoneTransformer;
use App\Transformers\Patient\PatientDetailTransformer;

class AppointmentDataTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        if (empty($data)) {
            return [];
        }

        $timezone = "";
        if(isset($data->timezoneId) && !empty($data->timezoneId)){
            $timezone = Timezone::where("id",$data->timezoneId)->first();
        }

        if($data->appoinmentStatus == 1){
            $appoinmentStatus = "Attended";
        }elseif($data->appoinmentStatus == 2){
            $appoinmentStatus = "Not Attended";
        }else{
            $appoinmentStatus = null;
        }
        
        return [
            'id' => $data->udid,
            'duration' => $data->duration->name,
            "date" => strtotime(@$data->startDateTime),
            "notes" => @$data->notes->note,
            "flagName" => @$data->notes->flag->name,
            "flagColor" => @$data->notes->flag->color,
            'conferenceId' => @$data->conferenceId,
            'time' => strtotime(@$data->startDateTime),
            "statusId" => @$data->statusId,
            "cancellationNote" => @$data->cancellationNote,
            'status' => @$data->status->name,
            'appoinmentStatus' => $appoinmentStatus,
            'appointmentType' => @$data->appointmentType->name,
            "patient" => str_replace("  ", " ", ucfirst($data->patient->lastName) . ',' . ' ' . ucfirst($data->patient->firstName) . ' ' . ucfirst($data->patient->middleName)),
            "patientUdid" => @$data->patient->udid,
            "patientDetailed" => @$data->patient != null ? fractal()->item($data->patient)->transformWith(new PatientDetailTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : array(),
            "staff" => @$data->staff != null ? fractal()->item($data->staff)->transformWith(new StaffDetailTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : array(),
            "timezoneId" =>$data->timezoneId ?$data->timezoneId:"",
            "timezone" => $data->timezoneId?fractal()->item($timezone)->transformWith(new TimezoneTransformer())->toArray() : ""
        ];
    }
}
