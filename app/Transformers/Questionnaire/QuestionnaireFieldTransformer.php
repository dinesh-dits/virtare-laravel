<?php

namespace App\Transformers\Questionnaire;

use App\Models\GlobalCode\GlobalCode;
use League\Fractal\TransformerAbstract;
 

class QuestionnaireFieldTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */

    protected $showData;

    public function __construct($showData = true)
    {
        $this->showData = $showData;
    }
    
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
            'questionnaireId'=> $data->questionnaireId,
            'parameterKey'=> $data->parameterKey,
            'parameterValue'=> $data->parameterValue,
            'entityType'=> $data->entityType,
            'referenceId'=> $data->referenceId,
            'isActive'=> $data->isActive?True:false,
        ];
      
    }
}
