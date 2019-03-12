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
final class DivisionService implements DivisionServiceInterface
{

    /**
     * @var DatabaseProviderInterface $databaseProvider
     */
    private $databaseProvider;

    /**
     * @var HydratorProviderInterface $hydrator
     */
    private $hydrator;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

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
    ) {
        $this->databaseProvider = $databaseProvider;
        $this->logger = $logger;
        $this->hydrator = $hydrator;
    }

    /**
     * Get the latest division date (usually used to ensure data is up to date).
     *
     * @return string
     */
    public function getNewestDivisionDate(): string
    {
        return (string)$this->databaseProvider->table('pw_division')->max('division_date');
    }

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
    public function findByHouseDateAndNumber(string $house, string $date, int $divisionNumber): ?DivisionEntity
    {
        $data = $this->databaseProvider->table('pw_division')
            ->where('division_date', '=', $date)
            ->where('division_number', '=', $divisionNumber)
            ->where('house', '=', $house)
            ->first();
        if (null !== $data) {
            $this->logger->debug(
                __METHOD__ . ': Found {house} date {date} {divisionNumber}',
                [
                    'house' => $house,
                    'date' => $date,
                    'divisionNumber' => $divisionNumber,
                    'division' => $data
                ]
            );
            return $this->hydrator->hydrateInto(
                DivisionEntity::class,
                $data,
                $this->divisionTableToEntity()
            );
        }
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

    /**
     * Mapping for the hydrator.
     * @return array
     */
    private function divisionTableToEntity(): array
    {
        return [
            'division_id' => ['name' => 'id', 'type' => 'int'],
            'division_date' => 'date',
            'division_number' => ['name' => 'number', 'type' => 'int'],
            'division_name' => 'name',
            'motion' => 'motion',
            'house' => 'house'
        ];
    }
}
