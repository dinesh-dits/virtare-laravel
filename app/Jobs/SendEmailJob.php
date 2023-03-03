<?php

namespace App\Jobs;
use App\Helper;

class SendEmailJob extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $to;
    private $fromName;
    private $messageText;
    private $subject;
    private $emailFrom;
    private $userIds;
    private $type;
    private $refrenceId;


    public function __construct($to,$fromName,$messageText,$subject,$emailFrom,$userIds=array(),$type='',$refrenceId='')
    {
        $this->to = $to;
        $this->fromName = $fromName;
        $this->messageText = $messageText;
        $this->subject = $subject;
        $this->emailFrom = $emailFrom;
        $this->userIds = $userIds;
        $this->type = $type;
        $this->refrenceId = $refrenceId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Helper::CommonEmailFunction($this->to , $this->fromName, $this->messageText, $this->subject,$this->emailFrom,$this->userIds,$this->type,$this->refrenceId);
    }
}
