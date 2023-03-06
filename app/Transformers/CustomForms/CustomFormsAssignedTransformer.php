<?php

namespace App\Transformers\CustomForms;

use Illuminate\Support\Str;
use App\Models\Dashboard\Timezone;
use League\Fractal\TransformerAbstract;
use App\Transformers\CustomForms\CustomFormDetailTransformer;


class CustomFormsAssignedTransformer extends TransformerAbstract
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

    protected $showData;

	public function __construct($showData = true)
	{
		$this->showData = $showData;
	}
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($data): array
    {       
       $response = isset($data['customform']->formFields)?json_decode($data['customform']->formFields):array();
        return [
            'assignedUdid' => $data->udid,
            'formUdid' => $data['customform']->udid,
            'formName' => $data['customform']->formName,
            'formType' => 'Form/Document',
            'type'=>(isset($response[0]) && $response[0]->type == 'questionnaire')?'questionnaire':'',            
            'templateId'=>(isset($response[0]) && $response[0]->type == 'questionnaire')?$response[0]->id:'',
            'filled_status' => (isset($data['response']) && (isset($data['response']->id)))?1:0,
            'assignedOn' => strtotime($data['customform']->createdAt),
            
        ];
    }
}
