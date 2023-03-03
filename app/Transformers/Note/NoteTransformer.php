<?php

namespace App\Transformers\Note;

use Illuminate\Support\Facades\URL;
use League\Fractal\TransformerAbstract;
use App\Transformers\User\UserTransformer;


class NoteTransformer extends TransformerAbstract
{

    protected array $defaultIncludes = [
        //
    ];


    protected array $availableIncludes = [
        //
    ];


    public function transform($data): array
    {
        if (empty($data)) {
            return [];
        }
        return [
            'id' => $data->udid,
            'date' => strtotime($data->date),
            'category' => (!empty($data->categoryName->name)) ? $data->categoryName->name : $data->category,
            'type' => $data->type,
            'note' => $data->note,
            'addedBy' => $data->addedBy?$data->addedBy:'',
            'addedById' => (!empty($data->addedById)) ? $data->addedById :'',
            'addedByType' => (!empty($data->addedByType)) ? $data->addedByType : '',
            'profilePhoto' => (!empty(@$data->profilePhoto)) && (!is_null(@$data->profilePhoto)) ? str_replace("public", "", URL::to('/')) . '/' . @$data->profilePhoto : ((!empty(@$data->profilePhoto)) && (!is_null(@$data->profilePhoto)) ? str_replace("public", "", URL::to('/')) . '/' . @$data->user->profilePhoto : ''),
            'specialization' => (!empty(@$data->specialization)) ? @$data->specialization : @$data->user->staff->specialization->name,
            'color'=>(!(empty($data->flagColor)))?$data->flagColor:'',
            'icon'=>(!(empty($data->flagIcon)))?$data->flagIcon:'',
            'flag'=>(!(empty($data->flagName)))?$data->flagName:'',
            'entityType'=>(!(empty($data->entityType)))?$data->entityType:'',
        ];
    }
}
