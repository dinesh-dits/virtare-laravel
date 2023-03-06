<?php

namespace App\Transformers\Document;

use Illuminate\Support\Facades\URL;
use League\Fractal\TransformerAbstract;
use App\Transformers\Tag\TagTransformer;
use App\Transformers\GlobalCode\GlobalCodeTransformer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class DocumentTransformer extends TransformerAbstract
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
        if (empty($data)) {
            return [];
        }
        return [
            'id' => $data->udid,
            'name' => $data->name,
            'type' => @$data->documentType->name,
            'typeId' => @$data->documentType->id,
            'patient' => $data->referanceId,
            'document' => (!empty($data->filePath)) && (!is_null($data->filePath)) ? Storage::disk('s3')->temporaryUrl($data->filePath, Carbon::now()->addDays(5)) : "",
            'path' => (!empty($data->filePath)) && (!is_null($data->filePath)) ? request()->url() . "?path=" . Storage::disk('s3')->temporaryUrl($data->filePath, Carbon::now()->addDays(5)) : "",
            'entity' => $data->entityType,
            'tags' => fractal()->collection($data->tag)->transformWith(new TagTransformer())->toArray(),
            'tag' => fractal()->collection($data->tag)->transformWith(new TagTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray()
        ];
    }
}
