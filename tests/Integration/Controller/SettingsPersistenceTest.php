<?php

declare(strict_types=1);

namespace Eventbrite\Tests\Integration\Controller;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Eventbrite\Tests\Support\ApiFactory;
use Eventbrite\Tests\Support\MakesSettingsHarness;

/** Settings saved through the controller are what the API client then connects with. */
final class SettingsPersistenceTest extends TestCase
{
    use MakesSettingsHarness;

    /** The API client uses the API key and base URL saved through Settings. */
    public function testSavedSettingsDriveTheApiConnection(): void
    {
        $h = $this->settings(['api_key' => 'saved-key', 'base_url' => 'https://saved.test/v9']);
        $h->controller->save();

        $f = ApiFactory::make([new Response(200, [], '{"id":"1"}')], $h->config);
        $f->api->getMe();

        $this->assertSame('https://saved.test/v9/users/me', (string) $f->lastRequest()->getUri());
        $this->assertSame('Bearer saved-key', $f->lastRequest()->getHeaderLine('Authorization'));
    }
}
