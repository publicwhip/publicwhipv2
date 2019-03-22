<?php
declare(strict_types = 1);

namespace PublicWhip\Providers;

use DateTimeInterface;
use Psr\Log\LoggerInterface;
use PublicWhip\Exceptions\Providers\CheckTypeProviderWrongTypeException;
use PublicWhip\Factories\DateTimeFactoryInterface;
use function get_class;
use function is_int;
use function is_object;
use function is_string;

/**
 * CheckTypeProvider.
 *
 * Checks the type of mixed items (usually strings) against criteria.
 */
class CheckTypeProvider implements CheckTypeProviderInterface
{
    /**
     * Logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Factory to create dates and times.
     *
     * @var DateTimeFactoryInterface $dateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @param LoggerInterface $logger The logger.
     */
    public function __construct(LoggerInterface $logger, DateTimeFactoryInterface $dateTimeFactory)
    {
        $this->logger = $logger;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * Checks that the field is of the expected type.
     *
     * @param string $referenceName Name of the what we are building (for error reporting).
     * @param string $allowedType String of the expected type.
     * @param string|int|float|object|bool $value The value to check.
     * @return string|int|float|object|bool
     */
    public function checkType(string $referenceName, string $allowedType, $value)
    {
        $check = null;
        switch ($allowedType) {
            case 'int':
                $check = $this->checkTypeInt($value);
                break;

            case 'string':
                if (is_string($value)) {
                    $check = $value;
                }

                break;

            case DateTimeInterface::class:
                $check = $this->checkTypeDateTime($value);

                break;

            default:
                throw new CheckTypeProviderWrongTypeException(
                    sprintf(
                        'Bad definition found for %s: found unknown type %s',
                        $referenceName,
                        $allowedType,
                    )
                );
        }
        if (null !== $check) {
            return $check;
        }

        $actualType = is_object($value) ? get_class($value) : gettype($value);
        $this->logger->error(
            'Expected {referenceName} to be of type {allowedType}, but ' .
            'was {actualType} }',
            [
                'referenceName' => $referenceName,
                'allowedType' => $allowedType,
                'actualType' => $actualType,
            ]
        );
        $message = sprintf(
            'Expected %s to be of type %s, but was %s',
            $referenceName,
            $allowedType,
            $actualType
        );

        throw new CheckTypeProviderWrongTypeException($message);
    }

    /**
     * Checks dates/times (and makes them into DateTimeImmutables if suitable).
     *
     * @param string|int|float|object|bool $value The value to check.
     * @return DateTimeInterface|null Null if not matched.
     */
    public function checkTypeDateTime($value): ?DateTimeInterface
    {
        if ($value instanceof DateTimeInterface) {
            return $value;
        }
        if (is_string($value)) {
            if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $value)) {
                $value = $this->dateTimeFactory->createImmutableFromFormat('!Y-m-d', $value);

                return $value;
            }

            if (preg_match('/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
                $value = $this->dateTimeFactory->createImmutableFromFormat('!Y-m-d H:i:s', $value);
                $this->logger->debug('Parsed to date item');

                return $value;
            }
        }

        return null;
    }

    /**
     * Checks that the field is an int (or looks like one).
     *
     * @param string|int|float|object|bool $value The value to check.
     * @return int|null Null if not accepted.
     */
    private function checkTypeInt($value): ?int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && is_numeric($value) && ctype_digit($value)) {
            return (int)$value;
        }

        return null;
    }
}
