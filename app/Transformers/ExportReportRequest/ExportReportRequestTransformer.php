<?php

namespace App\Transformers\ExportReportRequest;

use League\Fractal\TransformerAbstract;


class ExportReportRequestTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        return [
            'id' => $data->id,
            'udid' => $data->udid,
            'userId' => $data->userId,
            'reportType' => $data->reportType,
            'isActive'  => $data->isActive ? True : False
        ];
    }
}
