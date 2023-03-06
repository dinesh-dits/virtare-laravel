<?php

namespace App\Transformers\Communication;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use App\Transformers\Staff\StaffTransformer;
use App\Transformers\Communication\MessageCountTransformer;
use App\Transformers\Communication\MessageTypeCountTransformer;


class MessageTypeTransformer extends TransformerAbstract
{
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
        return [
            'text' => $data->messageName,
            'count' => $data->count,
            'duration' => strtotime($data->duration),
            'time' => $data->time,
        ];
    }
}
