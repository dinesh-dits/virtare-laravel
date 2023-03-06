<?php

namespace App\Transformers\CustomForms;

use Illuminate\Support\Str;
use App\Models\Dashboard\Timezone;
use League\Fractal\TransformerAbstract;
use App\Transformers\CustomForms\CustomFormDetailTransformer;


class CustomFormsTransformer extends TransformerAbstract
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
      $response = json_decode($data['formFields']);
     // print_r($response); //die;
        return [
            'id' => $data['udid'],
            'formName' => $data['formName'],
            'formType' => 'Form/Document',
            'createdOn' => strtotime($data['createdAt']),
            'templateId'=>isset($data['templateId'])?$data['templateId']:0,
            'type'=>(isset($response[0]) && $response[0]->type == 'questionnaire')?'questionnaire':'',
            //'type'=>(isset($response->type) && !empty($response->type[0]))?$response->type[0]:'',
            'fields' => $this->showData?json_decode($data['formFields']):new \stdClass()//$this->showData ? fractal()->collection($data->fields)->transformWith(new CustomFormDetailTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : new \stdClass(),         
        ];
    }
}
