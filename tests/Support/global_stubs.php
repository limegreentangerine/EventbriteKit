<?php

declare(strict_types=1);

// Minimal stand-ins for the Concrete globals/facades the package relies on,
// so tests run without booting Concrete.

namespace {
    if (!function_exists('t')) {
        function t(string $text, mixed ...$args): string
        {
            return $args === [] ? $text : vsprintf($text, $args);
        }
    }

    if (!class_exists('Core')) {
        /** Facade stand-in: tests bind instances by class name. */
        class Core
        {
            /** @var array<string, object> */
            private static array $bindings = [];

            public static function bind(string $abstract, object $instance): void
            {
                self::$bindings[$abstract] = $instance;
            }

            public static function reset(): void
            {
                self::$bindings = [];
            }

            public static function make(string $abstract): object
            {
                return self::$bindings[$abstract]
                    ?? throw new \RuntimeException("Core stub: nothing bound for {$abstract}");
            }
        }
    }

    if (!class_exists('Package')) {
        class Package
        {
            /** @var array<string, object|null> */
            private static array $packages = [];

            public static function register(string $handle, ?object $package): void
            {
                self::$packages[$handle] = $package;
            }

            public static function reset(): void
            {
                self::$packages = [];
            }

            public static function getByHandle(string $handle): ?object
            {
                return self::$packages[$handle] ?? null;
            }
        }
    }

    if (!class_exists('ORM')) {
        class ORM
        {
            private static ?\Doctrine\ORM\EntityManagerInterface $em = null;

            public static function setEntityManager(?\Doctrine\ORM\EntityManagerInterface $em): void
            {
                self::$em = $em;
            }

            public static function entityManager(): \Doctrine\ORM\EntityManagerInterface
            {
                return self::$em ?? throw new \RuntimeException('ORM stub: no entity manager set');
            }
        }
    }
}
