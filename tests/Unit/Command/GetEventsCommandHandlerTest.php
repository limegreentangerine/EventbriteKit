<?php

declare(strict_types=1);

namespace Eventbrite\Tests\Unit\Command;

use Eventbrite\Api\Eventbrite;
use PHPUnit\Framework\TestCase;
use Eventbrite\Log\EventbriteLogger;
use Doctrine\ORM\EntityManagerInterface;
use Eventbrite\Command\GetEventsCommand;
use Eventbrite\Tests\Support\FakeConfig;
use Eventbrite\Tests\Support\FakePackage;
use Eventbrite\Tests\Support\RecordingLogger;
use Eventbrite\Tests\Support\RecordingOutput;
use Eventbrite\Command\GetEventsCommandHandler;
use Symfony\Component\HttpFoundation\JsonResponse;

final class GetEventsCommandHandlerTest extends TestCase
{
    private RecordingLogger $logger;
    private RecordingOutput $output;

    /** Build a handler whose API returns $apiResponse, using the given entity manager or a mock. */
    private function handler(JsonResponse $apiResponse, ?EntityManagerInterface $em = null): GetEventsCommandHandler
    {
        \Core::bind(Eventbrite::class, new class($apiResponse) {
            public function __construct(private JsonResponse $r) {}
            public function getEvents(): JsonResponse
            {
                return $this->r;
            }
        });
        $handler = new GetEventsCommandHandler($em ?? $this->createMock(EntityManagerInterface::class));
        $handler->setOutput($this->output);

        return $handler;
    }

    /** The handler throws when the eventbrite package isn't installed. */
    public function testThrowsWhenPackageNotInstalled(): void
    {
        \Package::register('eventbrite', null);
        $handler = $this->handler(new JsonResponse([]));

        $this->expectException(\RuntimeException::class);
        $handler(new GetEventsCommand());
    }

    /** A non-200 API response is logged and written to output, and nothing is persisted. */
    public function testNonOkStatusIsLoggedAndNothingPersisted(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('persist');
        $handler = $this->handler(new JsonResponse(['x' => 1], 502), $em);

        $handler(new GetEventsCommand());

        $this->assertCount(1, $this->logger->messages('error'));
        $this->assertNotEmpty($this->output->lines);
    }

    /** An API result with success: false reports 'API did not connect.' */
    public function testUnsuccessfulApiResultReportsConnectionFailure(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('persist');
        $handler = $this->handler(new JsonResponse(['success' => false, 'message' => 'bad']), $em);

        $handler(new GetEventsCommand());

        $this->assertSame(['API did not connect.'], $this->logger->messages('error'));
        $this->assertSame(['API did not connect.'], $this->output->lines);
    }

    /** A database error rolls back, is logged and is rethrown. */
    public function testDatabaseFailureRollsBackLogsAndRethrows(): void
    {
        $conn = $this->createMock(\Doctrine\DBAL\Connection::class);
        $conn->method('executeStatement')->willThrowException(new \RuntimeException('db down'));
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($conn);
        $em->expects($this->once())->method('rollback');
        $em->expects($this->never())->method('commit');

        $handler = $this->handler(new JsonResponse(['success' => true, 'data' => []]), $em);

        try {
            $handler(new GetEventsCommand());
            $this->fail('Expected exception');
        } catch (\RuntimeException $e) {
            $this->assertSame('db down', $e->getMessage());
        }
        $this->assertStringContainsString('db down', $this->logger->messages('error')[0]);
    }

    /** An empty event list commits and reports zero processed. */
    public function testEmptyEventListCompletesWithZeroProcessed(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($this->createMock(\Doctrine\DBAL\Connection::class));
        $em->expects($this->once())->method('commit');

        $handler = $this->handler(new JsonResponse(['success' => true, 'data' => []]), $em);
        $handler(new GetEventsCommand());

        $this->assertSame(['Event import complete. Processed: 0'], $this->output->lines);
    }

    /** Register a fake package and a recording logger and output. */
    protected function setUp(): void
    {
        \Package::register('eventbrite', new FakePackage(new FakeConfig()));
        $this->logger = new RecordingLogger();
        $this->output = new RecordingOutput();
        \Core::bind(EventbriteLogger::class, $this->logger);
    }

    /** Clear the Core and Package stubs. */
    protected function tearDown(): void
    {
        \Core::reset();
        \Package::reset();
    }
}
