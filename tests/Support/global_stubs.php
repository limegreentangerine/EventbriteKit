<?php

declare(strict_types=1);

// Minimal stand-ins for the Concrete globals/facades the package relies on,
// so tests run without booting Concrete.

namespace {
    if (!function_exists('t')) {
        /** Translation stub: returns the text, sprintf-formatted when args are given. */
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

            /** Register the instance that make() returns for $abstract. */
            public static function bind(string $abstract, object $instance): void
            {
                self::$bindings[$abstract] = $instance;
            }

            /** Remove all bindings. */
            public static function reset(): void
            {
                self::$bindings = [];
            }

            /**
             * Get the instance bound for $abstract.
             *
             * @throws \RuntimeException When nothing is bound
             */
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

            /** Register the package that getByHandle() returns; null means not installed. */
            public static function register(string $handle, ?object $package): void
            {
                self::$packages[$handle] = $package;
            }

            /** Remove all registered packages. */
            public static function reset(): void
            {
                self::$packages = [];
            }

            /** Get a registered package, or null. */
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

            /** Set the entity manager that entityManager() returns; null clears it. */
            public static function setEntityManager(?\Doctrine\ORM\EntityManagerInterface $em): void
            {
                self::$em = $em;
            }

            /**
             * Get the current entity manager.
             *
             * @throws \RuntimeException When no entity manager is set
             */
            public static function entityManager(): \Doctrine\ORM\EntityManagerInterface
            {
                return self::$em ?? throw new \RuntimeException('ORM stub: no entity manager set');
            }
        }
    }
}
