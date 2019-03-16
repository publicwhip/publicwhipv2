<?php
declare(strict_types=1);

namespace PublicWhip\Entities;

use DateTimeImmutable;

/**
 * Class DivisionEntity
 */
final class DivisionEntity
{

    /**
     * @var int Id of the division.
     */
    private $divisionId;

    /**
     * @var DateTimeImmutable
     */
    private $date;

    /**
     * @var int Number of the division.
     */
    private $number;

    /**
     * @var string Source of the division.
     */
    private $sourceUrl;

    /**
     * @var string Url of the debate.
     */
    private $debateUrl;

    /**
     * @var string Text of the motion.
     */
    private $motionText;

    /**
     * @var string Title of the motion.
     */
    private $motionTitle;

    /**
     * @var string Original text of the motion/division.
     */
    private $originalMotionText;

    /**
     * @var string Original title of the motion/division.
     */
    private $originalMotionTitle;

    /**
     * @var string House.
     */
    private $house;

    /**
     * @var int|null Number of rebellions.
     */
    private $rebellions;

    /**
     * @var int|null Total number of votes.
     */
    private $turnout;

    /**
     * @var int|null Number of possible votes.
     */
    private $possibleTurnout;

    /**
     * @var int|null Majority of the 'ayes'. May be negative.
     */
    private $ayeMajority;

    /**
     * Get the division id.
     * @return int
     */
    public function getDivisionId(): int
    {
        return $this->divisionId;
    }

    /**
     * Get the date of the division.
     * @return DateTimeImmutable
     */
    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * Get the number of the division for this day.
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * Get the source url.
     * @return string
     */
    public function getSourceUrl(): string
    {
        return $this->sourceUrl;
    }

    /**
     * Get the debate url.
     * @return string
     */
    public function getDebateUrl(): string
    {
        return $this->debateUrl;
    }

    /**
     * Get the motion text.
     * @return string
     */
    public function getMotionText(): string
    {
        return $this->motionText;
    }

    /**
     * Get the motion title.
     * @return string
     */
    public function getMotionTitle(): string
    {
        return $this->motionTitle;
    }

    /**
     * Get the original (imported) motion text.
     * @return string
     */
    public function getOriginalMotionText(): string
    {
        return $this->originalMotionText;
    }

    /**
     * Get the original (imported) motion title.
     * @return string
     */
    public function getOriginalMotionTitle(): string
    {
        return $this->originalMotionTitle;
    }

    /**
     * Which house was this division in?
     * @return string
     */
    public function getHouse(): string
    {
        return $this->house;
    }

    /**
     * How many rebellions (in total) were then? May be null if not yet compiled.
     * @return int|null
     */
    public function getRebellions(): ?int
    {
        return $this->rebellions;
    }

    /**
     * What was the total turnout/votes cast? May be null if not yet compiled.
     * @return int|null
     */
    public function getTurnout(): ?int
    {
        return $this->turnout;
    }

    /**
     * Get the total possible turnout (registered MPs etc) on that date. May be null if not yet compiled.
     * @return int|null
     */
    public function getPossibleTurnout(): ?int
    {
        return $this->possibleTurnout;
    }

    /**
     * Get the majority of the ayes. May be negative if the noes won. May be null if not yet compiled.
     * @return int|null
     */
    public function getAyeMajority(): ?int
    {
        return $this->ayeMajority;
    }
}
