<?php

declare(strict_types=1);

namespace Eventbrite\Tests\Unit\Entity;

use Eventbrite\Entity\Event;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

final class EventTest extends TestCase
{
    private function data(): array
    {
        return [
            'eventbriteId' => 'e1',
            'name' => 'Name',
            'url' => 'https://x.test',
            'venue' => 'Hall',
            'startDate' => new \DateTimeImmutable('2030-01-02 10:00:00'),
            'endDate' => new \DateTimeImmutable('2030-01-02 12:30:00'),
            'description' => 'Desc',
            'image' => null,
        ];
    }

    private function emReturning(mixed $existing): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn($existing);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);
        \ORM::setEntityManager($em);
    }

    public function testCreateOrUpdateBuildsNewEventWhenNoneExists(): void
    {
        $this->emReturning(null);
        $event = Event::createOrUpdate($this->data());

        $this->assertSame('e1', $event->getEventbriteId());
        $this->assertSame('Name', $event->getName());
        $this->assertSame('https://x.test', $event->getUrl());
        $this->assertSame('Hall', $event->getVenue());
        $this->assertSame('Desc', $event->getDescription());
        $this->assertNull($event->getImage());
    }

    public function testFormattedDates(): void
    {
        $this->emReturning(null);
        $event = Event::createOrUpdate($this->data());

        $this->assertSame('2030-01-02 10:00:00', $event->getStartDateFormatted());
        $this->assertSame('12:30', $event->getEndDateFormatted('H:i'));
    }
    protected function tearDown(): void
    {
        \ORM::setEntityManager(null);
    }
}
