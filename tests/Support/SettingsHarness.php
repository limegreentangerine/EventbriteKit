<?php

declare(strict_types=1);

namespace Eventbrite\Tests\Support;

use PHPUnit\Framework\TestCase;
use Concrete\Core\Error\ErrorList\ErrorList;
use Concrete\Package\Eventbrite\Controller\SinglePage\Dashboard\Eventbrite\Settings;

/** Builds a Settings controller with fake request/token/app so save() runs without Concrete. */
final class SettingsHarness
{
    public Settings $controller;
    public FakeConfig $config;
    /** @var list<string> */
    public array $redirects = [];
    /** @var list<array{string, string}> */
    public array $flashes = [];
    /** @var array<string, mixed> */
    public array $vars = [];

    /** Set a property on the nearest class in the hierarchy that declares it; otherwise set it as a dynamic property. */
    private static function setProp(object $obj, string $name, mixed $value): void
    {
        $ref = new \ReflectionObject($obj);
        while ($ref && !$ref->hasProperty($name)) {
            $ref = $ref->getParentClass();
        }
        if ($ref === false || $ref === null) {
            $obj->{$name} = $value;
            return;
        }
        $prop = $ref->getProperty($name);
        $prop->setValue($obj, $value);
    }

    /**
     * @param array<string, mixed> $post
     */
    public static function make(TestCase $test, object $mock, array $post, bool $isPost = true, bool $csrfValid = true, bool $withPackage = true): self
    {
        $h = new self();
        $h->config = new FakeConfig();

        $mock->method('buildRedirect')->willReturnCallback(function (string $to) use ($h) {
            $h->redirects[] = $to;
            return new class {
                public function send(): string
                {
                    return 'redirected';
                }
            };
        });
        $mock->method('flash')->willReturnCallback(function ($k, $v) use ($h) {
            $h->flashes[] = [$k, $v];
        });
        $mock->method('set')->willReturnCallback(function ($k, $v) use ($h) {
            $h->vars[$k] = $v;
        });

        $request = new class($post, $isPost) {
            public function __construct(private array $post, private bool $isPost) {}
            public function isPost(): bool
            {
                return $this->isPost;
            }
            public function request(?string $key = null): mixed
            {
                return $key === null ? $this->post : ($this->post[$key] ?? null);
            }
        };
        $token = new class($csrfValid) {
            public function __construct(private bool $valid) {}
            public function validate(string $action = ''): bool
            {
                return $this->valid;
            }
            public function getErrorMessage(): string
            {
                return 'Invalid form token';
            }
        };
        $app = new class {
            public function make(string $abstract): object
            {
                return new class {
                    public function notempty($v): bool
                    {
                        return is_string($v) && trim($v) !== '';
                    }
                };
            }
        };

        self::setProp($mock, 'request', $request);
        self::setProp($mock, 'token', $token);
        self::setProp($mock, 'app', $app);
        self::setProp($mock, 'error', new ErrorList());
        self::setProp($mock, 'pkg', $withPackage ? new FakePackage($h->config) : null);

        $h->controller = $mock;

        return $h;
    }

    /** Get the controller's protected error list. */
    public function error(): ErrorList
    {
        $p = new \ReflectionProperty(Settings::class, 'error');
        return $p->getValue($this->controller);
    }
}
