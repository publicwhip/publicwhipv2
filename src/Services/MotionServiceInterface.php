<?php
declare(strict_types = 1);

namespace PublicWhip\Services;

use PublicWhip\Entities\MotionEntity;

/**
 * Reads/writes motions.
 *
 * These are stored in the database as 'wiki' entries.
 */
interface MotionServiceInterface
{
    /**
     * Get the latest motion text for a division.
     *
     * @param int $divisionId The division id.
     * @return MotionEntity
     */
    public function getLatestForDivision(int $divisionId) : MotionEntity;
}
