<?php

declare(strict_types=1);

namespace EventbriteKit\Tests\Support;

use Symfony\Component\HttpFoundation\JsonResponse;

final class FakeResponseFactory
{
    /** Build a JSON response, like Concrete's ResponseFactory::json(). */
    public function json(mixed $data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status);
    }
}
