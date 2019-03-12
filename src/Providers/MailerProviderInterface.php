<?php
declare(strict_types=1);

namespace PublicWhip\Providers;

use DebugBar\DebugBarException;

/**
 * Class MailerProviderInterface
 * @package PublicWhip\Providers
 */
interface MailerProviderInterface
{
    /**
     * MailerProvider constructor.
     * @param array $options
     */
    public function __construct(array $options);

    /**
     * @param string $subject
     * @param string $to
     * @param array $body
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function sendMultipartMail(string $subject, string $to, array $body): int;

    /**
     * @param string $subject
     * @param string $to
     * @param string $body
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function sendMail(string $subject, string $to, string $body): int;

    /**
     * @param DebuggerProviderInterface $debugger
     * @throws DebugBarException
     */
    public function addToDebugger(DebuggerProviderInterface $debugger): void;
}
