<?php

namespace App\Transformers\Communication;

use League\Fractal\TransformerAbstract;


class CommunicationInboundTransformer extends TransformerAbstract
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
          'id' => $data->udid,
          'from' => $data->from,
          'to' => $data->to,
          'type' => $data->type,
          'subject' => $data->subject,
          'message' => $data ? $data->message : "",
          'isActive' => $data->isActive,
          'createdAt' => strtotime($data->createdAt),
		];
    }
}
