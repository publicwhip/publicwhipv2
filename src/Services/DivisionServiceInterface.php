<?php
declare(strict_types = 1);

namespace PublicWhip\Services;

use Psr\Log\LoggerInterface;
use PublicWhip\Entities\DivisionEntity;
use PublicWhip\Factories\DateTimeFactoryInterface;
use PublicWhip\Factories\EntityFactoryInterface;
use PublicWhip\Providers\DatabaseProviderInterface;
use PublicWhip\Providers\WikiParserProviderInterface;
use ReflectionException;

/**
 * Reads/writes divisions.
 */
interface DivisionServiceInterface
{
    /**
     *  Setup the division service.
     *
     * @param DatabaseProviderInterface $databaseProvider Database connection layer.
     * @param EntityFactoryInterface $entityFactory Entity factory.
     * @param DateTimeFactoryInterface $dateTimeFactory Date time factory.
     * @param WikiParserProviderInterface $wikiParser Wiki parser.
     * @param LoggerInterface $logger Logger.
     */
    public function __construct(
        DatabaseProviderInterface $databaseProvider,
        EntityFactoryInterface $entityFactory,
        DateTimeFactoryInterface $dateTimeFactory,
        WikiParserProviderInterface $wikiParser,
        LoggerInterface $logger
    );

    /**
     * Get the latest division date (usually used to ensure data is up to date).
     *
     * @return string In YYYY-MM-DD format.
     */
    public function getNewestDivisionDate(): string;

    /**
     * Find a division by its numerical id.
     *
     * @param int $divisionId Numerical id of the division.
     * @return DivisionEntity|null Null if not found.
     */
    public function findByDivisionId(int $divisionId): ?DivisionEntity;

    /**
     * Find a division by the house, date and division number.
     *
     * @param string $house Name of the house - probably 'commons','lords' or 'scotland'
     * @param string $date Date in YYYY-MM-DD of the division.
     * @param int $divisionNumber Number of the division.
     * @return DivisionEntity|null Entity if found.
     * @throws ReflectionException
     */
    public function findByHouseDateAndNumber(string $house, string $date, int $divisionNumber): ?DivisionEntity;
}
