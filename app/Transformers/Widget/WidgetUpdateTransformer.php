<?php

namespace App\Transformers\Widget;

use League\Fractal\TransformerAbstract;

class WidgetUpdateTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform($data)
    {
        return [
           'title'=>$data->title,
           'rows'=>$data->rows,
           'columns'=>$data->columns,
           'canNotViewModifyOrDelete'=>$data->canNotViewModifyOrDelete ? 'true' : 'false',
        ];
    }
}
