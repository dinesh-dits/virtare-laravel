<?php

namespace App\Transformers\Tag;

use League\Fractal\TransformerAbstract;
use App\Transformers\GlobalCode\GlobalCodeTransformer;


class TagTransformer extends TransformerAbstract
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
            'id'=>$data->udid,
            'tagId'=>$data->tags->id,
			'tag'=>$data->tags->name,
            'documentId'=>$data->documentId,
		];
    }
}
