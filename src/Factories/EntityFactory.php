<?php
declare(strict_types=1);

namespace PublicWhip\Factories;

use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use PublicWhip\Entities\DivisionEntity;
use PublicWhip\Exceptions\Factories\EntityFactoryPassedUnrecognisedFieldException;
use PublicWhip\Exceptions\Factories\EntityFieldWrongTypeException;
use PublicWhip\Exceptions\Factories\EntityMissingRequiredFieldException;

/**
 * Class EntityFactory.
 *
 * Factories up entities.
 */
final class EntityFactory implements EntityFactoryInterface
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger The logger.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Build an entity from an array.
     *
     * @param array<string,mixed> $data The data to build from.
     * @param string $entityName Name of the entity to build.
     * @param array<string,mixed> $required The required data configuration.
     * @param array<string,mixed> $optional The optional data configuration.
     *
     * @return object
     */
    private function buildFromArray(array $data, string $entityName, array $required, array $optional): object
    {
        $this->logger->debug('Building ' . $entityName);
        $new = new $entityName();
        /**
         * Handles hydration without having to use reflection.
         *
         * @param object $object Object we are modifying.
         * @param string $property Name of the property.
         * @param mixed $newValue New value.
         */
        $hydrate = function (object $object, string $property, $newValue): void {
            \Closure::bind(function () use ($property, $newValue): void {
                $this->$property = $newValue;
            }, $object, $object)->__invoke();
        };
        $fieldsMapped = $this->fillRequiredFields($new, $required, $data, $hydrate, $entityName);

        $fieldsMapped = $this->fillOptionalFields($new, $optional, $data, $hydrate, $entityName, $fieldsMapped);
        /**
         * Check that all fields were loaded.
         */
        $difference = array_diff(array_keys($data), $fieldsMapped);
        if (count($difference) > 0) {
            $message = sprintf(
                'The entity %s was passed the following field(s) which it does not know how to handle: %s',
                self::class,
                implode(', ', $difference)
            );
            $this->logger->warning($message);
            throw new EntityFactoryPassedUnrecognisedFieldException($message);
        }
        return $new;
    }

    /**
     * Fill required fields fields.
     *
     * @param object $new The object we are hydrating.
     * @param array<string,mixed> $required The configuration for the required fields.
     * @param array<string,mixed> $data The provided data.
     * @param callable $hydrate Our hydrator.
     * @param string $entityName Name of the entity
     *
     * @return array<int,string> Mapped fields
     */
    private function fillRequiredFields(
        object $new,
        array $required,
        array $data,
        callable $hydrate,
        string $entityName
    ): array
    {
        $fieldsMapped = [];
        foreach ($required as $name => $type) {
            if (!isset($data[$name])) {
                $message = sprintf(
                    'Missing required field %s when building %s',
                    $name,
                    $entityName
                );
                $this->logger->warning($message);
                throw new EntityMissingRequiredFieldException($message);
            }
            if (!self::checkType($name, $type, $data[$name])) {
                $message = sprintf(
                    'Expected required entity field %s to be %s, but was %s when creating %s',
                    $name,
                    $type,
                    \is_object($data[$name]) ? \get_class($data[$name]) : gettype($data[$name]),
                    $entityName
                );
                $this->logger->warning($message);
                throw new EntityFieldWrongTypeException($message);
            }
            $hydrate($new, $name, $data[$name]);
            $fieldsMapped[] = $name;
        }
        return $fieldsMapped;
    }

    /**
     * Fill optional fields.
     *
     * @param object $new The object we are hydrating.
     * @param array<string,mixed> $optional The configuration for the optional fields.
     * @param array<string,mixed> $data The provided data.
     * @param callable $hydrate Our hydrator.
     * @param string $entityName Name of the entity
     * @param array<int,string> $fieldsMapped List of fields already mapped.
     *
     * @return array<int,string> Mapped fields
     */
    private function fillOptionalFields(
        object $new,
        array $optional,
        array $data,
        callable $hydrate,
        string $entityName,
        array $fieldsMapped
    ): array
    {
        foreach ($optional as $name => $type) {
            if (isset($data[$name])) {
                $hydrate($new, $name, null);
                if (!self::checkType($name, $type, $data[$name])) {
                    $message = sprintf(
                        'Expected entity field %s to be %s, but was %s when creating %s',
                        $name,
                        $type,
                        \is_object($data[$name]) ? \get_class($data[$name]) : gettype($data[$name]),
                        $entityName
                    );
                    $this->logger->warning($message);
                    throw new EntityFieldWrongTypeException($message);
                }
                $hydrate($new, $name, $data[$name]);
            }
            $fieldsMapped[] = $name;
        }
        return $fieldsMapped;
    }

    /**
     * Checks that the field is of the expected type.
     *
     * @param string $fieldName Name of the field (for error reporting).
     * @param string $expectedType String of the expected type.
     * @param mixed $value The value to check.
     *
     * @return bool
     */
    private static function checkType(string $fieldName, string $expectedType, $value): bool
    {
        $correctType = true;
        switch ($expectedType) {
            case 'int':
                if (!\is_int($value)) {
                    $correctType = false;
                }
                break;
            case 'string':
                if (!\is_string($value)) {
                    $correctType = false;
                }
                break;
            case DateTimeImmutable::class:
                if (!$value instanceof DateTimeImmutable) {
                    $correctType = false;
                }
                break;
            default:
                throw new EntityFieldWrongTypeException(
                    sprintf(
                        'Bad definition found for field %s in %s',
                        $fieldName,
                        self::class
                    )
                );
        }
        return $correctType;
    }

    /**
     * Build a division.
     *
     * @param array<string,mixed> $data Data to build the entity with.
     *
     * @return DivisionEntity
     */
    public function division(array $data): DivisionEntity
    {
        /** @var DivisionEntity $entity */
        $entity = $this->buildFromArray(
            $data,
            DivisionEntity::class,
            [
                'divisionId' => 'int',
                'date' => DateTimeImmutable::class,
                'number' => 'int',
                'sourceUrl' => 'string',
                'debateUrl' => 'string',
                'motionText' => 'string',
                'house' => 'string',
                'motionTitle' => 'string',
                'originalMotionText' => 'string',
                'originalMotionTitle' => 'string'
            ],
            [
                'rebellions' => 'int',
                'turnout' => 'int',
                'possibleTurnout' => 'int',
                'ayeMajority' => 'int'
            ]
        );
        return $entity;
    }
}
