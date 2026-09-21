<?php

namespace Concrete\Package\Eventbrite\Controller\SinglePage\Dashboard\Eventbrite;

use Concrete\Core\Utility\Service\Url;
use Concrete\Core\Filesystem\ElementManager;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Package\Eventbrite\Controller\Search\Events as SearchController;

class Events extends DashboardPageController
{
    protected $helpers = [
        'form',
        'concrete/ui',
        'concrete/asset_library',
    ];
    protected $pkg;
    protected $formContent;
    protected $errors;
    protected $logger;
    protected $rf;

    protected function getHeaderSearch($params)
    {
        if (!isset($this->headerASearch)) {
            $searchController = $this->app->make(SearchController::class);
            $paginationSizes = [];
            foreach ($searchController->getAllowedPaginationSizes() as $size) {
                $paginationSizes[$size] = $size;
            }
            $searching = new \Eventbrite\Search\ItemList\Event();
            $this->headerSearch = $this->app->make(ElementManager::class)->get('search/events', [
                'token' => $this->token,
                'headerSearchAction' => \Page::getCurrentPage()->getCollectionLink(),
                'params' => $params,
                'searchController' => $searchController,
                'urlHelper' => $this->app->make(Url::class),
                'paginationSizes' => $paginationSizes,
            ], 'eventbrite');

            return $this->headerSearch;
        }
    }

    public function on_start()
    {
        parent::on_start();
        $this->pkg = $this->app->make(\Concrete\Core\Package\PackageService::class)->getByHandle('eventbrite');
        $this->logger = $this->app->make(\Eventbrite\Log\EventbriteLogger::class);
        $this->rf = $this->app->make(\Concrete\Core\Http\ResponseFactoryInterface::class);
        $this->set('pkg', $this->pkg);
    }

    public function view()
    {
        $reset = false;
        if ($this->request->isPost()) {
            !$this->token->validate('events-search')
                ? $this->error->add($this->token->getErrorMessage())
                : $reset = true;
        }

        $search = $this->app->make(SearchController::class);
        $search->search($reset);

        $result = $search->getSearchResultObject();

        $this->set('result', $result);
        $this->set('items', $result->getItems());
        $this->set('pagination', $result->getPaginationHTML());

        $allowed_num_results = $search->getAllowedPaginationSizes();

        $params = $search->getStickyRequest()->getSearchRequest();
        $this->set('name', isset($params['name']) ? $params['name'] : '');

        $num_results = isset($params['num_results']) && isset($allowed_num_results[$params['num_results']])
            ? (int) $params['num_results']
            : $search->getDefaultPaginationSize();

        $this->set('num_results', $num_results);
        $this->set('params', $params);
        $this->set('allowed_num_results', $allowed_num_results);
        $this->set('token', $this->token->generate('vacancies-search'));
        $this->set('headerSearch', $this->getHeaderSearch($params));
    }

    public function details(?string $id = null)
    {
        if (!is_null($id)) {
            $entity = \Eventbrite\Entity\Event::getByID($id);
            $this->set('entity', $entity);
        } else {
            return $this->buildRedirect('/dashboard/eventbrite/events')->send();
        }
    }

    public function clear_search()
    {
        $search = $this->app->make(SearchController::class);
        $search->search(true);
        return $this->buildRedirect('/dashboard/eventbrite/events')->send();
    }
}
