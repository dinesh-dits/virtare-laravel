<?php

namespace App\Transformers\Conversation;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use League\Fractal\TransformerAbstract;


class ConversationListTransformer extends TransformerAbstract
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
        // dd($data->sender);
        if (auth()->user()->id == $data->sender->id) {
            $pic = (!empty($data->receiver->profilePhoto)) && (!is_null($data->receiver->profilePhoto)) ? Storage::disk('s3')->temporaryUrl($data->receiver->profilePhoto, Carbon::now()->addDays(5)) : "";
        } else {
            $pic = (!empty($data->sender->profilePhoto)) && (!is_null($data->sender->profilePhoto)) ? Storage::disk('s3')->temporaryUrl($data->sender->profilePhoto, Carbon::now()->addDays(5)) : "";
        }
        return [
            'id' => $data->id,
            'senderId' => $data->from,
            'sender' => $data->sender->patient ? str_replace("  ", " ", ucfirst(@$data->sender->patient->lastName) . ',' . ' ' . ucfirst(@$data->sender->patient->firstName) . ' ' . ucfirst(@$data->sender->patient->middleName)) :
                str_replace("  ", " ", ucfirst(@$data->sender->staff->lastName) . ',' . ' ' . ucfirst(@$data->sender->staff->firstName) . ' ' . ucfirst(@$data->sender->staff->middleName)),
            'receiverId' => (int)$data->referenceId,
            'profile_photo' => $pic,
            'profilePhoto' => $pic,
            'expertise' => (!empty($data->receiver->staff->expertise)) ? $data->receiver->staff->expertise->name : '',
            'designation' => (!empty($data->receiver->staff)) ? @$data->receiver->staff->designation->name : '',
            'specialization' => (!empty($data->receiver->staff)) ? @$data->receiver->staff->specialization->name : '',
            'receiver' => (!empty($data->receiver->patient)) ? $data->receiver->patient->firstName . ' ' . $data->receiver->patient->lastName : (!empty($data->receiver->staff)) ? @$data->receiver->staff->firstName . ' ' . @$data->receiver->staff->lastName : '',
            'message' => (!empty($data->conversationMessages->last()->message)) ? $data->conversationMessages->last()->message : '',
            'type' => (!empty($data->conversationMessages->last()->type)) ? $data->conversationMessages->last()->type : '',
            'messageSender' => (!empty($data->conversationMessages->last()->senderId)) ? $data->conversationMessages->last()->senderId : '',
            'isRead' => (!empty($data->conversationMessages->last()->isRead)) ? 1 : 0,
            "createdAt" => (!empty($data->conversationMessages->last()->createdAt)) ? strtotime($data->conversationMessages->last()->createdAt) : '',
        ];
    }
}
