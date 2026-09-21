<?php

declare(strict_types=1);

namespace Eventbrite\Tests\Support;

use Symfony\Component\HttpFoundation\JsonResponse;

final class FakeResponseFactory
{
    public function json(mixed $data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status);
    }
}
