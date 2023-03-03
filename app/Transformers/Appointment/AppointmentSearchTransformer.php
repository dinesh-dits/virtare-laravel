<?php

namespace App\Transformers\Appointment;

use App\Models\Patient\Patient;
use App\Models\Dashboard\Timezone;
use League\Fractal\TransformerAbstract;
use App\Transformers\Dashboard\TimezoneTransformer;
use App\Transformers\Patient\PatientDetailTransformer;


class AppointmentSearchTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        $timezone = "";
        if (isset($data->timezoneId) && !empty($data->timezoneId)) {
            $timezone = Timezone::where("id", $data->timezoneId)->first();
        }
        $patient=Patient::where('udid',$data->patient_id)->first();
        if($data->appoinmentStatus == 1){
            $appoinmentStatus = "Attended";
        }elseif($data->appoinmentStatus == 2){
            $appoinmentStatus = "Not Attended";
        }else{
            $appoinmentStatus = null;
        }

        if(isset($data->staffFirstName)){
            $staffFirstName = $data->staffFirstName;
        }else{
            $staffFirstName = "";
        }

        if(isset($data->staffMiddleName)){
            $staffMiddleName = $data->staffMiddleName;
        }else{
            $staffMiddleName = "";
        }

        if(isset($data->staffLastName)){
            $staffLastName = $data->staffLastName;
        }else{
            $staffLastName = "";
        }

        if(isset($data->patientFirstName)){
            $patientFirstName = $data->patientFirstName;
        }else{
            $patientFirstName = "";
        }

        if(isset($data->patientMiddleName)){
            $patientMiddleName = $data->patientMiddleName;
        }else{
            $patientMiddleName = "";
        }

        if(isset($data->patientLastName)){
            $patientLastName = $data->patientLastName;
        }else{
            $patientLastName = "";
        }

        return [
            "id" => $data->id,
            "udid" => $data->udid,
            "date" => strtotime($data->startDate),
            "notes" => $data->note,
            "flags" => $data->flags,
            "flagName" => $data->flagName,
            "cancellationNote" => $data->cancellationNote,
            "duration" => $data->duration,
            "status" => (!empty($data->status)) ? $data->status->name : '',
            "statusId" => @$data->statusId,
            "appointmentType" => (!empty($data->appointmentType->name)) ? $data->appointmentType->name : $data->appointmentType,
            'time' => strtotime($data->startDate),
            "patient" => $data->patient,
            "patientData" => $patient ? fractal()->item($patient)->transformWith(new PatientDetailTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
            "staff" => $data->staff,
            "patient_id" => $data->patient_id,
            "patientId" => $data->patientId,
            "staffId" => $data->staffId,
            "staff_id" => $data->staff_id,
            'conferenceId' => @$data->conferenceId,
            "timezoneId" => isset($data->timezoneId) ? $data->timezoneId : "",
            'timezoneUdid' => isset($timezone->udid) ? $timezone->udid : "",
            'abbr' => isset($timezone->abbr) ? $timezone->abbr : "",
            'timeZone' => isset($timezone->timeZone) ? $timezone->timeZone : "",
            'UTCOffset' => isset($timezone->UTCOffset) ? $timezone->UTCOffset : "",
            'UTCDSTOffset' => isset($timezone->UTCDSTOffset) ? $timezone->UTCDSTOffset : "",
            "staffFirstName" => $staffFirstName,
            "staffMiddleName" => $staffMiddleName,
            "staffLastName" => $staffLastName,
            "patientFirstName" => $patientFirstName,
            "patientMiddleName" => $patientMiddleName,
            "patientLastName" => $patientLastName,
            "appoinmentStatus" => $appoinmentStatus,
            "timezone" => $data->timezoneId ? fractal()->item($timezone)->transformWith(new TimezoneTransformer())->toArray() : "",
        ];
    }
}
