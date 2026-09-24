<?php

namespace Concrete\Package\Eventbrite\Controller\Search;

use Concrete\Core\Search\StickyRequest;
use Concrete\Core\Controller\AbstractController;
use Eventbrite\Search\ItemList\Event as SearchList;
use Eventbrite\Search\Result\Event as SearchResult;
use Eventbrite\Search\Column\Set\Event as ColumnSet;

class Events extends AbstractController
{
    private $stickyRequest;
    private $searchList;
    private $searchResult;

    /**
     * Get the event item list, created on first use and bound to the sticky request.
     *
     * @return SearchList
     */
    protected function getSearchList()
    {
        if (is_null($this->searchList)) {
            $this->searchList = $this->app->make(SearchList::class, [$this->getStickyRequest()]);
        }

        return $this->searchList;
    }

    /**
     * Get the sticky request that keeps search params between page loads (`ev_events` namespace).
     *
     * @return StickyRequest
     */
    public function getStickyRequest()
    {
        if (is_null($this->stickyRequest)) {
            $this->stickyRequest = new StickyRequest('ev_events');
        }

        return $this->stickyRequest;
    }

    /**
     * Page sizes the search UI allows.
     *
     * @return list<int>
     */
    public function getAllowedPaginationSizes()
    {
        return [
            10,
            20,
            50,
            100,
        ];
    }

    /**
     * Page size to use when the request doesn't give a valid one (the smallest allowed size).
     *
     * @return int
     */
    public function getDefaultPaginationSize()
    {
        return $this->getAllowedPaginationSizes()[0];
    }

    /**
     * Run the event search from the sticky request params and store the result for getSearchResultObject().
     *
     * Applies the default sort column, the name filter and the requested page size.
     *
     * @param bool $reset Clear the stored search params first
     */
    public function search($reset = false)
    {
        $stickyRequest = $this->getStickyRequest();
        $searchList = $this->getSearchList();

        if ($reset) {
            $stickyRequest->resetSearchRequest();
        }

        $req = $stickyRequest->getSearchRequest();

        $columnSet = new ColumnSet();

        if (!$searchList->getActiveSortColumn()) {
            $sortColumn = $columnSet->getDefaultSortColumn();
            $searchList->sanitizedSortBy($sortColumn->getColumnKey(), $sortColumn->getColumnDefaultSortDirection());
        }

        $vnumbers = $this->app->make('helper/validation/numbers');
        $req = $stickyRequest->getSearchRequest();

        $q = isset($req['name']) ? $req['name'] : null;
        if (is_string($q) && $q !== '') {
            $searchList->filterByName($q, 'like');
        }

        $paginationSize = null;
        $q = isset($req['num_results']) ? $req['num_results'] : null;
        if ($q && $vnumbers->integer($q)) {
            $q = (int) $q;
            $paginationSizes = $this->getAllowedPaginationSizes();
            if (in_array($q, $paginationSizes, true)) {
                $paginationSize = (int) $q;
            }
        }

        if ($paginationSize === null) {
            $paginationSize = $this->getDefaultPaginationSize();
        }

        $searchList->setItemsPerPage($paginationSize);

        $this->searchResult = new SearchResult(
            $columnSet,
            $searchList,
            $this->app->make('url/manager')->resolve(['dashboard/eventbrite/events/']),
        );
    }

    /**
     * Get the search result (once the search() method has been called).
     *
     * @return SearchResult|null
     */
    public function getSearchResultObject()
    {
        return $this->searchResult;
    }
}
