<?php
declare(strict_types = 1);

namespace PublicWhip\Tests\Unit\Factories;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use PublicWhip\Exceptions\Factories\BadDateTimeException;
use PublicWhip\Factories\DateTimeFactory;
use ReflectionException;

/**
 * WikiParserProviderTest.
 *
 * @coversDefaultClass \PublicWhip\Factories\DateTimeFactory
 * @covers \PublicWhip\Factories\DateTimeFactory::<!public>
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
final class DateTimeFactoryTest extends TestCase
{
    /**
     * @covers ::createImmutableFromFormat
     * @covers ::__construct
     * @throws ReflectionException
     */
    public function testCreateImmutableFromFormat(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $sut = new DateTimeFactory($logger);
        $timeZone = new DateTimeZone('UTC');
        $dateTime = $sut->createImmutableFromFormat('!Y-m-d', '2019-03-23');
        self::assertEquals($timeZone, $dateTime->getTimezone());
        $expected = DateTimeImmutable::createFromFormat('!Y-m-d', '2019-03-23', $timeZone);
        self::assertEquals($expected, $dateTime);
    }

    /**
     * @covers ::createImmutableFromFormat
     * @throws ReflectionException
     */
    public function testCreateImmutableFromFormatError(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $sut = new DateTimeFactory($logger);
        $expectedMessage = 'Failed to createImmutableFromFormat from !Y-m-d using the input tester : errors: ' .
            '"A four digit year could not be found", "Data missing" : ' .
            'warnings: [none]';
        $this->expectException(BadDateTimeException::class);
        $this->expectExceptionMessage($expectedMessage);
        $sut->createImmutableFromFormat('!Y-m-d', 'tester');
    }
}
