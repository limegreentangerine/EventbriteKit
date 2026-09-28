<?php

namespace Concrete\Package\Eventbrite\Controller\SinglePage\Dashboard\Eventbrite;

use Package;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Page\Controller\DashboardPageController;

class Settings extends DashboardPageController
{
    protected $pkg;
    protected $helpers = [
        'form',
        'concrete/ui',
    ];

    /**
     * Add a form error when the API key is empty.
     *
     * @param \Concrete\Core\Http\Request $request
     */
    protected function validate($request)
    {
        $vstrings = $this->app->make('helper/validation/strings');

        if (!$vstrings->notempty($request->request('api_key'))) {
            $this->error->add(t('Please enter a valid API Key'), 'api_key');
        }
    }

    /**
     * Load the eventbrite package and pass it to the view.
     */
    public function on_start()
    {
        parent::on_start();

        $this->pkg = Package::getByHandle('eventbrite');
        $this->set('pkg', $this->pkg);
    }

    /**
     * Validate the API key and base URL and save them to the package file config.
     *
     * Redirects on success or on a non-POST request; on a validation error the form is re-populated.
     *
     * @throws UserMessageException When the eventbrite package isn't installed
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|null
     */
    public function save()
    {
        if ($this->request->isPost()) {
            if (!$this->token->validate('submit')) {
                $this->error->add($this->token->getErrorMessage());
            }

            if (!is_object($this->pkg)) {
                throw new UserMessageException(t('Eventbrite Package not found'));
            }
            $config = $this->pkg->getFileConfig();


            $this->validate($this->request);

            if (!$this->error->has()) {
                $config->save('eventbrite.api_key', $this->request->request('api_key'));
                $config->save('eventbrite.base_url', $this->request->request('base_url'));

                $this->flash('success', t('Eventbrite settings saved.'));
                return $this->buildRedirect('/dashboard/eventbrite/settings')->send();
            }
            $this->set('formContent', $this->request->request());

        } else {
            return $this->buildRedirect('/dashboard/eventbrite/settings')->send();
        }
    }
}
