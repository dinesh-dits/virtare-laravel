<?php

namespace App\Transformers\Program;

use League\Fractal\TransformerAbstract;
use App\Transformers\Provider\ProviderTransformer;

class ProgramProviderTransformer extends TransformerAbstract
{
	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data): array
	{
		return (!empty($data->provider)) ? fractal()->item($data->provider)->transformWith(new ProviderTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : array();
	}
}
