<?php

namespace Concrete\Package\Eventbrite\Block\EventbriteEventList;

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Block\BlockController;
use ClassKit\Page\TranslationAdaptorTrait;

class Controller extends BlockController
{
    use TranslationAdaptorTrait;

    protected $btTable = 'btEventbriteEventList';
    protected $btDefaultSet = 'eventbrite';
    protected $btInterfaceWidth = 800;
    protected $btInterfaceHeight = 600;

    protected function getEvents()
    {
        $search = new \Eventbrite\Search\ItemList\Event();
        $search->filterByActive();
        $search->sortByField('endDate', 'ASC');
        return $search->getResults();
    }

    public function getBlockTypeName()
    {
        return t('Eventbrite Events Listing');
    }

    public function getBlockTypeDescription()
    {
        return t('Carousel of Eventbrite Events');
    }

    public function add() {}

    public function edit() {}

    public function save($args)
    {
        $args['content'] = \Concrete\Core\Editor\LinkAbstractor::translateTo($args['content']);
        parent::save($args);
    }

    public function view()
    {
        $this->set('form', $this->app->make('helper/form'));
        $this->set('events', $this->getEvents());
    }
}
