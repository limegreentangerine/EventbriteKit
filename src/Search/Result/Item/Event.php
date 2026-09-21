<?php

namespace Eventbrite\Search\Result\Item;

use URL;
use Concrete\Core\Search\Result\Item;
use ClassKit\Search\Result\Item\ItemTrait;

class Event extends Item
{
    use ItemTrait;

    public function getViewUrl()
    {
        return URL::to('/dashboard/eventbrite/events/details', $this->entity->getID());
    }
}
