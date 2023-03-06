<?php

namespace App\Transformers\BugReport;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use League\Fractal\TransformerAbstract;


class BugReportDocumentTransformer extends TransformerAbstract
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
            'bugReportDocumentId' => $data->udid,
            'filePath' => Storage::disk('s3')->temporaryUrl($data->filePath, Carbon::now()->addDays(5)),
            'isActive' => $data->isActive,
        ];
    }
}
