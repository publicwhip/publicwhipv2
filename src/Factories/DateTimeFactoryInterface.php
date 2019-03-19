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
     * Convert a string in yyyy-mm-dd format to a DateTimeImmutable.
     *
     * @param string $ymd Date in yyyy-mm-dd to be converted. Time will be set to 00:00:00
     * @return DateTimeImmutable
     */
    public function dateTimeImmutableFromYyyyMmDd(string $ymd): DateTimeImmutable;
}
