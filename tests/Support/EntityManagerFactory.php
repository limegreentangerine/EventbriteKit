<?php

declare(strict_types=1);

namespace EventbriteKit\Tests\Support;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use EventbriteKit\Entity\Event;
use Doctrine\ORM\Tools\SchemaTool;

final class EntityManagerFactory
{
    /** In-memory SQLite entity manager with the Event table created. */
    public static function create(): EntityManager
    {
        $config = Setup::createAnnotationMetadataConfiguration(
            [dirname(__DIR__, 2) . '/src/Entity'],
            true,
            null,
            null,
            false,
        );

        $em = EntityManager::create(['driver' => 'pdo_sqlite', 'memory' => true], $config);
        (new SchemaTool($em))->createSchema([$em->getClassMetadata(Event::class)]);

        return $em;
    }
}
