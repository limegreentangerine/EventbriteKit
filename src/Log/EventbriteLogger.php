<?php

namespace EventbriteKit\Log;

use ClassKit\Log\Logger;

class EventbriteLogger extends Logger
{
    /**
     * Create a logger on the `EventbriteKit` channel.
     */
    public function __construct()
    {
        parent::__construct('EventbriteKit');
    }
}
