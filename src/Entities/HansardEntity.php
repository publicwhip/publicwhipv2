<?php
declare(strict_types = 1);

namespace PublicWhip\Entities;

use DateTimeImmutable;

/**
 * HansardEntity.
 *
 * The raw details fo a division as loaded from Hansard.
 */
final class HansardEntity
{
    /**
     * Id of the division.
     *
     * @var int
     */
    private $id;

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
    private $text;

    /**
     * Title of the motion.
     *
     * @var string
     */
    private $title;

    /**
     * House.
     *
     * @var string
     */
    private $house;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getSourceUrl(): string
    {
        return $this->sourceUrl;
    }

    public function getDebateUrl(): string
    {
        return $this->debateUrl;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getHouse(): string
    {
        return $this->house;
    }
}
