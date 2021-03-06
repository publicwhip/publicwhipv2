<?php
declare(strict_types = 1);

namespace PublicWhip\Exceptions\Factories;

/**
 * If an entity is being built, but a required field is missing.
 */
final class EntityMissingRequiredFieldException extends AbstractFactoryException
{
}
