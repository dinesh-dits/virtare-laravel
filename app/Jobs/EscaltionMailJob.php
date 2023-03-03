<?php

namespace App\Jobs;
use App\Services\Api\EscalationService;

class EscaltionMailJob extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $escaltionId;
    public function __construct($escaltionId)
    {
        
        $this->escaltionId = $escaltionId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->escaltionId;
        $EscalationService = new EscalationService();         
        $filename =  $EscalationService->escalationEmailAdd(NULL,$this->escaltionId);
    }
}
