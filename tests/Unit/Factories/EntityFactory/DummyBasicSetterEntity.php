<?php
declare(strict_types = 1);

namespace PublicWhip\Tests\Unit\Factories\EntityFactory;

/**
 * DummyBasicEntity for testing.
 */
class DummyBasicSetterEntity
{
    /**
     * Dummy entry that can only be set by setter.
     *
     * @var int
     */
    private $value;

    /**
     * Sets a value after multiplying it by 3.
     *
     * @param int $value Input value.
     */
    public function setValue(int $value): void
    {
        $this->value = $value * 3;
    }

    /**
     * Dummy.
     *
     * @return int|null
     */
    public function getValue(): ?int
    {
        return $this->value;
    }
}
