<?php
declare(strict_types = 1);

namespace PublicWhip\Providers;

/**
 * CheckTypeProvider.
 *
 * Checks the type of mixed items (usually strings) against criteria.
 */
interface CheckTypeProviderInterface
{
    /**
     * Checks that the field is of the expected type.
     *
     * @param string $referenceName Name of the what we are building (for error reporting).
     * @param string $allowedType String of the expected type.
     * @param string|int|float|object|bool $value The value to check.
     * @return string|int|float|object|bool
     */
    public function checkType(string $referenceName, string $allowedType, $value);
}
