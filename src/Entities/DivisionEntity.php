<?php
declare(strict_types=1);

namespace PublicWhip\Entities;

/**
 * Class DivisionEntity
 * @package PublicWhip\Entities
 */
class DivisionEntity
{

    /**
     * @var int Id of the division.
     */
    private $id;

    /**
     * @var string YYYY-MM-DD Of the division
     */
    private $date;

    /**
     * @var int Number of the division.
     */
    private $number;

    /**
     * @var string Name of the division.
     */
    private $name;

    /**
     * @var string Source of the division.
     */
    private $sourceUrl;

    /**
     * @var string Text of the motion.
     */
    private $motion;

    /**
     * @var string House.
     */
    private $house;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDate(): string
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
    public function getName(): string
    {
        return $this->name;
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
    public function getMotion(): string
    {
        return $this->motion;
    }

    /**
     * @return string
     */
    public function getHouse(): string
    {
        return $this->house;
    }
}
