<?php

namespace App\Events;

use App\Models\Patient\Patient;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
class PateientIntakeEvent extends Event
{

	use  InteractsWithSockets, SerializesModels;

    /**
     * The patient instance.
     *
     * @var App\Models\Patient\Patient
     */
    public $patient;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($patient)
    {
        $this->patient = $patient;
    }
}