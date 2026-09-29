<?php

declare(strict_types=1);

namespace EventbriteKit\Tests\Support;

use Concrete\Package\EventbriteKit\Controller\SinglePage\Dashboard\EventbriteKit\Settings;

/** For TestCases: builds the partial Settings double (getMockBuilder is protected) and wraps it. */
trait MakesSettingsHarness
{
    /**
     * Build a Settings harness for the given POST data and request conditions.
     *
     * @param array<string, mixed> $post
     */
    private function settings(array $post, bool $isPost = true, bool $csrfValid = true, bool $withPackage = true): SettingsHarness
    {
        $mock = $this->getMockBuilder(Settings::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['buildRedirect', 'flash', 'set'])
            ->getMock();

        return SettingsHarness::make($this, $mock, $post, $isPost, $csrfValid, $withPackage);
    }
}
