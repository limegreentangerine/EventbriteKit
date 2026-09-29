<?php

declare(strict_types=1);

namespace EventbriteKit\Tests\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use EventbriteKit\Api\EventbriteKit;
use GuzzleHttp\Handler\MockHandler;
use ClassKit\Api\ConnectionController;

/**
 * Builds a real EventbriteKit API client without Concrete: the constructor's facade calls
 * are bypassed, the ClassKit constructor still runs, and Guzzle is backed by a MockHandler.
 */
final class ApiFactory
{
    /** @var list<array<string, mixed>> Guzzle history (request/response pairs) */
    public array $history = [];

    public MockHandler $mock;

    public EventbriteKit $api;

    /** @param list<\GuzzleHttp\Psr7\Response|\Throwable> $queue */
    public static function make(array $queue, ?FakeConfig $config = null): self
    {
        $config ??= new FakeConfig([
            'EventbriteKit.base_url' => 'https://www.EventbriteKitapi.com/v3',
            'EventbriteKit.api_key' => 'test-token',
        ]);

        $self = new self();
        $self->mock = new MockHandler($queue);
        $stack = HandlerStack::create($self->mock);
        $stack->push(Middleware::history($self->history));

        $api = (new \ReflectionClass(EventbriteKit::class))->newInstanceWithoutConstructor();

        $props = [
            'pkg' => new FakePackage($config),
            'config' => $config,
            'rf' => new FakeResponseFactory(),
            'logger' => new RecordingLogger(),
            'client' => new Client(['handler' => $stack]),
        ];
        foreach ($props as $name => $value) {
            $prop = new \ReflectionProperty(
                $name === 'client' ? ConnectionController::class : EventbriteKit::class,
                $name,
            );
            $prop->setValue($api, $value);
        }

        // Run the real ClassKit constructor (base URL, JSON format, headers) but keep our client.
        $client = $props['client'];
        (new \ReflectionMethod(ConnectionController::class, '__construct'))->invoke(
            $api,
            $config->get('EventbriteKit.base_url'),
            'json',
            ['Authorization' => 'Bearer ' . $config->get('EventbriteKit.api_key')],
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
