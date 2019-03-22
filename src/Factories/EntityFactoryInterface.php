<?php
declare(strict_types = 1);

namespace PublicWhip\Factories;

use PublicWhip\Entities\DivisionVoteSummary;
use PublicWhip\Entities\HansardEntity;
use PublicWhip\Entities\MotionEntity;

/**
 * Factories up entities.
 */
interface EntityFactoryInterface
{
    /**
     * Build a HansardEntry.
     *
     * @param array<string,string|int|float|object|bool> $data Data to build the entity with.
     * @return HansardEntity
     */
    public function hansardEntry(array $data): HansardEntity;

    /**
     * Builds a division summary vote.
     *
     * @param array<string,string|int|float|object|bool> $data Data to build the entity with.
     * @return DivisionVoteSummary
     */
    public function divisionVoteSummary(array $data): DivisionVoteSummary;

    /**
     * Builds a motion.
     *
     * @param array<string,string|int|float|object|bool> $data Data to build the entity with.
     * @return MotionEntity
     */
    public function motion(array $data): MotionEntity;
}
