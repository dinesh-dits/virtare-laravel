<?php

namespace App\Events;

class SetUpPasswordEvent extends Event
{
    public $data;

    /**
     * Create a new event instance.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }
}
