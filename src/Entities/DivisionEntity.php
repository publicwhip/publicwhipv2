<?php
declare(strict_types=1);

namespace PublicWhip\Entities;

use DateTimeImmutable;

/**
 * Class DivisionEntity
 * @package PublicWhip\Entities
 */
final class DivisionEntity extends AbstractEntity
{

    /**
     * @var int Id of the division.
     */
    private $id;

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
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getSourceUrl(): string
    {
        return $this->sourceUrl;
    }

    /**
     * @return string
     */
    public function getDebateUrl(): string
    {
        return $this->debateUrl;
    }

    /**
     * @return string
     */
    public function getMotionText(): string
    {
        return $this->motionText;
    }

    /**
     * @return string
     */
    public function getMotionTitle(): string
    {
        return $this->motionTitle;
    }

    /**
     * @return string
     */
    public function getOriginalMotionText(): string
    {
        return $this->originalMotionText;
    }

    /**
     * @return string
     */
    public function getOriginalMotionTitle(): string
    {
        return $this->originalMotionTitle;
    }
    /**
     * @return string
     */
    public function getHouse(): string
    {
        return $this->house;
    }

    /**
     * @return int|null
     */
    public function getRebellions(): ?int
    {
        return $this->rebellions;
    }

    /**
     * @return int|null
     */
    public function getTurnout(): ?int
    {
        return $this->turnout;
    }

    /**
     * @return int|null
     */
    public function getPossibleTurnout(): ?int
    {
        return $this->possibleTurnout;
    }

    /**
     * @return int|null
     */
    public function getAyeMajority(): ?int
    {
        return $this->ayeMajority;
    }


    /**
     * Returns an associated array of properties the entity requires.
     *
     * @return array
     */
    protected function requiredPropertiesMapping(): array
    {
        return [
            'id' => 'int',
            'date' => DateTimeImmutable::class,
            'number' => 'int',
            'sourceUrl' => 'string',
            'debateUrl' => 'string',
            'motionText' => 'string',
            'house' => 'string',
            'motionTitle' => 'string',
            'originalMotionText' => 'string',
            'originalMotionTitle' => 'string'
        ];
    }

    /**
     * Returns an optional associated array of properties this entity can deal with.
     *
     * @return array
     */
    protected function optionalPropertiesMapping(): array
    {
        return [
            'rebellions' => 'int',
            'turnout' => 'int',
            'possibleTurnout' => 'int',
            'ayeMajority' => 'int'
        ];
    }
}
