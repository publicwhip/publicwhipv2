<?php
declare(strict_types=1);

namespace PublicWhip\Providers;

use Psr\Log\LoggerInterface;
use ReflectionException;

/**
 * Class HydratorProvider.
 *
 * Hydrates entities.
 *
 * @package PublicWhip\Providers
 */
interface HydratorProviderInterface
{
    /**
     * HydratorProvider constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger);

    /**
     * Logger.
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface;

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void;

    /**
     * Hydrate an object into a entity.
     *
     * @param string $entityClassName The name of the entity class we are hydrating.
     * @param object $object The data object returned from the database.
     * @param array $mapping The mapping that should be used.
     * @return mixed An instance of the entityClass.
     * @throws ReflectionException
     */
    public function hydrateInto(string $entityClassName, $object, array $mapping);
}
