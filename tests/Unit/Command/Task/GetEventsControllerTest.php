<?php

declare(strict_types=1);

namespace Eventbrite\Tests\Unit\Command\Task;

use PHPUnit\Framework\TestCase;
use Eventbrite\Command\Task\Controller\GetEventsController;

final class GetEventsControllerTest extends TestCase
{
    /** The task has the expected name and description. */
    public function testNameAndDescription(): void
    {
        $c = new GetEventsController();
        $this->assertSame('Get Eventbrite events', $c->getName());
        $this->assertStringContainsString('Eventbrite', $c->getDescription());
    }

    /** The controller source contains no copy-pasted text naming another product. */
    public function testRunnerMessageDoesNotMentionAnotherProduct(): void
    {
        $src = (string) file_get_contents(dirname(__DIR__, 4) . '/src/Command/Task/Controller/GetEventsController.php');
        $this->assertStringNotContainsString('Cipher iRecruit', $src);
    }
}
