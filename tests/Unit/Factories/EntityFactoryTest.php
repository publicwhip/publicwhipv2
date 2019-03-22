<?php
declare(strict_types = 1);

namespace PublicWhip\Tests\Unit\Factories;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use PublicWhip\Entities\HansardEntity;
use PublicWhip\Entities\MotionEntity;
use PublicWhip\Exceptions\Factories\EntityFactoryUnrecognisedFieldException;
use PublicWhip\Exceptions\Factories\EntityMissingRequiredFieldException;
use PublicWhip\Factories\EntityFactory;
use PublicWhip\Providers\CheckTypeProviderInterface;
use PublicWhip\Tests\Unit\Factories\EntityFactory\DummyBasicEntity;
use PublicWhip\Tests\Unit\Factories\EntityFactory\DummyBasicSetterEntity;
use ReflectionException;

/**
 * WikiParserProviderTest.
 *
 * @coversDefaultClass \PublicWhip\Factories\EntityFactory
 * @covers \PublicWhip\Factories\EntityFactory::<!public>
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class EntityFactoryTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::buildFromArray
     * @throws ReflectionException
     */
    public function testBuildFromArray(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $checkTypeProvider = $this->createMock(CheckTypeProviderInterface::class);
        $checkTypeProvider->method('checkType')->will(self::returnArgument(2));
        $sut = new EntityFactory($logger, $checkTypeProvider);
        $data = [
            'abc' => 'test',
            'def' => 'words'
        ];
        $out = $sut->buildFromArray($data, DummyBasicEntity::class, ['abc' => 'string'], ['def' => 'string']);
        self::assertInstanceOf(DummyBasicEntity::class, $out);
        /** @var DummyBasicEntity $out */
        self::assertSame('test', $out->getAbc());
        self::assertSame('words', $out->getDef());
    }

    /**
     * @covers ::buildFromArray
     * @throws ReflectionException
     */
    public function testBuildFromArrayWithSetter(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $checkTypeProvider = $this->createMock(CheckTypeProviderInterface::class);
        $checkTypeProvider->method('checkType')->will(self::returnArgument(2));
        $sut = new EntityFactory($logger, $checkTypeProvider);
        $data = [
            'value' => 15
        ];
        $out = $sut->buildFromArray($data, DummyBasicSetterEntity::class, ['value' => 'int']);
        self::assertInstanceOf(DummyBasicSetterEntity::class, $out);
        /** @var DummyBasicSetterEntity $out */
        self::assertSame(45, $out->getValue());
    }

    /**
     * @covers ::buildFromArray
     * @throws ReflectionException
     */
    public function testBuildFromArrayMissingRequiredField(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $checkTypeProvider = $this->createMock(CheckTypeProviderInterface::class);
        $checkTypeProvider->method('checkType')->will(self::returnArgument(2));
        $sut = new EntityFactory($logger, $checkTypeProvider);
        $data = [
            'abc' => 'test',
            'def' => 'words'
        ];
        $this->expectException(EntityMissingRequiredFieldException::class);
        $this->expectExceptionMessage('Missing required field dummy when building ' . DummyBasicEntity::class);
        $sut->buildFromArray(
            $data,
            DummyBasicEntity::class,
            ['abc' => 'string', 'dummy' => 'here'],
            ['def' => 'string']
        );
    }

    /**
     * @covers ::buildFromArray
     * @throws ReflectionException
     */
    public function testBuildFromArrayTooManyFields(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $checkTypeProvider = $this->createMock(CheckTypeProviderInterface::class);
        $checkTypeProvider->method('checkType')->will(self::returnArgument(2));
        $sut = new EntityFactory($logger, $checkTypeProvider);
        $data = [
            'abc' => 'test',
            'def' => 'words'
        ];
        $this->expectException(EntityFactoryUnrecognisedFieldException::class);
        $this->expectExceptionMessage(
            'The entity ' .
            DummyBasicEntity::class .
            ' was passed the following field(s) which it does not know how to handle: def'
        );
        $sut->buildFromArray($data, DummyBasicEntity::class, ['abc' => 'string']);
    }

    /**
     * Ensures we create a Hansard entry correctly.
     *
     * @covers ::hansardEntry
     * @throws ReflectionException
     */
    public function testHansardEntry(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $checkTypeProvider = $this->createMock(CheckTypeProviderInterface::class);
        $dummyDateTime = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            '2019-01-23',
            new DateTimeZone('UTC')
        );
        $checkType = static function (string $referenceName, string $type, $value) use ($dummyDateTime) {
            if (HansardEntity::class . ':date' === $referenceName && 'date' === $type) {
                return $dummyDateTime;
            }

            return $value;
        };
        $checkTypeProvider->method('checkType')->willReturnCallback($checkType);

        $sut = new EntityFactory($logger, $checkTypeProvider);
        $data = [
            'id' => 123,
            'date' => '2019-01-23',
            'number' => 14,
            'sourceUrl' => 'https://example.com/source',
            'debateUrl' => 'https://example.com/debate',
            'text' => 'hello',
            'title' => 'to be written',
            'house' => 'commons'
        ];
        $entity = $sut->hansardEntry($data);
        self::assertSame(123, $entity->getId());
        self::assertSame($dummyDateTime, $entity->getDate());
        self::assertSame(14, $entity->getNumber());
        self::assertSame('https://example.com/source', $entity->getSourceUrl());
        self::assertSame('https://example.com/debate', $entity->getDebateUrl());
        self::assertSame('hello', $entity->getText());
        self::assertSame('to be written', $entity->getTitle());
        self::assertSame('commons', $entity->getHouse());
    }

    /**
     * @covers ::divisionVoteSummary
     * @throws ReflectionException
     */
    public function testDivisionVoteSummary(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $checkTypeProvider = $this->createMock(CheckTypeProviderInterface::class);
        $checkTypeProvider->method('checkType')->will(self::returnArgument(2));
        $sut = new EntityFactory($logger, $checkTypeProvider);
        $data = [
            'divisionId' => 456,
            'rebellions' => 12,
            'tellers' => 3,
            'turnout' => 200,
            'possibleTurnout' => 442,
            'ayeMajority' => -23
        ];
        $entity = $sut->divisionVoteSummary($data);
        self::assertSame(456, $entity->getDivisionId());
        self::assertSame(12, $entity->getRebellions());
        self::assertSame(3, $entity->getTellers());
        self::assertSame(200, $entity->getTurnout());
        self::assertSame(442, $entity->getPossibleTurnout());
        self::assertSame(-23, $entity->getAyeMajority());
    }

    /**
     * @covers ::motion
     * @throws ReflectionException
     */
    public function testMotion(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $checkTypeProvider = $this->createMock(CheckTypeProviderInterface::class);
        $dummyDateTime = DateTimeImmutable::createFromFormat(
            '!Y-m-d H:i:s',
            '2018-06-23 14:23:12',
            new DateTimeZone('UTC')
        );
        $checkType = static function (string $referenceName, string $type, $value) use ($dummyDateTime) {
            if (MotionEntity::class . ':lastEditDateTime' === $referenceName && 'date' === $type) {
                return $dummyDateTime;
            }

            return $value;
        };
        $checkTypeProvider->method('checkType')->willReturnCallback($checkType);

        $sut = new EntityFactory($logger, $checkTypeProvider);
        $data = [
            'divisionId' => 456,
            'title' => 'hello',
            'motion' => 'what was voted on',
            'comments' => 'none',
            'ayeSummary' => 'voted yes',
            'noeSummary' => 'voted no',
            'id' => 23,
            'lastEditedByUserId' => 134,
            'lastEditDateTime' => '2018-06-23 14:23:12'
        ];
        $entity = $sut->motion($data);
        self::assertSame(456, $entity->getDivisionId());
        self::assertSame('hello', $entity->getTitle());
        self::assertSame('what was voted on', $entity->getMotion());
        self::assertSame('none', $entity->getComments());
        self::assertSame('voted yes', $entity->getAyeSummary());
        self::assertSame('voted no', $entity->getNoeSummary());
        self::assertSame(23, $entity->getId());
        self::assertSame(134, $entity->getLastEditedByUserId());
        self::assertEquals($dummyDateTime, $entity->getLastEditDateTime());
    }

    /**
     * @covers ::motion
     * @throws ReflectionException
     */
    public function testMotionWithoutOptionals(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $checkTypeProvider = $this->createMock(CheckTypeProviderInterface::class);
        $checkTypeProvider->method('checkType')->will(self::returnArgument(2));
        $sut = new EntityFactory($logger, $checkTypeProvider);
        $data = [
            'divisionId' => 456,
            'title' => 'hello',
            'motion' => 'what was voted on',
            'comments' => 'none',
            'ayeSummary' => 'voted yes',
            'noeSummary' => 'voted no'
        ];
        $entity = $sut->motion($data);
        self::assertSame(456, $entity->getDivisionId());
        self::assertSame('hello', $entity->getTitle());
        self::assertSame('what was voted on', $entity->getMotion());
        self::assertSame('none', $entity->getComments());
        self::assertSame('voted yes', $entity->getAyeSummary());
        self::assertSame('voted no', $entity->getNoeSummary());
        self::assertNull($entity->getId());
        self::assertNull($entity->getLastEditedByUserId());
        self::assertNull($entity->getLastEditDateTime());
    }
}
