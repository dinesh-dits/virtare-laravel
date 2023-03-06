<?php

namespace App\Transformers\Notification;

use League\Fractal\TransformerAbstract;

class NotificationListTransformer extends TransformerAbstract
{
	protected array $defaultIncludes = [];

	protected array $availableIncludes = [];

	public function transform($data)
	{
		//print_r($data); die;
		return [
			'date' => $data['year'],
			'value' => $data ?  fractal()->item($data)->transformWith(new NotificationTransformer(true))->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray() : array(),
		];
	}
}
