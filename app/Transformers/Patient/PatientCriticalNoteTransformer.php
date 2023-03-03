<?php

namespace App\Transformers\Patient;

use League\Fractal\TransformerAbstract;


class PatientCriticalNoteTransformer extends TransformerAbstract
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
            'id' => $data->udid,
            'patientId' => $data->patientId,
            'criticalNote' => $data->criticalNote,
            'isRead' =>$data->isRead,
            
		];
    }
}
