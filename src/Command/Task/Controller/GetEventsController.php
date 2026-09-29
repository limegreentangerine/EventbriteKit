<?php

namespace EventbriteKit\Command\Task\Controller;

use Concrete\Core\Command\Batch\Batch;
use Concrete\Core\Command\Task\TaskInterface;
use Concrete\Core\Command\Task\Input\InputInterface;
use Concrete\Core\Command\Task\Controller\AbstractController;
use EventbriteKit\Command\GetEventsCommand as CommandGetEventsCommand;
use Concrete\Core\Command\Task\Runner\{BatchProcessTaskRunner, TaskRunnerInterface};

class GetEventsController extends AbstractController
{
    /**
     * Task name shown in the dashboard.
     */
    public function getName(): string
    {
        return t('Get Eventbrite events');
    }

    /**
     * Task description shown in the dashboard.
     */
    public function getDescription(): string
    {
        return t('Gets all live events from the Eventbrite account');
    }

    /**
     * Queue a single GetEventsCommand in a batch runner, with no PHP time limit.
     */
    public function getTaskRunner(TaskInterface $task, InputInterface $input): TaskRunnerInterface
    {
        set_time_limit(0);
        $batch = new Batch();
        $batch->add(new CommandGetEventsCommand());
        return new BatchProcessTaskRunner($task, $batch, $input, t('Updating events from Eventbrite'));
    }
}
