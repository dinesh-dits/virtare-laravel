<?php

namespace App\Transformers\Login;

use League\Fractal\TransformerAbstract;
use App\Transformers\User\UserPatientTransformer;

class LoginPatientTransformer extends TransformerAbstract
{
	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data)
	{
		return [
			'token' => $data['token'],
			'expiresIn' => $data['expiresIn'],
			'user' => $data['user'] ? fractal()->item($data['user'])->transformWith(new UserPatientTransformer(true))->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : array(),
		];
	}
}
