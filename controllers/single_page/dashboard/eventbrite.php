<?php

namespace Concrete\Package\Eventbrite\Controller\SinglePage\Dashboard;

use Concrete\Core\Page\Controller\DashboardPageController;

class Eventbrite extends DashboardPageController
{
    /**
     * Redirect the Eventbrite dashboard root to the settings page.
     */
    public function view()
    {
        $this->buildRedirect('/dashboard/eventbrite/settings')->send();
    }
}
