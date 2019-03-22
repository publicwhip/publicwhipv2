<?php
declare(strict_types = 1);

namespace PublicWhip\Services;

use Psr\Log\LoggerInterface;
use PublicWhip\Entities\HansardEntity;
use PublicWhip\Factories\EntityFactoryInterface;
use PublicWhip\Providers\DatabaseProviderInterface;

/**
 * Reads/writes divisions.
 */
final class HansardService implements HansardServiceInterface
{
    /**
     *  The entity factory.
     *
     * @var EntityFactoryInterface
     */
    private $entityFactory;

    /**
     *  The database layer.
     *
     * @var DatabaseProviderInterface $databaseProvider
     */
    private $databaseProvider;

    /**
     *  The logger.
     *
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * Setup.
     *
     * @param DatabaseProviderInterface $databaseProvider Database connection layer.
     * @param EntityFactoryInterface $entityFactory Entity factory.
     * @param LoggerInterface $logger Logger.
     */
    public function __construct(
        DatabaseProviderInterface $databaseProvider,
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger
    ) {
        $this->databaseProvider = $databaseProvider;
        $this->entityFactory = $entityFactory;
        $this->logger = $logger;
    }

    /**
     * Get the latest division date (usually used to ensure data is up to date).
     *
     * @return string In YYYY-MM-DD format.
     */
    public function getNewestDivisionDate(): string
    {
        return (string)$this->databaseProvider->table('pw_division')->max('division_date');
    }

    /**
     * Find a division by its numerical id.
     *
     * @param int $divisionId Numerical id of the division.
     * @return HansardEntity|null Null if not found.
     */
    public function findByDivisionId(int $divisionId): ?HansardEntity
    {
        /**
         * Get the basic division data.
         *
         * @var object|null $basicDivision
         */
        $basicDivision = $this->databaseProvider->table('pw_division')
            ->select([
                'division_id AS id',
                'division_date As date',
                'division_number AS number',
                'source_url AS sourceUrl',
                'debate_url AS debateUrl',
                'division_name AS title',
                'motion AS text',
                'house'
            ])
            ->where('division_id', '=', $divisionId)
            ->first();

        if (null === $basicDivision) {
            $this->logger->debug(
                __METHOD__ . ': Did not find by id {id}',
                [
                    'id' => $divisionId
                ]
            );

            return null;
        }

        return $this->entityFactory->hansardEntry(get_object_vars($basicDivision));
    }

    /**
     * Find a division by the house, date and division number.
     *
     * @param string $house Name of the house - probably 'commons','lords' or 'scotland'
     * @param string $date Date in YYYY-MM-DD of the division.
     * @param int $divisionNumber Number of the division.
     * @return HansardEntity|null Entity if found.
     */
    public function findByHouseDateAndNumber(string $house, string $date, int $divisionNumber): ?HansardEntity
    {
        /**
         * Get the basic division data.
         *
         * @var object|null $basicDivision
         */
        $basicDivision = $this->databaseProvider->table('pw_division')
            ->select([
                'division_id AS id',
                'division_date As date',
                'division_number AS number',
                'source_url AS sourceUrl',
                'debate_url AS debateUrl',
                'division_name AS title',
                'motion AS text',
                'house'
            ])
            ->where('division_date', '=', $date)
            ->where('division_number', '=', $divisionNumber)
            ->where('house', '=', $house)
            ->first();

        if (null === $basicDivision) {
            $this->logger->debug(
                __METHOD__ . ': Did not find in {house} date {date} {divisionNumber}',
                [
                    'house' => $house,
                    'date' => $date,
                    'divisionNumber' => $divisionNumber
                ]
            );

            return null;
        }

        return $this->entityFactory->hansardEntry(get_object_vars($basicDivision));
    }
}
