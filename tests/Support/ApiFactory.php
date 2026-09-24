<?php

declare(strict_types=1);

namespace Eventbrite\Tests\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use Eventbrite\Api\Eventbrite;
use GuzzleHttp\Handler\MockHandler;
use ClassKit\Api\ConnectionController;

/**
 * Builds a real Eventbrite API client without Concrete: the constructor's facade calls
 * are bypassed, the ClassKit constructor still runs, and Guzzle is backed by a MockHandler.
 */
final class ApiFactory
{
    /** @var list<array<string, mixed>> Guzzle history (request/response pairs) */
    public array $history = [];

    public MockHandler $mock;

    public Eventbrite $api;

    /** @param list<\GuzzleHttp\Psr7\Response|\Throwable> $queue */
    public static function make(array $queue, ?FakeConfig $config = null): self
    {
        $config ??= new FakeConfig([
            'eventbrite.base_url' => 'https://www.eventbriteapi.com/v3',
            'eventbrite.api_key' => 'test-token',
        ]);

        $self = new self();
        $self->mock = new MockHandler($queue);
        $stack = HandlerStack::create($self->mock);
        $stack->push(Middleware::history($self->history));

        $api = (new \ReflectionClass(Eventbrite::class))->newInstanceWithoutConstructor();

        $props = [
            'pkg' => new FakePackage($config),
            'config' => $config,
            'rf' => new FakeResponseFactory(),
            'logger' => new RecordingLogger(),
            'client' => new Client(['handler' => $stack]),
        ];
        foreach ($props as $name => $value) {
            $prop = new \ReflectionProperty(
                $name === 'client' ? ConnectionController::class : Eventbrite::class,
                $name,
            );
            $prop->setValue($api, $value);
        }

        // Run the real ClassKit constructor (base URL, JSON format, headers) but keep our client.
        $client = $props['client'];
        (new \ReflectionMethod(ConnectionController::class, '__construct'))->invoke(
            $api,
            $config->get('eventbrite.base_url'),
            'json',
            ['Authorization' => 'Bearer ' . $config->get('eventbrite.api_key')],
        );
        (new \ReflectionProperty(ConnectionController::class, 'client'))->setValue($api, $client);

        $self->api = $api;

        return $self;
    }

    /** The most recent request sent through the mock handler. */
    public function lastRequest(): \Psr\Http\Message\RequestInterface
    {
        return $this->history[array_key_last($this->history)]['request'];
    }
}
