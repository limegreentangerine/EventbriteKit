<?php

declare(strict_types=1);

namespace EventbriteKit\Tests\Support;

final class Fixtures
{
    /** Read a fixture from tests/fixtures/EventbriteKit as a raw JSON string. */
    public static function raw(string $name): string
    {
        return (string) file_get_contents(dirname(__DIR__) . "/fixtures/EventbriteKit/{$name}.json");
    }

    /** @return array<mixed> */
    public static function json(string $name): array
    {
        return json_decode(self::raw($name), true, flags: JSON_THROW_ON_ERROR);
    }
}
