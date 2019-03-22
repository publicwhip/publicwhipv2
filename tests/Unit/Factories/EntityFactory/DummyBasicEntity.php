<?php
declare(strict_types=1);

namespace PublicWhip\Tests\Unit\Factories\EntityFactory;

/**
 * DummyBasicEntity for testing.
 */
class DummyBasicEntity
{
    /**
     * Dummy entry.
     *
     * @var string|null
     */
    private $abc;
    /**
     * Dummy entry.
     *
     * @var string|null
     */
    private $def;

    /**
     * Dummy.
     *
     * @return string|null
     */
    public function getAbc() : ?string
    {
        return $this->abc;
    }

    /**
     * Dummy.
     *
     * @return string|null
     */
    public function getDef() : ?string
    {
        return $this->def;
    }
}
