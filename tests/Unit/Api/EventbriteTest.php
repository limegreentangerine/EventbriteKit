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
    /** Build a mock JSON response from a fixture file. */
    private function json(string $fixture, int $status = 200): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], Fixtures::raw($fixture));
    }

    /** Requests use the configured base URL and bearer token. */
    public function testConnectionUsesConfiguredBaseUrlAndBearerToken(): void
    {
        $f = ApiFactory::make([$this->json('users_me')]);
        $f->api->getMe();

        $request = $f->lastRequest();
        $this->assertSame('https://www.eventbriteapi.com/v3/users/me', (string) $request->getUri());
        $this->assertSame('Bearer test-token', $request->getHeaderLine('Authorization'));
    }

    /** A custom base URL and API key are used instead of the defaults. */
    public function testCustomConfigIsUsed(): void
    {
        $config = new FakeConfig(['eventbrite.base_url' => 'https://example.test/api', 'eventbrite.api_key' => 'abc']);
        $f = ApiFactory::make([$this->json('users_me')], $config);
        $f->api->getMe();

        $this->assertSame('https://example.test/api/users/me', (string) $f->lastRequest()->getUri());
        $this->assertSame('Bearer abc', $f->lastRequest()->getHeaderLine('Authorization'));
    }

    /** getMe() returns a 200 response as success, with the body in data. */
    public function testGetMeReturnsSuccessWithData(): void
    {
        $f = ApiFactory::make([$this->json('users_me')]);
        $body = json_decode($f->api->getMe()->getContent(), true);

        $this->assertTrue($body['success']);
        $this->assertSame('111', $body['data']['id']);
    }

    /** Params passed to getMe() are added to the query string. */
    public function testGetMeAppendsQueryParams(): void
    {
        $f = ApiFactory::make([$this->json('users_me')]);
        $f->api->getMe(['expand' => 'x']);

        $this->assertSame('expand=x', $f->lastRequest()->getUri()->getQuery());
    }

    /** A 401 response gives success: false with a message. */
    public function testGetMeReportsFailureOnUnauthorised(): void
    {
        $f = ApiFactory::make([$this->json('error_401', 401)]);
        $body = json_decode($f->api->getMe()->getContent(), true);

        $this->assertFalse($body['success']);
        $this->assertArrayHasKey('message', $body);
    }

    /** getOrganization() calls /users/me/organizations and returns its data. */
    public function testGetOrganizationHitsCorrectEndpoint(): void
    {
        $f = ApiFactory::make([$this->json('organizations')]);
        $body = json_decode($f->api->getOrganization()->getContent(), true);

        $this->assertSame('/v3/users/me/organizations', $f->lastRequest()->getUri()->getPath());
        $this->assertTrue($body['success']);
        $this->assertSame('9001', $body['data']['organizations'][0]['id']);
    }

    /** A 500 response gives success: false. */
    public function testGetOrganizationReportsFailureOnServerError(): void
    {
        $f = ApiFactory::make([new Response(500, [], '{"error":"boom"}')]);
        $body = json_decode($f->api->getOrganization()->getContent(), true);

        $this->assertFalse($body['success']);
    }

    /** A connection failure gives success: false instead of throwing. */
    public function testGetOrganizationReportsFailureOnTransportError(): void
    {
        $f = ApiFactory::make([new \GuzzleHttp\Exception\ConnectException('down', new \GuzzleHttp\Psr7\Request('GET', '/'))]);
        $body = json_decode($f->api->getOrganization()->getContent(), true);

        $this->assertFalse($body['success']);
    }

    /** getEvents() requests the first organisation's events with expand=venue. */
    public function testGetEventsUsesOrganisationIdAndExpandsVenue(): void
    {
        $f = ApiFactory::make([$this->json('organizations'), $this->json('events')]);
        $f->api->getEvents();

        $this->assertSame('/v3/organizations/9001/events', $f->lastRequest()->getUri()->getPath());
        $this->assertStringContainsString('expand=venue', $f->lastRequest()->getUri()->getQuery());
    }

    /** getEvents() drops events that aren't live and sorts the rest by start time. */
    public function testGetEventsReturnsOnlyLiveEventsSortedByStart(): void
    {
        $f = ApiFactory::make([$this->json('organizations'), $this->json('events')]);
        $body = json_decode($f->api->getEvents()->getContent(), true);

        $this->assertTrue($body['success']);
        $this->assertSame(['evt-early', 'evt-late'], array_column($body['data'], 'id'));
    }

    /** An error from the events request gives success: false. */
    public function testGetEventsFailsWhenEventsRequestFails(): void
    {
        $f = ApiFactory::make([$this->json('organizations'), $this->json('error_401', 401)]);
        $body = json_decode($f->api->getEvents()->getContent(), true);

        $this->assertFalse($body['success']);
    }

    /** An account with no organisations gives success: false without requesting events. */
    public function testGetEventsFailsCleanlyWhenThereAreNoOrganisations(): void
    {
        $f = ApiFactory::make([$this->json('organizations_empty')]);
        $body = json_decode($f->api->getEvents()->getContent(), true);

        $this->assertFalse($body['success']);
    }
}
