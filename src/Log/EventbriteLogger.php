<?php

namespace Eventbrite\Log;

use ClassKit\Log\Logger;

class EventbriteLogger extends Logger
{
    /**
     * Create a logger on the `eventbrite` channel.
     */
    public function __construct()
    {
        parent::__construct('eventbrite');
    }
}
