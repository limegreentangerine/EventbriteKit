<?php

namespace EventbriteKit\Search\ItemList;

use ClassKit\Search\ItemList\ListTrait;
use EventbriteKit\Entity\Event as CustomItemList;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Search\ItemList\Database\ItemList;
use Pagerfanta\Doctrine\DBAL\QueryAdapter as DoctrineDbalAdapter;
use Concrete\Core\Application\{ApplicationAwareInterface, ApplicationAwareTrait};

class Event extends ItemList implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;
    use ListTrait;

    protected $prefix = 'eve';

    /**
     * Build pagination that counts distinct event IDs for the total.
     *
     * @return Pagination
     */
    protected function createPaginationObject()
    {
        $adapter = new DoctrineDbalAdapter(
            $this->deliverQueryObject(),
            function (\Doctrine\DBAL\Query\QueryBuilder $query) {
                $query
                    ->resetQueryParts(['groupBy', 'orderBy', 'having'])
                    ->select(sprintf('COUNT(DISTINCT %s.id)', $this->prefix))
                    ->setMaxResults(1);
            },
        );
        return new Pagination($this, $adapter);
    }

    /**
     * Select all columns from `EventbriteKit_events`, grouped by ID.
     */
    public function createQuery()
    {
        $this->query->select(sprintf('%s.*', $this->prefix));
        $this->query->from('EventbriteKit_events', $this->prefix);
        $this->query->groupBy(sprintf('%s.id', $this->prefix));
    }

    /**
     * Filter by event name.
     *
     * @param string $operator SQL comparison operator, e.g. '=' or 'LIKE'
     */
    public function filterByName(string $name, string $operator = '=')
    {
        $this->filterBy(sprintf('%s.name', $this->prefix), $name, $operator);
    }

    /**
     * Filter on whether the event has ended.
     *
     * @param bool $flag True for events ending now or later, false for events that have already ended
     */
    public function filterByActive(bool $flag = true)
    {
        $today = new \DateTime();
        $this->filterBy(sprintf('%s.endDate', $this->prefix), $today->format('Y-m-d H:i:s'), $flag ? '>=' : '<=');
    }

    /**
     * Sort by a column of the events table.
     *
     * @param string $field Column name, without the table alias
     * @param string $order 'asc' or 'desc'
     */
    public function sortByField(string $field = '', string $order = 'asc')
    {
        $this->query->orderBy(sprintf('%s.%s', $this->prefix, $field), $order);
    }

    /**
     * Get the IDs of all events matching the current filters.
     *
     * @return list<string>
     */
    public function getResultIDs(): array
    {
        $query = $this->getQueryObject();
        $query->select(sprintf('DISTINCT(%s.id)', $this->prefix));
        $rows = $query->execute()->fetchFirstColumn();
        return $rows ?: [];
    }

    /**
     * Count the distinct events matching the current filters.
     *
     * @return int
     */
    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();
        $query->resetQueryParts(['select', 'orderBy', 'groupBy', 'having'])
        ->select(sprintf('COUNT(DISTINCT %s.id)', $this->prefix));

        return (int) $query->execute()->fetchOne();
    }

    /**
     * Load the Event entity for a result row.
     *
     * @param array<string, mixed> $row Database row; only `id` is used
     *
     * @return \EventbriteKit\Entity\Event|null
     */
    public function getResult($row)
    {
        return CustomItemList::getByID($row['id']);
    }

}
