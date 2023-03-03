<?php

namespace App\Transformers\RolePermission;

use App\Transformers\Screen\ScreenTransformer;
use App\Transformers\Action\ActionTransformer;
use App\Transformers\Module\ModuleTransformer;
use App\Transformers\Module\PermissionModuleTransformer;
use League\Fractal\TransformerAbstract;


class RolePermissionTransformer extends TransformerAbstract
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
            "name" => $data->role->roles,
            "description" => $data->role->roleDescription,
            'module' => fractal()->item($data->action->screen->module)->transformWith(new PermissionModuleTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray()
        ];
    }
}
