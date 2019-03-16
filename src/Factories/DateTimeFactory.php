<?php
declare(strict_types=1);

namespace PublicWhip\Factories;

use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use PublicWhip\Exceptions\Factories\BadDateTimeException;

/**
 * Class DateTimeFactory.
 *
 * Creates datetime objects.
 *
 * As a factory, we are allowed to use static calls so suppress PHPMD.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
final class DateTimeFactory implements DateTimeFactoryInterface
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger Logger.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Convert a string in yyyy-mm-dd format to a DateTimeImmutable.
     *
     * @param string $ymd Date in yyyy-mm-dd to be converted. Time will be set to 00:00:00
     *
     * @return DateTimeImmutable
     */
    public function dateTimeImmutableFromYyyyMmDd(string $ymd): DateTimeImmutable
    {
        $this->logger->info('Creating dateTimeImmutable from {ymd} in yyyy-mm-dd format', ['ymd' => $ymd]);
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $ymd);
        if (!$date instanceof DateTimeImmutable) {
            $errors = DateTimeImmutable::getLastErrors();
            $encoded = json_encode($errors);
            $message = sprintf(
                'Failed to create DateTimeImmutable from yyyy-mm-dd using the input %s : errors: %s',
                $ymd,
                $encoded ?: '[failed]'
            );
            $this->logger->warning($message);
            throw new BadDateTimeException($message);
        }
        return $date;
    }
}
