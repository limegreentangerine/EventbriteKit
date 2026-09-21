<?php

namespace Eventbrite\Log;

use ClassKit\Log\Logger;

class EventbriteLogger extends Logger
{
    public function __construct()
    {
        parent::__construct('eventbrite');
    }
}
