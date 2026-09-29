<?php

namespace EventbriteKit\Command;

use Core;
use Package;
use DateTime;
use EventbriteKit\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Concrete\Core\Command\Task\Output\OutputAwareTrait;
use Concrete\Core\Command\Task\Output\OutputAwareInterface;

class GetEventsCommandHandler implements OutputAwareInterface
{
    use OutputAwareTrait;

    private EntityManagerInterface $entityManager;
    private $logger;

    /**
     * Inject the entity manager used to persist imported events.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Import live events from EventbriteKit.
     *
     * Upserts each event, then deletes events whose end date has passed, in a single transaction.
     * API failures are logged and written to the task output rather than thrown.
     *
     * @throws \RuntimeException When the EventbriteKit package isn't installed
     * @throws \Throwable        Rethrown after rollback when the database write fails
     */
    public function __invoke(GetEventsCommand $command): void
    {
        $pkg = Package::getByHandle('EventbriteKit');

        if ($pkg === null) {
            throw new \RuntimeException('Package EventbriteKit is not installed.');
        }

        $this->logger = Core::make(\EventbriteKit\Log\EventbriteKitLogger::class)->getLogger();

        $api = Core::make(\EventbriteKit\Api\EventbriteKit::class);
        $events = $api->getEvents();

        $processed = 0;

        if ($events->getStatusCode() !== 200) {
            $message = sprintf('Events did not import. Error message: %s', $events->getStatusCode());
            $this->output->write($message);
            $this->logger->error($message);
        } else {
            $response = json_decode($events->getContent(), true);
            if (!$response['success']) {
                $message = 'API did not connect.';
                $this->output->write($message);
                $this->logger->error($message);
            } else {
                $allEvents = $response['data'];
                foreach ($allEvents as $key => $data) {
                    $logo = null;
                    if (is_array($data['logo'] ?? null)) {
                        if (array_key_exists('original', $data['logo'])) {
                            $logo = $data['logo']['original']['url'] ?? null;
                        }
                    }
                    $evData = [
                        'EventbriteKitId' => $data['id'],
                        'name' => $data['name']['text'],
                        'url' => $data['url'],
                        'venue' => is_array($data['venue'] ?? null) ? ($data['venue']['name'] ?? null) : null,
                        'startDate' => new \DateTimeImmutable($data['start']['local']),
                        'endDate' => new \DateTimeImmutable($data['end']['local']),
                        'description' => $data['summary'] ?? null,
                        'image' => $logo,
                    ];
                    $event = Event::createOrUpdate($evData);
                    $this->entityManager->persist($event);
                    $processed++;
                }
                $conn = $this->entityManager->getConnection();
                $this->entityManager->beginTransaction();
                try {
                    $currentDate = new DateTime();
                    $conn->executeStatement(
                        'DELETE FROM `EventbriteKit_events` WHERE `endDate` < :expiryDate',
                        ['expiryDate' => $currentDate->format('Y-m-d H:i:s')],
                    );
                    $this->entityManager->flush();
                    $this->entityManager->commit();

                } catch (\Throwable $e) {
                    $this->entityManager->rollback();
                    $this->logger->error(sprintf('Events import failed. Message: %s', $e->getMessage()));
                    throw $e;
                }
                $message = sprintf('Event import complete. Processed: %d', $processed);
                $this->output->write($message);
                $this->logger->info($message);
            }
        }
    }
}
