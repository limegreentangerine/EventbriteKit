<?php

declare(strict_types=1);

namespace EventbriteKit\Tests\Support;

final class FakeConfig
{
    /** @param array<string, mixed> $values */
    public function __construct(private array $values = []) {}

    /** Get a config value, or $default when unset. */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values[$key] ?? $default;
    }

    /** Store a config value in memory. */
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
