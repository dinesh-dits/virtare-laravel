<?php

namespace App\Transformers\Staff;

use League\Fractal\TransformerAbstract;

class StaffNoteTransformer extends TransformerAbstract
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
            'id'=>@$data->notes->id,
            'notes'=>@$data->notes->note,
            'type'=>@$data->appointmentType->name,
            'date'=>strtotime(@$data->notes->createdAt)
        ];
    }
}
