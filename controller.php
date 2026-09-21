<?php

namespace Concrete\Package\Eventbrite;

use Concrete\Core\Entity\Package;
use ClassKit\Package\Traits\PageTrait;
use ClassKit\Package\PackageController;
use ClassKit\Package\Traits\BlockTrait;

class Controller extends PackageController
{
    use PageTrait;
    use BlockTrait;

    /**
     * The packages handle.
     * Note that this must be unique in the
     * entire concrete5 package ecosystem.
     *
     * @var string
     */
    protected $pkgHandle = 'eventbrite';

    /**
     * The packages version.
     *
     * @var string
     */
    protected $pkgVersion = '0.1.0';

    /**
     * The minimum Concrete version compatible with the package.
     * Override this value according to the minimum required version for your package.
     *
     * @var string
     */
    protected $appVersionRequired = '9.5.0';

    /**
     * The minimum PHP version compatible with the package.
     * Override this value according to the minimum required version for your package.
     *
     * @var string
     * @var string
     */
    protected $phpVersionRequired = '8.4';

    /**
     * Package service providers to register.
     *
     * eg. 'Concrete\Package\PackageHandle\Src\Providers\PackageServiceProvider'
     *
     * @var array
     */
    protected $providers = [];

    /**
     * An array describing the package dependencies.
     * Keys are package handles.
     * Values may be:
     * - false: this package can't be installed if the other package is already installed.
     * - true: this package can't be installed of the other package is not installed
     * - a string: this package can't be installed of the other package is not installed or it's installed with an older version
     * - an array with two strings, representing the minimum and the maximum version of the other package to be installed.
     *
     * @var array
     *
     * @example [
     *     // This package can't be installed if a package with handle other_package_1 is already installed.
     *     'other_package_1' => false,
     *     // This package can't be installed if a package with handle other_package_2 is not installed.
     *     'other_package_2' => true,
     *     // This package can't be installed if a package with handle other_package_3 is not installed, or it has a version prior to 1.0
     *     'other_package_3' => '1.0',
     *     // This package can't be installed if a package with handle other_package_4 is not installed, or it has a version prior to 2.0, or it has a version after 2.9
     *     'other_package_4' => ['2.0', '2.9'],
     * ]
     */
    protected $packageDependencies = [
        'class_kit' => true,
        'lgt_toolkit' => true,
    ];

    /**
     * Package class autoloader registrations
     * The package install helper class, included with this boilerplate,
     * is activated by default.
     *
     * @see https://goo.gl/4wyRtH
     * @var array
     */
    protected $pkgAutoloaderRegistries = [
        'src' => '\Eventbrite',
    ];

    /**
     * Package tasks to register.
     *
     * eg. 'task_handle' => \PackageHandle\Command\Task\Controller\TaskHandleController::class,
     *
     * @var array
     */
    protected $tasks = [
        'get_events' => \Eventbrite\Command\Task\Controller\GetEventsController::class,
    ];

    public function getPackageName()
    {
        return t('Eventbrite');
    }

    public function getPackageDescription()
    {
        return t('Eventbrite package');
    }

    public function installOrUpgrade(Package $pkg)
    {
        // Add blocks
        $this->autoInstallBlocks($pkg);

        // dashboard pages
        $this->addSinglePage('/dashboard/eventbrite', $pkg, t('Eventbrite'));
        $this->addSinglePage('/dashboard/eventbrite/settings', $pkg, t('Settings'));
        $this->addSinglePage('/dashboard/eventbrite/events', $pkg, t('Events'));

        // add tasks
        $this->installContentFile('tasks.xml');
    }

    public function registerRoutes(): void {}

    public function registerEvents(): void {}
}
