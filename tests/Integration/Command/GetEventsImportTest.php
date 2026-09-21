<?php

declare(strict_types=1);

namespace Eventbrite\Tests\Integration\Command;

use Eventbrite\Entity\Event;
use GuzzleHttp\Psr7\Response;
use Eventbrite\Api\Eventbrite;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Eventbrite\Log\EventbriteLogger;
use Eventbrite\Tests\Support\Fixtures;
use Eventbrite\Command\GetEventsCommand;
use Eventbrite\Tests\Support\ApiFactory;
use Eventbrite\Tests\Support\FakeConfig;
use Eventbrite\Tests\Support\FakePackage;
use Eventbrite\Tests\Support\RecordingLogger;
use Eventbrite\Tests\Support\RecordingOutput;
use Eventbrite\Command\GetEventsCommandHandler;
use Eventbrite\Tests\Support\EntityManagerFactory;

/** Real handler + real API client (mocked HTTP) + real Doctrine on in-memory SQLite. */
final class GetEventsImportTest extends TestCase
{
    private EntityManager $em;
    private RecordingLogger $logger;
    private RecordingOutput $output;

    /** @param list<Response> $responses */
    private function runImport(array $responses): void
    {
        \Core::bind(Eventbrite::class, ApiFactory::make($responses)->api);
        $handler = new GetEventsCommandHandler($this->em);
        $handler->setOutput($this->output);
        $handler(new GetEventsCommand());
    }

    /** @return list<Response> */
    private function happyResponses(): array
    {
        return [
            new Response(200, [], Fixtures::raw('organizations')),
            new Response(200, [], Fixtures::raw('events')),
        ];
    }

    private function eventsById(): array
    {
        $this->em->clear();
        $out = [];
        foreach ($this->em->getRepository(Event::class)->findAll() as $e) {
            $out[$e->getEventbriteId()] = $e;
        }

        return $out;
    }

    public function testImportsLiveEventsWithMappedFields(): void
    {
        $this->runImport($this->happyResponses());
        $events = $this->eventsById();

        $this->assertSame(['evt-early', 'evt-late'], array_keys($events) === ['evt-late', 'evt-early'] ? ['evt-early', 'evt-late'] : array_keys($events));
        $late = $events['evt-late'];
        $this->assertSame('Late Event', $late->getName());
        $this->assertSame('https://eventbrite.test/late', $late->getUrl());
        $this->assertSame('Town Hall', $late->getVenue());
        $this->assertSame('Later summary', $late->getDescription());
        $this->assertSame('https://img.test/late.png', $late->getImage());
        $this->assertSame('2099-06-02 19:00:00', $late->getStartDateFormatted());
        $this->assertSame('2099-06-02 21:00:00', $late->getEndDateFormatted());
        $this->assertNull($events['evt-early']->getImage(), 'Missing logo must import as null');
        $this->assertSame(['Event import complete. Processed: 2'], $this->output->lines);
    }

    public function testDraftEventsAreNotImported(): void
    {
        $this->runImport($this->happyResponses());

        $this->assertArrayNotHasKey('evt-draft', $this->eventsById());
    }

    public function testRerunUpdatesInsteadOfDuplicating(): void
    {
        $this->runImport($this->happyResponses());
        $this->runImport($this->happyResponses());

        $this->assertCount(2, $this->eventsById());
    }

    public function testExpiredEventsAreDeleted(): void
    {
        $old = new Event();
        $old->setEventbriteId('evt-old')->setName('Old')->setUrl('https://old.test')->setVenue(null)
            ->setStartDate(new \DateTimeImmutable('2001-01-01 10:00:00'))
            ->setEndDate(new \DateTimeImmutable('2001-01-01 12:00:00'))
            ->setDescription(null)->setImage(null);
        $this->em->persist($old);
        $this->em->flush();

        $this->runImport($this->happyResponses());

        $this->assertArrayNotHasKey('evt-old', $this->eventsById());
        $this->assertCount(2, $this->eventsById());
    }

    public function testApiFailureImportsNothingAndReports(): void
    {
        $this->runImport([new Response(401, [], Fixtures::raw('error_401'))]);

        $this->assertCount(0, $this->eventsById());
        $this->assertNotEmpty($this->logger->messages('error'));
    }

    protected function setUp(): void
    {
        $this->em = EntityManagerFactory::create();
        \ORM::setEntityManager($this->em);
        \Package::register('eventbrite', new FakePackage(new FakeConfig()));
        $this->logger = new RecordingLogger();
        $this->output = new RecordingOutput();
        \Core::bind(EventbriteLogger::class, $this->logger);
    }

    protected function tearDown(): void
    {
        \ORM::setEntityManager(null);
        \Core::reset();
        \Package::reset();
    }
}
