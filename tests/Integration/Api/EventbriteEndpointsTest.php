<?php

declare(strict_types=1);

namespace Eventbrite\Tests\Integration\Api;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Eventbrite\Tests\Support\Fixtures;
use Eventbrite\Tests\Support\ApiFactory;

/** Chains the endpoints the way the import does: me -> organisations -> events. */
final class EventbriteEndpointsTest extends TestCase
{
    public function testFullEndpointSequence(): void
    {
        $f = ApiFactory::make([
            new Response(200, [], Fixtures::raw('users_me')),
            new Response(200, [], Fixtures::raw('organizations')),
            new Response(200, [], Fixtures::raw('organizations')),
            new Response(200, [], Fixtures::raw('events')),
        ]);

        $me = json_decode($f->api->getMe()->getContent(), true);
        $orgs = json_decode($f->api->getOrganization()->getContent(), true);
        $events = json_decode($f->api->getEvents()->getContent(), true);

        $this->assertTrue($me['success']);
        $this->assertTrue($orgs['success']);
        $this->assertTrue($events['success']);
        $this->assertCount(2, $events['data']);
        $this->assertSame(0, $f->mock->count());

        $paths = array_map(static fn($h) => $h['request']->getUri()->getPath(), $f->history);
        $this->assertSame([
            '/v3/users/me',
            '/v3/users/me/organizations',
            '/v3/users/me/organizations',
            '/v3/organizations/9001/events',
        ], $paths);
    }

    public function testEveryRequestCarriesTheAuthHeader(): void
    {
        $f = ApiFactory::make([
            new Response(200, [], Fixtures::raw('organizations')),
            new Response(200, [], Fixtures::raw('events')),
        ]);
        $f->api->getEvents();

        foreach ($f->history as $entry) {
            $this->assertSame('Bearer test-token', $entry['request']->getHeaderLine('Authorization'));
        }
    }
}
