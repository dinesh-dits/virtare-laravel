<?php

namespace App\Transformers\Questionnaire;

use Illuminate\Support\Facades\DB;
use League\Fractal\TransformerAbstract;
use App\Transformers\Questionnaire\QuestionOptionTransformer;
 

class QuestionScoreTransformer extends TransformerAbstract
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
        return[
            'id'=> $data->udid,
            'score'=> $data->score,
            'referenceId'=>$data->referenceId,
            'entityType'=>$data->entityType,
        ];
    }
}
