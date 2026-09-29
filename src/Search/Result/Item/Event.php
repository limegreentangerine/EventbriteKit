<?php

namespace EventbriteKit\Search\Result\Item;

use URL;
use Concrete\Core\Search\Result\Item;
use ClassKit\Search\Result\Item\ItemTrait;

class Event extends Item
{
    use ItemTrait;

    /**
     * URL of the event's dashboard details page.
     *
     * @return \League\Url\UrlInterface
     */
    public function getViewUrl()
    {
        return URL::to('/dashboard/EventbriteKit/events/details', $this->entity->getID());
    }
}
