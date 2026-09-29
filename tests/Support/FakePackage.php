<?php

declare(strict_types=1);

namespace EventbriteKit\Tests\Support;

final class FakePackage
{
    /** Wrap the config that getFileConfig() returns. */
    public function __construct(private FakeConfig $config) {}

    /** Get the package's file config. */
    public function getFileConfig(): FakeConfig
    {
        return $this->config;
    }
}
