<?php
declare(strict_types = 1);

namespace PublicWhip\Factories;

use Closure;
use DateTimeInterface;
use Psr\Log\LoggerInterface;
use PublicWhip\Entities\DivisionVoteSummary;
use PublicWhip\Entities\HansardEntity;
use PublicWhip\Entities\MotionEntity;
use PublicWhip\Exceptions\Factories\EntityFactoryUnrecognisedFieldException;
use PublicWhip\Exceptions\Factories\EntityMissingRequiredFieldException;
use PublicWhip\Providers\CheckTypeProviderInterface;

/**
 * Factories up entities.
 */
final class EntityFactory implements EntityFactoryInterface
{
    /**
     * Logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * The check typer.
     *
     * @var CheckTypeProviderInterface
     */
    private $checkTypeProvider;

    /**
     * @param LoggerInterface $logger The logger.
     * @param CheckTypeProviderInterface $checkTypeProvider The type checker.
     */
    public function __construct(LoggerInterface $logger, CheckTypeProviderInterface $checkTypeProvider)
    {
        $this->logger = $logger;
        $this->checkTypeProvider = $checkTypeProvider;
    }

    /**
     * Build a HansardEntry.
     *
     * @param array<string,string|int|float|object|bool> $data Data to build the entity with.
     * @return HansardEntity
     */
    public function hansardEntry(array $data): HansardEntity
    {
        /** @var HansardEntity $entity */
        $entity = $this->buildFromArray(
            $data,
            HansardEntity::class,
            [
                'id' => 'int',
                'date' => DateTimeInterface::class,
                'number' => 'int',
                'sourceUrl' => 'string',
                'debateUrl' => 'string',
                'text' => 'string',
                'title' => 'string',
                'house' => 'string'
            ]
        );

        return $entity;
    }

    /**
     * Builds a division summary vote.
     *
     * @param array<string,string|int|float|object|bool> $data Data to build the entity with.
     * @return DivisionVoteSummary
     */
    public function divisionVoteSummary(array $data): DivisionVoteSummary
    {
        /** @var DivisionVoteSummary $entity */
        $entity = $this->buildFromArray(
            $data,
            DivisionVoteSummary::class,
            [
                'divisionId' => 'int',
                'rebellions' => 'int',
                'tells' => 'int',
                'turnout' => 'int',
                'possibleTurnout' => 'int',
                'ayeMajority' => 'int',
            ]
        );

        return $entity;
    }

    /**
     * Builds a motion.
     *
     * @param array<string,string|int|float|object|bool> $data Data to build the entity with.
     * @return MotionEntity
     */
    public function motion(array $data): MotionEntity
    {
        /** @var MotionEntity $entity */
        $entity = $this->buildFromArray(
            $data,
            MotionEntity::class,
            [
                'divisionId' => 'int',
                'title' => 'string',
                'motion' => 'string',
                'comments' => 'string',
                'ayeSummary' => 'string',
                'noeSummary' => 'string',
            ],
            [
                'id' => 'int',
                'lastEditedByUserId' => 'int',
                'lastEdited' => DateTimeInterface::class,
            ]
        );

        return $entity;
    }

    /**
     * Build an entity from an array.
     *
     * @param array<string,string|float|int|object|bool> $data The data to build from.
     * @param string $entityName Name of the entity to build.
     * @param array<string,string> $required The required data configuration.
     * @param array<string,string>|null $optional The optional data configuration.
     * @return object
     */
    private function buildFromArray(array $data, string $entityName, array $required, ?array $optional = null): object
    {
        $this->logger->debug('Building ' . $entityName);
        $new = new $entityName();

        /**
         * Handles hydration without having to use reflection.
         *
         * @param object $object Object we are modifying.
         * @param string $property Name of the property.
         * @param string|bool|int|float $newValue New value.
         */
        $hydrate = function (object $object, string $property, $newValue): void {
            Closure::bind(function () use ($property, $newValue): void {
                $this->$property = $newValue;
            }, $object, $object)->__invoke();
        };

        $fieldsMapped = $this->fillRequiredFields($new, $required, $data, $hydrate, $entityName);

        if ($optional) {
            $fieldsMapped = $this->fillOptionalFields($new, $optional, $data, $hydrate, $entityName, $fieldsMapped);
        }
        /**
         * Check that all fields were loaded.
         */
        $difference = array_diff(array_keys($data), $fieldsMapped);

        if (count($difference) > 0) {
            $this->logger->warning(
                'The entity {entityName} was passed the following field(s) which it does ' .
                'not know how to handle {fields}',
                ['entityName' => $entityName, 'fields' => $difference]
            );
            $message = sprintf(
                'The entity %s was passed the following field(s) which it does not know how to handle: %s',
                $entityName,
                implode(', ', $difference)
            );

            throw new EntityFactoryUnrecognisedFieldException($message);
        }

        return $new;
    }

    /**
     * Fill required fields fields.
     *
     * @param object $new The object we are hydrating.
     * @param array<string,string> $required Name of field as key, type of field as value.
     * @param array<string,string|int|float|object|bool> $data The provided data.
     * @param callable $hydrate Our hydrator.
     * @param string $entityName Name of the entity
     * @return array<int,string> Mapped fields
     */
    private function fillRequiredFields(
        object $new,
        array $required,
        array $data,
        callable $hydrate,
        string $entityName
    ): array {
        $fieldsMapped = [];

        foreach ($required as $name => $type) {
            if (!isset($data[$name])) {
                $this->logger->error(
                    'Missing required field {fieldName} when building {entityName}',
                    ['fieldName' => $name, 'entityName' => $entityName]
                );
                $message = sprintf(
                    'Missing required field %s when building %s',
                    $name,
                    $entityName
                );

                throw new EntityMissingRequiredFieldException($message);
            }

            $value = $this->checkTypeProvider->checkType($entityName . ':' . $name, $type, $data[$name]);

            $hydrate($new, $name, $value);
            $fieldsMapped[] = $name;
        }

        return $fieldsMapped;
    }

    /**
     * Fill optional fields.
     *
     * @param object $new The object we are hydrating.
     * @param array<string,string> $optional Name of field as key, type(s) of field as value.
     * @param array<string,string|int|float|object|bool> $data The provided data.
     * @param callable $hydrate Our hydrator.
     * @param string $entityName Name of the entity
     * @param array<int,string> $fieldsMapped List of fields already mapped.
     * @return array<int,string> Mapped fields
     */
    private function fillOptionalFields(
        object $new,
        array $optional,
        array $data,
        callable $hydrate,
        string $entityName,
        array $fieldsMapped
    ): array {
        foreach ($optional as $name => $type) {
            if (isset($data[$name])) {
                $hydrate($new, $name, null);

                $value = $this->checkTypeProvider->checkType($entityName . ':' . $name, $type, $data[$name]);

                $hydrate($new, $name, $value);
            }

            $fieldsMapped[] = $name;
        }

        return $fieldsMapped;
    }
}
