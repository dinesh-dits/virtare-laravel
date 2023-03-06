<?php

namespace App\Transformers\Communication;

use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use League\Fractal\TransformerAbstract;
use App\Transformers\Staff\StaffTransformer;
use App\Transformers\GlobalCode\GlobalCodeTransformer;
use Illuminate\Support\Facades\Auth;

class CommunicationTransformer extends TransformerAbstract
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
        $dataArray =  [
             'id'=>$data->id,
            'from'=>isset($data->sender->patient) ? str_replace("  ", " ", ucfirst(@$data->sender->patient->lastName) . ',' . ' ' . ucfirst(@$data->sender->patient->firstName) . ' ' . ucfirst(@$data->sender->patient->middleName)): 
            str_replace("  ", " ", ucfirst(@$data->sender->staff->lastName) . ',' . ' ' . ucfirst(@$data->sender->staff->firstName). ' ' . ucfirst(@$data->sender->staff->middleName)),
            'type'=>$data->type->name,
            'to'=>@$data->receiver->patient ?
             str_replace("  ", " ", ucfirst(@$data->receiver->patient->lastName) . ',' . ' ' . ucfirst(@$data->receiver->patient->firstName) . ' ' . ucfirst(@$data->receiver->patient->middleName)): 
             str_replace("  ", " ", ucfirst(@$data->receiver->staff->lastName) . ',' . ' ' . ucfirst(@$data->receiver->staff->firstName). ' ' . ucfirst(@$data->sender->staff->middleName)),
            'category'=>$data->globalCode->name,
            'priority'=>$data->priority->name,
            'createdAt'=>strtotime($data->updatedAt),
            'senderId' => $data->from,
            'fromId' => @$data->sender->patient ? @$data->sender->patient->udid : @$data->sender->staff->udid,
            'toId' => @$data->receiver->patient ? @$data->receiver->patient->udid : @$data->receiver->staff->udid,
            'sender' => @$data->sender->patient ? str_replace("  ", " ", ucfirst(@$data->sender->patient->lastName) . ',' . ' ' . ucfirst(@$data->sender->patient->firstName) . ' ' . ucfirst(@$data->sender->patient->middleName)): 
            str_replace("  ", " ", ucfirst(@$data->sender->staff->lastName) . ',' . ' ' . ucfirst(@$data->sender->staff->firstName). ' ' . ucfirst(@$data->sender->staff->middleName)),
            'receiverId' => $data->referenceId,
            'profile_photo' => (!empty($data->receiver->profilePhoto)) && (!is_null($data->receiver->profilePhoto)) ? URL::to('/') . '/' . $data->receiver->profilePhoto : "",
            'profilePhoto' => (!empty($data->receiver->profilePhoto)) && (!is_null($data->receiver->profilePhoto)) ? URL::to('/') . '/' . $data->receiver->profilePhoto : "",
            'expertise' => (!empty($data->receiver->staff->expertise)) ? $data->receiver->staff->expertise->name : '',
            'designation' => @$data->receiver->staff ? @$data->receiver->staff->designation->name : '',
            'specialization' => @$data->receiver->staff ? @$data->receiver->staff->specialization->name : '',
            'receiver' => @$data->receiver->patient ? @$data->receiver->patient->firstName . ' ' . @$data->receiver->patient->lastName : @$data->receiver->staff->firstName . ' ' . @$data->receiver->staff->lastName,
            'message' => (!empty($data->conversationMessages->last()->message)) ? $data->conversationMessages->last()->message : '',
            'messageType' => (!empty($data->conversationMessages->last()->type)) ? $data->conversationMessages->last()->type : '',
            'messageSender' => (!empty($data->conversationMessages->last()->senderId)) ? $data->conversationMessages->last()->senderId : '',
            'isRead' => (isset($data->conversationMessages)) && (@$data->conversationMessages->last()->isRead==0) ? 0 : 1,
            "created_at" => (!empty($data->conversationMessages->last()->updatedAt)) ? strtotime($data->conversationMessages->last()->updatedAt) : '',
            "is_sender_patient" => false,
            "is_receiver_patient" => false,
        ];


        if(@$data->sender->patient){
            if((!empty($data->sender['profilePhoto'])) && (!is_null($data->sender['profilePhoto']))){
                $dataArray['patientPic'] = str_replace("public", "", URL::to('/')) . '/' . $data->sender['profilePhoto'];
            }else{
                $dataArray['patientPic'] = '';
            }
            $dataArray['patientName'] = str_replace("  ", " ", ucfirst(@$data->sender->patient->lastName) . ',' . ' ' . ucfirst(@$data->sender->patient->firstName) . ' ' . ucfirst(@$data->sender->patient->middleName));
            $dataArray['is_sender_patient'] = true ;
        }elseif(@$data->receiver->patient){
            if((!empty($data->receiver['profilePhoto'])) && (!is_null($data->receiver['profilePhoto']))){
                $dataArray['patientPic'] = str_replace("public", "", URL::to('/')) . '/' . $data->receiver['profilePhoto'];
            }else{
                $dataArray['patientPic'] = '';
                
            }
            $dataArray['patientName'] = str_replace("  ", " ", ucfirst(@$data->receiver->patient->lastName) . ',' . ' ' . ucfirst(@$data->receiver->patient->firstName) . ' ' . ucfirst(@$data->receiver->patient->middleName)) ;
            $dataArray['is_receiver_patient'] = true ;
        }elseif(isset(auth()->user()->Id) && auth()->user()->Id == $data->sender['Id']){
            $dataArray['patientPic'] = str_replace("public", "", URL::to('/')) . '/' . $data->receiver['profilePhoto'];
            $dataArray['patientName'] = str_replace("  ", " ", ucfirst(@$data->receiver->staff->lastName) . ',' . ' ' . ucfirst(@$data->receiver->staff->firstName). ' ' . ucfirst(@$data->receiver->staff->middleName));
        }else{
            if(isset($data->sender['profilePhoto'])){
                $profilePhoto  = $data->sender['profilePhoto'];
            }else{
                $profilePhoto  = "";
            }
            $dataArray['patientPic'] = str_replace("public", "", URL::to('/')) . '/' . $profilePhoto;
            $dataArray['patientName'] = str_replace("  ", " ", ucfirst(@$data->sender->staff->lastName) . ',' . ' ' . ucfirst(@$data->sender->staff->firstName). ' ' . ucfirst(@$data->sender->staff->middleName));
        }
        return $dataArray;
    }
}
