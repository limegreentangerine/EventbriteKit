<?php

declare(strict_types=1);

namespace EventbriteKit\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use Concrete\Core\Error\UserMessageException;
use EventbriteKit\Tests\Support\MakesSettingsHarness;

final class SettingsTest extends TestCase
{
    use MakesSettingsHarness;

    /** A non-POST request redirects without saving. */
    public function testNonPostRedirectsWithoutSaving(): void
    {
        $h = $this->settings([], isPost: false);
        $h->controller->save();

        $this->assertSame(['/dashboard/eventbrite/settings'], $h->redirects);
        $this->assertSame([], $h->config->all());
    }

    /** A blank API key adds an error, saves nothing and re-populates the form. */
    public function testEmptyApiKeyIsRejectedAndFormRepopulated(): void
    {
        $h = $this->settings(['api_key' => '  ', 'base_url' => 'https://x.test']);
        $h->controller->save();

        $this->assertTrue($h->error()->has());
        $this->assertSame([], $h->config->all());
        $this->assertSame([], $h->redirects);
        $this->assertSame(['api_key' => '  ', 'base_url' => 'https://x.test'], $h->vars['formContent']);
    }

    /** An invalid CSRF token adds an error and saves nothing. */
    public function testInvalidCsrfTokenBlocksSave(): void
    {
        $h = $this->settings(['api_key' => 'k', 'base_url' => 'https://x.test'], csrfValid: false);
        $h->controller->save();

        $this->assertTrue($h->error()->has());
        $this->assertSame([], $h->config->all());
    }

    /** A valid POST saves both settings, shows a success flash and redirects. */
    public function testValidPostSavesConfigFlashesAndRedirects(): void
    {
        $h = $this->settings(['api_key' => 'secret', 'base_url' => 'https://api.test/v3']);
        $h->controller->save();

        $this->assertSame('secret', $h->config->get('eventbrite.api_key'));
        $this->assertSame('https://api.test/v3', $h->config->get('eventbrite.base_url'));
        $this->assertSame([['success', 'EventbriteKit settings saved.']], $h->flashes);
        $this->assertSame(['/dashboard/eventbrite/settings'], $h->redirects);
        $this->assertFalse($h->error()->has());
    }

    /** save() throws UserMessageException when the package is missing. */
    public function testMissingPackageThrowsUserMessageException(): void
    {
        $h = $this->settings(['api_key' => 'k'], withPackage: false);

        $this->expectException(UserMessageException::class);
        $h->controller->save();
    }
}
