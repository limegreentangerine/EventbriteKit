<?php

namespace Concrete\Package\EventbriteKit\Block\EventbriteEventList;

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Block\BlockController;
use ClassKit\Page\TranslationAdaptorTrait;

class Controller extends BlockController
{
    use TranslationAdaptorTrait;

    protected $btTable = 'btEventbriteEventList';
    protected $btDefaultSet = 'EventbriteKit';
    protected $btInterfaceWidth = 800;
    protected $btInterfaceHeight = 600;

    /**
     * Get events that haven't ended yet, soonest-ending first.
     *
     * @return \EventbriteKit\Entity\Event[]
     */
    protected function getEvents()
    {
        $search = new \EventbriteKit\Search\ItemList\Event();
        $search->filterByActive();
        $search->sortByField('endDate', 'ASC');
        return $search->getResults();
    }

    /**
     * Block type name shown in the block picker.
     *
     * @return string
     */
    public function getBlockTypeName()
    {
        return t('Eventbrite Events Listing');
    }

    /**
     * Block type description shown in the block picker.
     *
     * @return string
     */
    public function getBlockTypeDescription()
    {
        return t('Carousel of Eventbrite Events');
    }

    /**
     * Prepare the add dialog. Nothing to set up.
     */
    public function add() {}

    /**
     * Prepare the edit dialog. Nothing to set up.
     */
    public function edit() {}

    /**
     * Save block data, first converting editor links in `content` to their stored form.
     *
     * @param array<string, mixed> $args Submitted block form data
     */
    public function save($args)
    {
        $args['content'] = \Concrete\Core\Editor\LinkAbstractor::translateTo($args['content']);
        parent::save($args);
    }

    /**
     * Pass the form helper and the list of active events to the block view.
     */
    public function view()
    {
        $this->set('form', $this->app->make('helper/form'));
        $this->set('events', $this->getEvents());
    }
}
