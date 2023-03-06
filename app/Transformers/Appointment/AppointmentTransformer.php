<?php

namespace App\Transformers\Appointment;

use App\Models\Dashboard\Timezone;
use League\Fractal\TransformerAbstract;
use App\Transformers\Dashboard\TimezoneTransformer;


class AppointmentTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        $timezone = "";
        if(isset($data->timezoneId) && !empty($data->timezoneId)){
            $timezone = Timezone::where("id",$data->timezoneId)->first();
        }
        return [
            'key' => $data->id,
            'udid' => $data->udid,
            "notes" => @$data->notes->note,
            "flagName" => @$data->notes->flag->name,
            "flagColor" => @$data->notes->flag->color,
            'startDateTime' => strtotime($data->startDateTime),
            'startTime' =>  strtotime($data->startDateTime),
            'startDate' =>  strtotime($data->startDateTime),
            'patient' =>str_replace("  ", " ", ucfirst($data->patient->lastName) . ',' . ' ' . ucfirst($data->patient->firstName) . ' ' . ucfirst($data->patient->middleName)),
            'patientUdid' => @$data->patient->udid,
            'staffUdid' => @$data->staff->udid,
            'staff' => str_replace("  ", " ", ucfirst($data->staff->lastName) . ',' . ' ' . ucfirst($data->staff->firstName). ' ' . ucfirst($data->staff->middleName)),
            'appointmentType' => $data->appointmentType->name,
            'duration' => $data->duration->name,
            "statusId" => @$data->statusId,
            "cancellationNote" => @$data->cancellationNote,
            'status' => @$data->status->name,
            'conferenceId' => @$data->conferenceId,
            "timezoneId" => isset($data->timezoneId)?$data->timezoneId:"",
            'timezoneUdid' => isset($timezone->udid)?$timezone->udid:"",
            'abbr' => isset($timezone->abbr)?$timezone->abbr:"",
            'timeZone' => isset($timezone->timeZone)?$timezone->timeZone:"",
            'UTCOffset' => isset($timezone->UTCOffset)?$timezone->UTCOffset:"",
            'UTCDSTOffset' => isset($timezone->UTCDSTOffset)?$timezone->UTCDSTOffset:"",
            "timezone" => $data->timezoneId?fractal()->item($timezone)->transformWith(new TimezoneTransformer())->toArray() : ""
        ];
    }
}
