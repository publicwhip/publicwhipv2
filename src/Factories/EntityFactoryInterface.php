<?php
declare(strict_types = 1);

namespace PublicWhip\Factories;

use Psr\Log\LoggerInterface;
use PublicWhip\Entities\DivisionEntity;

/**
 * Factories up entities.
 */
interface EntityFactoryInterface
{
    /**
     * @param LoggerInterface $logger The logger.
     */
    public function __construct(LoggerInterface $logger);

    /**
     * Build a division.
     *
     * @param array<string,string|int|float|object|bool>$data Data to build the entity with.
     * @return DivisionEntity
     */
    public function division(array $data): DivisionEntity;
}
