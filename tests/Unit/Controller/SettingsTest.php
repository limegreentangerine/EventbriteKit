<?php

declare(strict_types=1);

namespace Eventbrite\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use Concrete\Core\Error\UserMessageException;
use Eventbrite\Tests\Support\MakesSettingsHarness;

final class SettingsTest extends TestCase
{
    use MakesSettingsHarness;

    public function testNonPostRedirectsWithoutSaving(): void
    {
        $h = $this->settings([], isPost: false);
        $h->controller->save();

        $this->assertSame(['/dashboard/eventbrite/settings'], $h->redirects);
        $this->assertSame([], $h->config->all());
    }

    public function testEmptyApiKeyIsRejectedAndFormRepopulated(): void
    {
        $h = $this->settings(['api_key' => '  ', 'base_url' => 'https://x.test']);
        $h->controller->save();

        $this->assertTrue($h->error()->has());
        $this->assertSame([], $h->config->all());
        $this->assertSame([], $h->redirects);
        $this->assertSame(['api_key' => '  ', 'base_url' => 'https://x.test'], $h->vars['formContent']);
    }

    public function testInvalidCsrfTokenBlocksSave(): void
    {
        $h = $this->settings(['api_key' => 'k', 'base_url' => 'https://x.test'], csrfValid: false);
        $h->controller->save();

        $this->assertTrue($h->error()->has());
        $this->assertSame([], $h->config->all());
    }

    public function testValidPostSavesConfigFlashesAndRedirects(): void
    {
        $h = $this->settings(['api_key' => 'secret', 'base_url' => 'https://api.test/v3']);
        $h->controller->save();

        $this->assertSame('secret', $h->config->get('eventbrite.api_key'));
        $this->assertSame('https://api.test/v3', $h->config->get('eventbrite.base_url'));
        $this->assertSame([['success', 'Eventbrite settings saved.']], $h->flashes);
        $this->assertSame(['/dashboard/eventbrite/settings'], $h->redirects);
        $this->assertFalse($h->error()->has());
    }

    public function testMissingPackageThrowsUserMessageException(): void
    {
        $h = $this->settings(['api_key' => 'k'], withPackage: false);

        $this->expectException(UserMessageException::class);
        $h->controller->save();
    }
}
