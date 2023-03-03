<?php

namespace App\Transformers\Program;

use League\Fractal\TransformerAbstract;

class ProgramNewTransformer extends TransformerAbstract
{
	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data): array
	{
		return [
			'udid' =>$data->udid,
		];
	}
}
