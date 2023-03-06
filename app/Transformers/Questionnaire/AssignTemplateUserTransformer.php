<?php

namespace App\Transformers\Questionnaire;

use League\Fractal\TransformerAbstract;

class AssignTemplateUserTransformer extends TransformerAbstract
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
            'questionnaireTemplateId'=>$data->questionnaireTemplateUdid,
            'templateName'=>$data->templateName,
            'templateTypeId'=>$data->templateTypeId,
            'templateType'=>$data->templateType,
            'userId'=> $data->assignToUdid,
            'userName'=>$data->assignTo,
            'createdBy' =>$data->assignByUdid,
            'assignBy' =>$data->assignBy,
            'referenceId'=>$data->referenceId, 
            'entityType'=>$data->entityType, 
            'entity'=>$data->udid,
            "createdAt" => strtotime($data->createdAt),
            'isActive' => $data->isActive ? True : False,
        ];
      
    }
}
