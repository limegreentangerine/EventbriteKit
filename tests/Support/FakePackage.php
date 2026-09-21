<?php

declare(strict_types=1);

namespace Eventbrite\Tests\Support;

final class FakePackage
{
    public function __construct(private FakeConfig $config) {}

    public function getFileConfig(): FakeConfig
    {
        return $this->config;
    }
}
