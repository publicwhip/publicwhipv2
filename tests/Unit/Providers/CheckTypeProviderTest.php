<?php
declare(strict_types = 1);

namespace PublicWhip\Tests\Unit\Providers;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use PublicWhip\Exceptions\Providers\CheckTypeProviderWrongTypeException;
use PublicWhip\Factories\DateTimeFactoryInterface;
use PublicWhip\Providers\CheckTypeProvider;
use ReflectionException;
use stdClass;

/**
 * CheckTypeProviderTest.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @coversDefaultClass \PublicWhip\Providers\CheckTypeProvider
 * @covers \PublicWhip\Providers\CheckTypeProvider::<!public>
 */
final class CheckTypeProviderTest extends TestCase
{
    /**
     * @covers ::checkType
     * @throws ReflectionException
     */
    public function testCheckUnrecognised(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        /** @var DateTimeFactoryInterface $dateTime */
        $dateTime = $this->createMock(DateTimeFactoryInterface::class);
        $this->expectException(CheckTypeProviderWrongTypeException::class);
        $this->expectExceptionMessage('Bad definition found for test: found unknown type garbage');
        (new CheckTypeProvider($logger, $dateTime))
            ->checkType('test', 'garbage', 223);
    }

    /**
     * @covers ::__construct
     * @covers ::checkType
     * @throws ReflectionException
     */
    public function testCheckString(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        /** @var DateTimeFactoryInterface $dateTime */
        $dateTime = $this->createMock(DateTimeFactoryInterface::class);
        $out = (new CheckTypeProvider($logger, $dateTime))
            ->checkType('test', 'string', 'abc');
        self::assertIsString($out);
        self::assertSame('abc', $out);
    }

    /**
     * @covers ::checkType
     * @throws ReflectionException
     */
    public function testCheckStringInvalid(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        /** @var DateTimeFactoryInterface $dateTime */
        $dateTime = $this->createMock(DateTimeFactoryInterface::class);
        $this->expectException(CheckTypeProviderWrongTypeException::class);
        $this->expectExceptionMessage('Expected test to be of type string, but was int');
        (new CheckTypeProvider($logger, $dateTime))
            ->checkType('test', 'string', 223);
    }

    /**
     * @covers ::checkType
     * @throws ReflectionException
     */
    public function testCheckInt(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        /** @var DateTimeFactoryInterface $dateTime */
        $dateTime = $this->createMock(DateTimeFactoryInterface::class);
        $out = (new CheckTypeProvider($logger, $dateTime))
            ->checkType('test', 'int', 432);
        self::assertIsInt($out);
        self::assertSame(432, $out);
    }

    /**
     * @covers ::checkType
     * @throws ReflectionException
     */
    public function testCheckIntFromString(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        /** @var DateTimeFactoryInterface $dateTime */
        $dateTime = $this->createMock(DateTimeFactoryInterface::class);
        $out = (new CheckTypeProvider($logger, $dateTime))
            ->checkType('test', 'int', '432');
        self::assertIsInt($out);
        self::assertSame(432, $out);
    }

    /**
     * @covers ::checkType
     * @throws ReflectionException
     */
    public function testCheckIntFromStringFloat(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        /** @var DateTimeFactoryInterface $dateTime */
        $dateTime = $this->createMock(DateTimeFactoryInterface::class);
        $this->expectException(CheckTypeProviderWrongTypeException::class);
        $this->expectExceptionMessage('Expected test to be of type int, but was string');
        (new CheckTypeProvider($logger, $dateTime))
            ->checkType('test', 'int', '23.43');
    }

    /**
     * @covers ::checkType
     * @throws ReflectionException
     */
    public function testCheckIntInvalid(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        /** @var DateTimeFactoryInterface $dateTime */
        $dateTime = $this->createMock(DateTimeFactoryInterface::class);
        $this->expectException(CheckTypeProviderWrongTypeException::class);
        $this->expectExceptionMessage('Expected test to be of type int, but was stdClass');
        (new CheckTypeProvider($logger, $dateTime))
            ->checkType('test', 'int', new stdClass());
    }

    /**
     * @covers ::checkType
     * @throws ReflectionException
     */
    public function testCheckDate(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        /** @var DateTimeFactoryInterface $dateTime */
        $dateTime = $this->createMock(DateTimeFactoryInterface::class);
        $inputDate = new DateTimeImmutable();
        $out = (new CheckTypeProvider($logger, $dateTime))
            ->checkType('test', 'date', $inputDate);
        self::assertSame($inputDate, $out);
    }

    /**
     * @covers ::checkType
     * @throws ReflectionException
     */
    public function testCheckDateFromYmd(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $expectedDate = new DateTimeImmutable();
        $dateTime = $this->createMock(DateTimeFactoryInterface::class);
        $dateTime->method('createImmutableFromFormat')->with('!Y-m-d', '2014-03-22')
            ->willReturn($expectedDate);
        /** @var DateTimeFactoryInterface $dateTime */
        $sut = new CheckTypeProvider($logger, $dateTime);

        /** @var DateTimeImmutable $out */
        $out = $sut->checkType('test', 'date', '2014-03-22');
        self::assertSame($expectedDate, $out);
    }

    /**
     * @covers ::checkType
     * @throws ReflectionException
     */
    public function testCheckDateFromYmdHms(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $expectedDate = new DateTimeImmutable();
        $dateTime = $this->createMock(DateTimeFactoryInterface::class);
        $dateTime->method('createImmutableFromFormat')
            ->with('!Y-m-d H:i:s', '2014-03-22 21:23:43')
            ->willReturn($expectedDate);
        /** @var DateTimeFactoryInterface $dateTime */
        $sut = new CheckTypeProvider($logger, $dateTime);

        /** @var DateTimeImmutable $out */
        $out = $sut->checkType('test', 'date', '2014-03-22 21:23:43');
        self::assertSame($expectedDate, $out);
    }

    /**
     * @covers ::checkType
     * @throws ReflectionException
     */
    public function testCheckDateInvalid(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        /** @var DateTimeFactoryInterface $dateTime */
        $dateTime = $this->createMock(DateTimeFactoryInterface::class);
        $this->expectException(CheckTypeProviderWrongTypeException::class);
        $this->expectExceptionMessage('Expected test to be of type date, but was string');
        (new CheckTypeProvider($logger, $dateTime))
            ->checkType('test', 'date', 'hello');
    }
}
