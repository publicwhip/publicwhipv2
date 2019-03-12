<?php
declare(strict_types=1);

namespace PublicWhip\Services;

use Psr\Log\LoggerInterface;
use PublicWhip\Entities\DivisionEntity;
use PublicWhip\Providers\DatabaseProviderInterface;
use PublicWhip\Providers\HydratorProviderInterface;
use ReflectionException;

/**
 * Class DivisionService.
 *
 * Reads/writes divisions.
 *
 * @package PublicWhip\Services
 */
interface DivisionServiceInterface
{
    /**
     * DivisionService constructor.
     *
     * @param DatabaseProviderInterface $databaseProvider The database system.
     * @param HydratorProviderInterface $hydrator Our entity hydrator.
     * @param LoggerInterface $logger A logger.
     */
    public function __construct(
        DatabaseProviderInterface $databaseProvider,
        HydratorProviderInterface $hydrator,
        LoggerInterface $logger
    );

    /**
     * Get the latest division date (usually used to ensure data is up to date).
     *
     * @return string
     */
    public function getNewestDivisionDate(): string;

    /**
     * Find a division by the house, date and division number.
     *
     * @param string $house Name of the house - probably 'commons','lords' or 'scotland'
     * @param string $date Date in YYYY-MM-DD of the division.
     * @param int $divisionNumber Number of the division.
     *
     * @return DivisionEntity|null Entity if found.
     * @throws ReflectionException
     */
    public function findByHouseDateAndNumber(string $house, string $date, int $divisionNumber): ?DivisionEntity;
}
