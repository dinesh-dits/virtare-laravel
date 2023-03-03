<?php

namespace App\Transformers\Program;

use League\Fractal\TransformerAbstract;

class ProgramTransformer extends TransformerAbstract
{
	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data): array
	{
		return [
			'udid' =>$data->udid,
			'id'=>$data->id,
			'name'=>$data->name,
            'description'=>$data->description,
            'color'=>$data->color,
            'type'=>$data->type->name,
			'typeId' => $data->typeId,
			'isActive' =>$data->isActive,
			'code' =>$data->code,
		];
	}
}
