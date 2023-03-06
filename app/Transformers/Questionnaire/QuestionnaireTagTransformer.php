<?php

namespace App\Transformers\Questionnaire;

use League\Fractal\TransformerAbstract;


class QuestionnaireTagTransformer extends TransformerAbstract
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
    public function transform($data)
    {
        return [
            // 'id'=> $data->udid,
            $data->tag,
            // 'referenceId'=>(!empty($data->referenceId))?$data->referenceId:"",
            // 'entityType'=>(!empty($data->entityType))?$data->entityType:"",
        ];

    }
}
