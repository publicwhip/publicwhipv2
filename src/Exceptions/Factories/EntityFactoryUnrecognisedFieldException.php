<?php
declare(strict_types = 1);

namespace PublicWhip\Exceptions\Factories;

/**
 * If an entity has been passed a field that it does not know what to do with.
 */
final class EntityFactoryUnrecognisedFieldException extends AbstractFactoryException
{
}
