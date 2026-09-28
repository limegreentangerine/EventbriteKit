<?php

namespace Eventbrite\Search\Column\Set;

use Concrete\Core\Search\Column\{Column, Set};

class Event extends Set
{
    /**
     * Define the name, start date and end date columns, with end date ascending as the default sort.
     */
    public function __construct()
    {
        $this->addColumn(new Column('eve.name', t('Name'), 'getName', true));
        $this->addColumn(new Column('eve.startDate', t('Start Date'), 'getStartDateFormatted', true));
        $this->addColumn(new Column('eve.endDate', t('End Date'), 'getEndDateFormatted', true));

        $defaultSortColumn = $this->getColumnByKey('eve.endDate');
        $this->setDefaultSortColumn($defaultSortColumn, 'asc');
    }
}
