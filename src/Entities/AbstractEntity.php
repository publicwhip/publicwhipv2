<?php
declare(strict_types=1);

namespace PublicWhip\Entities;

use DateTimeImmutable;
use PublicWhip\Exceptions\Entities\EntityFieldWrongTypeException;
use PublicWhip\Exceptions\Entities\EntityMissingRequiredFieldException;
use PublicWhip\Exceptions\Entities\EntityPassedUnrecognisedFieldException;

/**
 * Class AbstractEntity.
 *
 * Base class for all the entities.
 *
 * @package PublicWhip\Entities
 */
abstract class AbstractEntity
{

    /**
     * Returns an associated array of properties the entity requires.
     *
     * @return array
     */
    abstract protected function requiredPropertiesMapping(): array;

    /**
     * Returns an optional associated array of properties this entity can deal with.
     *
     * @return array
     */
    protected function optionalPropertiesMapping(): array
    {
        return [];
    }

    /**
     * Build an entity from an array.
     *
     * @param array $data
     * @return static
     */
    final public static function buildFromArray(array $data): AbstractEntity
    {
        $new = new static();
        $hydrate = function ($object, $property, $newValue) {
            \Closure::bind(function () use ($property, $newValue) {
                $this->$property = $newValue;
            }, $object, $object)->__invoke();
        };
        $fieldsMapped = [];
        foreach ($new->requiredPropertiesMapping() as $name => $type) {
            if (!isset($data[$name])) {
                throw new EntityMissingRequiredFieldException(
                    sprintf(
                        'Missing required field %s when building %s',
                        $name,
                        static::class
                    )
                );
            }
            if (!self::checkType($name, $type, $data[$name])) {
                throw new EntityFieldWrongTypeException(
                    sprintf(
                        'Expected required entity field %s to be %s, but was %s when creating %s',
                        $name,
                        $type,
                        is_object($data[$name]) ? get_class($data[$name]) : gettype($data[$name]),
                        static::class
                    )
                );
            }
            $hydrate($new, $name, $data[$name]);
            $fieldsMapped[] = $name;
        }
        // now add the optional fields.
        foreach ($new->optionalPropertiesMapping() as $name => $type) {
            if (isset($data[$name])) {
                if (!self::checkType($name, $type, $data[$name])) {
                    throw new EntityFieldWrongTypeException(
                        sprintf(
                            'Expected entity field %s to be %s, but was %s when creating %s',
                            $name,
                            $type,
                            is_object($data[$name]) ? get_class($data[$name]) : gettype($data[$name]),
                            static::class
                        )
                    );
                }
                $hydrate($new, $name, $data[$name]);
            } else {
                $hydrate($new, $name, null);
            }
            $fieldsMapped[] = $name;
        }
        $difference = array_diff(array_keys($data), $fieldsMapped);
        if (count($difference) > 0) {
            throw new EntityPassedUnrecognisedFieldException(
                sprintf(
                    'The entity %s was passed the following field(s) which it does not know how to handle: %s',
                    static::class,
                    implode(', ', $difference)
                )
            );
        }
        return $new;
    }

    /**
     * Checks that the field is of the expected type.
     *
     * @param string $fieldName Name of the field (for error reporting).
     * @param string $expectedType String of the expected type.
     * @param mixed $value The value to check.
     * @return bool
     */
    private static function checkType(string $fieldName, string $expectedType, $value): bool
    {
        $correctType = true;
        switch ($expectedType) {
            case 'int':
                if (!is_int($value)) {
                    $correctType = false;
                }
                break;
            case 'string':
                if (!is_string($value)) {
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
                        static::class
                    )
                );
        }
        return $correctType;
    }
}
