<?php
declare(strict_types = 1);

namespace PublicWhip\Services;

use PublicWhip\Entities\DivisionVoteSummary;

interface DivisionVoteSummaryServiceInterface
{
    /**
     * Get the division vote summary or null if not found.
     *
     * @param int $divisionId Division id we are fetching for.
     * @return DivisionVoteSummary|null
     */
    public function getForDivision(int $divisionId): ?DivisionVoteSummary;
}
