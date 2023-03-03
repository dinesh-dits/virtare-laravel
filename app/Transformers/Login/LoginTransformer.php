<?php

namespace App\Transformers\Login;

use App\Transformers\User\UserTransformer;
use League\Fractal\TransformerAbstract;

class LoginTransformer extends TransformerAbstract
{
	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data)
	{
		return [
			'token' => $data['token'],
			'expiresIn' => $data['expiresIn'],
			'user' => $data['user'] ? fractal()->item($data['user'])->transformWith(new UserTransformer())->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : array(),
		];
	}
}
