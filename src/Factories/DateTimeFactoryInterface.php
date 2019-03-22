<?php
declare(strict_types = 1);

namespace PublicWhip\Factories;

use DateTimeImmutable;
use Psr\Log\LoggerInterface;

/**
 * Creates datetime objects.
 */
interface DateTimeFactoryInterface
{
    /**
     * @param LoggerInterface $logger Logger.
     */
    public function __construct(LoggerInterface $logger);

    /**
     * Convert a string to a DateTimeImmutable.
     *
     * @param string $format The date format.
     * @param string $inputDate Date to be converted.
     * @return DateTimeImmutable
     */
    public function createImmutableFromFormat(string $format, string $inputDate): DateTimeImmutable;
}
