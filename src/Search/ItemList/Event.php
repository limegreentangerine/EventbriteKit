<?php

namespace Eventbrite\Search\ItemList;

use ClassKit\Search\ItemList\ListTrait;
use Eventbrite\Entity\Event as CustomItemList;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Search\ItemList\Database\ItemList;
use Pagerfanta\Doctrine\DBAL\QueryAdapter as DoctrineDbalAdapter;
use Concrete\Core\Application\{ApplicationAwareInterface, ApplicationAwareTrait};

class Event extends ItemList implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;
    use ListTrait;

    protected $prefix = 'eve';

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

    public function createQuery()
    {
        $this->query->select(sprintf('%s.*', $this->prefix));
        $this->query->from('eventbrite_events', $this->prefix);
        $this->query->groupBy(sprintf('%s.id', $this->prefix));
    }

    public function filterByName(string $name, string $operator = '=')
    {
        $this->filterBy(sprintf('%s.name', $this->prefix), $name, $operator);
    }

    public function filterByActive(bool $flag = true)
    {
        $today = new \DateTime();
        $this->filterBy(sprintf('%s.endDate', $this->prefix), $today->format('Y-m-d H:i:s'), $flag ? '>=' : '<=');
    }

    public function sortByField(string $field = '', string $order = 'asc')
    {
        $this->query->orderBy(sprintf('%s.%s', $this->prefix, $field), $order);
    }

    public function getResultIDs(): array
    {
        $query = $this->getQueryObject();
        $query->select(sprintf('DISTINCT(%s.id)', $this->prefix));
        $rows = $query->execute()->fetchFirstColumn();
        return $rows ?: [];
    }

    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();
        $query->resetQueryParts(['select', 'orderBy', 'groupBy', 'having'])
        ->select(sprintf('COUNT(DISTINCT %s.id)', $this->prefix));

        return (int) $query->execute()->fetchOne();
    }

    public function getResult($row)
    {
        return CustomItemList::getByID($row['id']);
    }

}
