<?php

declare(strict_types=1);

namespace Eventbrite\Tests\Unit\Api;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Eventbrite\Tests\Support\Fixtures;
use Eventbrite\Tests\Support\ApiFactory;
use Eventbrite\Tests\Support\FakeConfig;

final class EventbriteTest extends TestCase
{
    private function json(string $fixture, int $status = 200): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], Fixtures::raw($fixture));
    }

    public function testConnectionUsesConfiguredBaseUrlAndBearerToken(): void
    {
        $f = ApiFactory::make([$this->json('users_me')]);
        $f->api->getMe();

        $request = $f->lastRequest();
        $this->assertSame('https://www.eventbriteapi.com/v3/users/me', (string) $request->getUri());
        $this->assertSame('Bearer test-token', $request->getHeaderLine('Authorization'));
    }

    public function testCustomConfigIsUsed(): void
    {
        $config = new FakeConfig(['eventbrite.base_url' => 'https://example.test/api', 'eventbrite.api_key' => 'abc']);
        $f = ApiFactory::make([$this->json('users_me')], $config);
        $f->api->getMe();

        $this->assertSame('https://example.test/api/users/me', (string) $f->lastRequest()->getUri());
        $this->assertSame('Bearer abc', $f->lastRequest()->getHeaderLine('Authorization'));
    }

    public function testGetMeReturnsSuccessWithData(): void
    {
        $f = ApiFactory::make([$this->json('users_me')]);
        $body = json_decode($f->api->getMe()->getContent(), true);

        $this->assertTrue($body['success']);
        $this->assertSame('111', $body['data']['id']);
    }

    public function testGetMeAppendsQueryParams(): void
    {
        $f = ApiFactory::make([$this->json('users_me')]);
        $f->api->getMe(['expand' => 'x']);

        $this->assertSame('expand=x', $f->lastRequest()->getUri()->getQuery());
    }

    public function testGetMeReportsFailureOnUnauthorised(): void
    {
        $f = ApiFactory::make([$this->json('error_401', 401)]);
        $body = json_decode($f->api->getMe()->getContent(), true);

        $this->assertFalse($body['success']);
        $this->assertArrayHasKey('message', $body);
    }

    public function testGetOrganizationHitsCorrectEndpoint(): void
    {
        $f = ApiFactory::make([$this->json('organizations')]);
        $body = json_decode($f->api->getOrganization()->getContent(), true);

        $this->assertSame('/v3/users/me/organizations', $f->lastRequest()->getUri()->getPath());
        $this->assertTrue($body['success']);
        $this->assertSame('9001', $body['data']['organizations'][0]['id']);
    }

    public function testGetOrganizationReportsFailureOnServerError(): void
    {
        $f = ApiFactory::make([new Response(500, [], '{"error":"boom"}')]);
        $body = json_decode($f->api->getOrganization()->getContent(), true);

        $this->assertFalse($body['success']);
    }

    public function testGetOrganizationReportsFailureOnTransportError(): void
    {
        $f = ApiFactory::make([new \GuzzleHttp\Exception\ConnectException('down', new \GuzzleHttp\Psr7\Request('GET', '/'))]);
        $body = json_decode($f->api->getOrganization()->getContent(), true);

        $this->assertFalse($body['success']);
    }

    public function testGetEventsUsesOrganisationIdAndExpandsVenue(): void
    {
        $f = ApiFactory::make([$this->json('organizations'), $this->json('events')]);
        $f->api->getEvents();

        $this->assertSame('/v3/organizations/9001/events', $f->lastRequest()->getUri()->getPath());
        $this->assertStringContainsString('expand=venue', $f->lastRequest()->getUri()->getQuery());
    }

    public function testGetEventsReturnsOnlyLiveEventsSortedByStart(): void
    {
        $f = ApiFactory::make([$this->json('organizations'), $this->json('events')]);
        $body = json_decode($f->api->getEvents()->getContent(), true);

        $this->assertTrue($body['success']);
        $this->assertSame(['evt-early', 'evt-late'], array_column($body['data'], 'id'));
    }

    public function testGetEventsFailsWhenEventsRequestFails(): void
    {
        $f = ApiFactory::make([$this->json('organizations'), $this->json('error_401', 401)]);
        $body = json_decode($f->api->getEvents()->getContent(), true);

        $this->assertFalse($body['success']);
    }

    public function testGetEventsFailsCleanlyWhenThereAreNoOrganisations(): void
    {
        $f = ApiFactory::make([$this->json('organizations_empty')]);
        $body = json_decode($f->api->getEvents()->getContent(), true);

        $this->assertFalse($body['success']);
    }
}
