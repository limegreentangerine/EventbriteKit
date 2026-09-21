<?php

namespace Eventbrite\Command\Task\Controller;

use Concrete\Core\Command\Batch\Batch;
use Concrete\Core\Command\Task\TaskInterface;
use Concrete\Core\Command\Task\Input\InputInterface;
use Concrete\Core\Command\Task\Controller\AbstractController;
use Eventbrite\Command\GetEventsCommand as CommandGetEventsCommand;
use Concrete\Core\Command\Task\Runner\{BatchProcessTaskRunner, TaskRunnerInterface};

class GetEventsController extends AbstractController
{
    public function getName(): string
    {
        return t('Get Eventbrite events');
    }

    public function getDescription(): string
    {
        return t('Gets all live events from the Eventbrite account');
    }

    public function getTaskRunner(TaskInterface $task, InputInterface $input): TaskRunnerInterface
    {
        set_time_limit(0);
        $batch = new Batch();
        $batch->add(new CommandGetEventsCommand());
        return new BatchProcessTaskRunner($task, $batch, $input, t('Updating events from Eventbrite'));
    }
}
