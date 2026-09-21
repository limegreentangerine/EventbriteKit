<?php

namespace Eventbrite\Search\Result;

use ClassKit\Search\Result as SearchResult;

class Event extends SearchResult
{
    public function getItemDetails($result)
    {
        return new Item\Event($this, $this->listColumns, $result);
    }
}
