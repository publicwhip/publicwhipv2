<?php
declare(strict_types = 1);

namespace PublicWhip\Entities;

use DateTimeImmutable;

/**
 * DivisionEntity.
 *
 * A division is the details of the motion - when it was, which house etc.
 */
final class DivisionEntity
{
    /**
     * Id of the division.
     *
     * @var int
     */
    private $divisionId;

    /**
     * Date of the division.
     *
     * @var DateTimeImmutable
     */
    private $date;

    /**
     * Number of the division on that date.
     *
     * @var int
     */
    private $number;

    /**
     * Source of the division.
     *
     * @var string
     */
    private $sourceUrl;

    /**
     * Url of the debate.
     *
     * @var string
     */
    private $debateUrl;

    /**
     * Text of the motion.
     *
     * @var string
     */
    private $motionText;

    /**
     * Title of the motion.
     *
     * @var string
     */
    private $motionTitle;

    /**
     * Original text of the motion/division.
     *
     * @var string
     */
    private $originalMotionText;

    /**
     * Original title of the motion/division.
     *
     * @var string
     */
    private $originalMotionTitle;

    /**
     * House.
     *
     * @var string
     */
    private $house;

    /**
     * Number of rebellions.
     *
     * @var int|null
     */
    private $rebellions;

    /**
     * Total number of votes.
     *
     * @var int|null
     */
    private $turnout;

    /**
     * Number of possible votes.
     *
     * @var int|null
     */
    private $possibleTurnout;

    /**
     * Majority of the 'ayes'. May be negative.
     *
     * @var int|null
     */
    private $ayeMajority;

    /**
     * Get the division id.
     *
     * @return int
     */
    public function getDivisionId(): int
    {
        return $this->divisionId;
    }

    /**
     * Get the date of the division.
     *
     * @return DateTimeImmutable
     */
    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * Get the number of the division for this day.
     *
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * Get the source url.
     *
     * @return string
     */
    public function getSourceUrl(): string
    {
        return $this->sourceUrl;
    }

    /**
     * Get the debate url.
     *
     * @return string
     */
    public function getDebateUrl(): string
    {
        return $this->debateUrl;
    }

    /**
     * Get the motion text.
     *
     * @return string
     */
    public function getMotionText(): string
    {
        return $this->motionText;
    }

    /**
     * Get the motion title.
     *
     * @return string
     */
    public function getMotionTitle(): string
    {
        return $this->motionTitle;
    }

    /**
     * Get the original (imported) motion text.
     *
     * @return string
     */
    public function getOriginalMotionText(): string
    {
        return $this->originalMotionText;
    }

    /**
     * Get the original (imported) motion title.
     *
     * @return string
     */
    public function getOriginalMotionTitle(): string
    {
        return $this->originalMotionTitle;
    }

    /**
     * Which house was this division in?
     *
     * @return string
     */
    public function getHouse(): string
    {
        return $this->house;
    }

    /**
     * How many rebellions (in total) were then? May be null if not yet compiled.
     *
     * @return int|null
     */
    public function getRebellions(): ?int
    {
        return $this->rebellions;
    }

    /**
     * What was the total turnout/votes cast? May be null if not yet compiled.
     *
     * @return int|null
     */
    public function getTurnout(): ?int
    {
        return $this->turnout;
    }

    /**
     * Get the total possible turnout (registered MPs etc) on that date. May be null if not yet compiled.
     *
     * @return int|null
     */
    public function getPossibleTurnout(): ?int
    {
        return $this->possibleTurnout;
    }

    /**
     * Get the majority of the ayes. May be negative if the noes won. May be null if not yet compiled.
     *
     * @return int|null
     */
    public function getAyeMajority(): ?int
    {
        return $this->ayeMajority;
    }
}
