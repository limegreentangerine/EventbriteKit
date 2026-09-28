<?php

declare(strict_types=1);

namespace Eventbrite\Tests\Support;

use Concrete\Core\Command\Task\Output\OutputInterface;

final class RecordingOutput implements OutputInterface
{
    /** @var list<string> */
    public array $lines = [];

    /** Record a line of output. */
    public function write($message): void
    {
        $this->lines[] = (string) $message;
    }

    /** Record an error line, stored with the normal output. */
    public function writeError($message): void
    {
        $this->lines[] = (string) $message;
    }
}
