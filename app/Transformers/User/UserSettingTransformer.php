<?php

namespace App\Transformers\User;

use League\Fractal\TransformerAbstract;

class UserSettingTransformer extends TransformerAbstract
{

	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data)
	{
		return [
            'id' =>$data->id,
            'udid'=>$data->udid,
            'config' => $data->config,
            'setting'=>$data->setting,
            'isActive'=>$data->isActive,
		];
	}
}
