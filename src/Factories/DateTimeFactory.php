<?php
declare(strict_types = 1);

namespace PublicWhip\Factories;

use DateTimeImmutable;
use DateTimeZone;
use Psr\Log\LoggerInterface;
use PublicWhip\Exceptions\Factories\BadDateTimeException;

/**
 * Creates datetime objects.
 * As a factory, we are allowed to use static calls so suppress PHPMD.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
final class DateTimeFactory implements DateTimeFactoryInterface
{
    /**
     * Logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * The default time zone to use. We should have everything in UTC.
     *
     * @var DateTimeZone
     */
    private $defaultTimeZone;

    /**
     * @param LoggerInterface $logger Logger.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->defaultTimeZone = new DateTimeZone('UTC');
    }

    /**
     * Convert a string to a DateTimeImmutable.
     *
     * @param string $format The date format.
     * @param string $inputDate Date to be converted.
     * @return DateTimeImmutable
     */
    public function createImmutableFromFormat(string $format, string $inputDate): DateTimeImmutable
    {
        $this->logger->info(
            'Creating createImmutableFromFormat from format {format} date {inputDate}',
            ['format' => $format, 'inputDate' => $inputDate]
        );
        $date = DateTimeImmutable::createFromFormat($format, $inputDate, $this->defaultTimeZone);

        if (!$date instanceof DateTimeImmutable) {
            $errors = DateTimeImmutable::getLastErrors();
            $errorsText = $errors['errors'] ? '"' . implode('", "', $errors['errors']) . '"' : null;
            $warningText = $errors['warnings'] ? '"' . implode('", "', $errors['warnings']) . '"' : null;
            $message = sprintf(
                'Failed to createImmutableFromFormat from %s using the input %s : errors: %s : warnings: %s',
                $format,
                $inputDate,
                $errorsText ?? '[none]',
                $warningText ?? '[none]'
            );
            $this->logger->warning($message);

            throw new BadDateTimeException($message);
        }

        return $date;
    }
}
