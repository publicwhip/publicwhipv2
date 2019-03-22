<?php
declare(strict_types = 1);

namespace PublicWhip\Services;

use PublicWhip\Entities\HansardEntity;

/**
 * Reads/writes divisions.
 */
interface HansardServiceInterface
{
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
     * @return HansardEntity|null Null if not found.
     */
    public function findByDivisionId(int $divisionId): ?HansardEntity;

    /**
     * Find a division by the house, date and division number.
     *
     * @param string $house Name of the house - probably 'commons','lords' or 'scotland'
     * @param string $date Date in YYYY-MM-DD of the division.
     * @param int $divisionNumber Number of the division.
     * @return HansardEntity|null Entity if found.
     */
    public function findByHouseDateAndNumber(string $house, string $date, int $divisionNumber): ?HansardEntity;
}
