<?php

namespace App\Transformers\RolePermission;

use App\Transformers\Screen\ScreenTransformer;
use App\Transformers\Action\ActionTransformer;
use App\Transformers\Module\ModuleTransformer;
use League\Fractal\TransformerAbstract;


class PermissionTransformer extends TransformerAbstract
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
                "id" => $data->id,
                "name" => PermissionTransformer::from_camel_case($data->name),
                "description"=>$data->description,
                "screens" => fractal()->collection($data->screens)->transformWith(new ScreenTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray()
        ];
    }

    function from_camel_case($input) {
        $pattern = ucwords($input);
        return str_replace("And","and",$pattern);
    }
      
}
