<?php

namespace App\Transformers\Staff;


use League\Fractal\TransformerAbstract;

class StaffAvailabilityTransformer extends TransformerAbstract
{


    protected $showData;

    public function __construct($showData = true)
    {
        $this->showData = $showData;
    }

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
            'id' => $data->udid,
            'staffId' => $data->staffId,
            'startTime' => strtotime($data->startTime),
            'endTime' => strtotime($data->endTime)
        ];
    }
}
