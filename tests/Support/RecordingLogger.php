<?php

declare(strict_types=1);

namespace Eventbrite\Tests\Support;

/** Records log calls; doubles as the EventbriteLogger wrapper (getLogger()). */
final class RecordingLogger
{
    /** @var list<array{string, string}> */
    public array $records = [];

    public function getLogger(): self
    {
        return $this;
    }

    public function error(string $message): void
    {
        $this->records[] = ['error', $message];
    }

    public function info(string $message): void
    {
        $this->records[] = ['info', $message];
    }

    /** @return list<string> */
    public function messages(string $level): array
    {
        return array_values(array_map(
            static fn(array $r) => $r[1],
            array_filter($this->records, static fn(array $r) => $r[0] === $level),
        ));
    }
}
