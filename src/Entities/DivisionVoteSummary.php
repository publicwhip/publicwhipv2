<?php
declare(strict_types = 1);

namespace PublicWhip\Entities;

class DivisionVoteSummary
{
    /**
     * The division id.
     *
     * @var int
     */
    private $divisionId;

    /**
     * Number of rebellions.
     *
     * @var int
     */
    private $rebellions;

    /**
     * Number of tellers.
     *
     * @var int
     */
    private $tellers;

    /**
     * Total turnout.
     *
     * @var int
     */
    private $turnout;

    /**
     * Total possible turnout.
     *
     * @var int
     */
    private $possibleTurnout;

    /**
     * Aye majority - could be negative.
     *
     * @var int
     */
    private $ayeMajority;

    public function getDivisionId(): int
    {
        return $this->divisionId;
    }

    public function getRebellions(): int
    {
        return $this->rebellions;
    }

    public function getTellers(): int
    {
        return $this->tellers;
    }

    public function getTurnout(): int
    {
        return $this->turnout;
    }

    public function getPossibleTurnout(): int
    {
        return $this->possibleTurnout;
    }

    public function getAyeMajority(): int
    {
        return $this->ayeMajority;
    }
}
