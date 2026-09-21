<?php

declare(strict_types=1);

namespace Eventbrite\Tests\Support;

use Concrete\Core\Command\Task\Output\OutputInterface;

final class RecordingOutput implements OutputInterface
{
    /** @var list<string> */
    public array $lines = [];

    public function write($message): void
    {
        $this->lines[] = (string) $message;
    }

    public function writeError($message): void
    {
        $this->lines[] = (string) $message;
    }
}
