<?php

namespace Eventbrite\Search\Result;

use ClassKit\Search\Result as SearchResult;

class Event extends SearchResult
{
    /**
     * Wrap a result entity in the package's search result item.
     *
     * @param \Eventbrite\Entity\Event $result
     *
     * @return Item\Event
     */
    public function getItemDetails($result)
    {
        return new Item\Event($this, $this->listColumns, $result);
    }
}
