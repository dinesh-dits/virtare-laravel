<?php

namespace App\Transformers\Communication;

use Illuminate\Support\Facades\URL;
use League\Fractal\TransformerAbstract;
use App\Transformers\Staff\StaffTransformer;


class CommunicationCallTransformer extends TransformerAbstract
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
            'staffId' => @$data->callRecord->staff ? $data->callRecord->staff->udid : '',
            'staff' => @$data->callRecord->staff ? str_replace("  ", " ", ucfirst(@$data->callRecord->staff->lastName) . ',' . ' ' . ucfirst(@$data->callRecord->staff->firstName) . ' ' . ucfirst(@$data->callRecord->staff->middleName)) : '',
            'startTime' =>strtotime(@$data->callRecord->callRecordTime->startTime),
            'endTime' =>strtotime(@$data->callRecord->callRecordTime->endTime),
            'date' => strtotime(@$data->createdAt),
        ];

        
    }
}
