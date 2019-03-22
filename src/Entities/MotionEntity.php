<?php
declare(strict_types=1);

namespace PublicWhip\Entities;

use DateTimeInterface;

class MotionEntity
{
    /**
     * Id for this entry. Null if not a saved entry.
     *
     * @var int|null
     */
    private $id;

    /**
     * Division id.
     *
     * @var int
     */
    private $divisionId;


    /**
     * Title of the motion.
     *
     * @var string
     */
    private $title;

    /**
     * Title of the motion
     *
     * @var string
     */
    private $motion;

    /**
     * Comments of the motion
     *
     * @var string
     */
    private $comments;

    /**
     * People who voted aye voted for ... summary
     *
     * @var string
     */
    private $ayeSummary;

    /**
     * People who voted no/noes voted for ... summary
     *
     * @var string
     */
    private $noeSummary;

    /**
     * Who was the last user to edit this.
     *
     * @var int|null
     */
    private $lastEditedByUserId;

    /**
     * The date of the last edit.
     *
     * @var DateTimeInterface|null
     */
    private $lastEditDateTime;

    /**
     * Get the id.
     *
     * @return int|null Null if not saved.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Which division do we relate to.
     *
     * @return int
     */
    public function getDivisionId(): int
    {
        return $this->divisionId;
    }

    /**
     * Get the motion title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get the motion text.
     *
     * @return string
     */
    public function getMotion(): string
    {
        return $this->motion;
    }

    /**
     * Get any comments/notes.
     *
     * @return string
     */
    public function getComments(): string
    {
        return $this->comments;
    }

    /**
     * Get the summary text of 'yes'/aye voters.
     *
     * @return string
     */
    public function getAyeSummary(): string
    {
        return $this->ayeSummary;
    }

    /**
     * Get the summary text for 'noe' voters.
     *
     * @return string
     */
    public function getNoeSummary(): string
    {
        return $this->noeSummary;
    }

    /**
     * Who was this last edited by.
     *
     * @return int|null Null if not edited.
     */
    public function getLastEditedByUserId(): ?int
    {
        return $this->lastEditedByUserId;
    }

    /**
     * When was this last editing.
     *
     * @return DateTimeInterface|null Null if not edited.
     */
    public function getLastEditDateTime(): ?DateTimeInterface
    {
        return $this->lastEditDateTime;
    }
}
