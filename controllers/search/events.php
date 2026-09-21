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

    protected function getSearchList()
    {
        if (is_null($this->searchList)) {
            $this->searchList = $this->app->make(SearchList::class, [$this->getStickyRequest()]);
        }

        return $this->searchList;
    }

    public function getStickyRequest()
    {
        if (is_null($this->stickyRequest)) {
            $this->stickyRequest = new StickyRequest('ev_events');
        }

        return $this->stickyRequest;
    }

    public function getAllowedPaginationSizes()
    {
        return [
            10,
            20,
            50,
            100,
        ];
    }

    public function getDefaultPaginationSize()
    {
        return $this->getAllowedPaginationSizes()[0];
    }

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
            $searchList->filterByTitle($q, 'like');
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
