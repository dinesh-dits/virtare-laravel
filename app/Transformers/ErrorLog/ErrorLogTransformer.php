<?php

namespace App\Transformers\ErrorLog;

use League\Fractal\TransformerAbstract;


class ErrorLogTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data): array
    {
        return [
            'id' => $data->id,
            'udid' => $data->udid,
            'userId' => $data->userId,
            'file' => $data->file,
            'requestUrl' => $data->requestUrl,
            'requestMethod' => $data->requestMethod,
            'requestParameter' => json_decode($data->requestParameter),
            'errorSummary' => $data->errorSummary,
            'isActive'  => $data->isActive ? True : False,
            'createdAt' => $data->createdAt
        ];
    }
}
