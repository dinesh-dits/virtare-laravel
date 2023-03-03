<?php

namespace App\Transformers\Group;

use League\Fractal\TransformerAbstract;


class StaffGroupTransformer extends TransformerAbstract
{
   
    protected array $defaultIncludes = [
        //
    ];
    
   
    protected array $availableIncludes = [
        //
    ];
    
    
    public function transform($data): array
    {
        return [
                'id' => $data->staffGroupId,
                'udid' => $data->udid,
                'firstName'=>$data->staff->firstName,
                'lastName' =>$data->staff->lastName,
                'targetUdid' =>$data->staff->udid,
                'department' =>'Department Name',
                'tag' => 'tag1'
                // 'udid'=>$data->udid,
			    // 'group'=>$data->group,
                // 'isActive'=>$data->isActive,
                // 'createdAt' =>$data->createdAt,
                // 'totalMembers' =>'0',
		];
    }
}
