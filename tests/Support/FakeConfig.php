<?php

declare(strict_types=1);

namespace Eventbrite\Tests\Support;

final class FakeConfig
{
    /** @param array<string, mixed> $values */
    public function __construct(private array $values = []) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values[$key] ?? $default;
    }

    public function save(string $key, mixed $value): void
    {
        $this->values[$key] = $value;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->values;
    }
}
