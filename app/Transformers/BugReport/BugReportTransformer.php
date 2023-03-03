<?php

namespace App\Transformers\BugReport;

use App\Transformers\GlobalCode\GlobalCodeTransformer;
use App\Transformers\Task\PatientTaskGlobalCodeTransformer;
use League\Fractal\TransformerAbstract;


class BugReportTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [
        //
    ];


    protected array $availableIncludes = [
        //
    ];


    public function transform($data): array
    {
        return [
            'bugReportId' => $data->udid,
            'subjectTitle' => $data->subjectTitle,
            'buildVersion' => $data->buildVersion,
            'osVersion' => $data->osVersion,
            'deviceName' => $data->deviceName,
            'deviceId' => $data->deviceId,
            'userLoginEmail' => $data->userLoginEmail,
            'bugReportEmail' => $data->bugReportEmail,
            'category' => $data->bugCategory ? fractal()->item($data->bugCategory)->transformWith(new PatientTaskGlobalCodeTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
            'priority' => $data->bugPriority ? fractal()->item($data->bugPriority)->transformWith(new GlobalCodeTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
            'platform' => $data->platform,
            'location' => $data->location,
            'description' => $data->description,
            'createdAt' => strtotime($data->createdAt),
            'screen' => $data->bugScreen ? fractal()->item($data->bugScreen)->transformWith(new ScreenTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
            'screenType' => $data->screenType,
            'documentPath' => $data->bugDocument ? fractal()->collection($data->bugDocument)->transformWith(new BugReportDocumentTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : "",
        ];
    }
}
