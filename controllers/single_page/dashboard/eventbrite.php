<?php

namespace Concrete\Package\EventbriteKit\Controller\SinglePage\Dashboard;

use Concrete\Core\Page\Controller\DashboardPageController;

class Eventbrite extends DashboardPageController
{
    /**
     * Redirect the EventbriteKit dashboard root to the settings page.
     */
    public function view()
    {
        $this->buildRedirect('/dashboard/eventbrite/settings')->send();
    }
}
