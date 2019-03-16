<?php
declare(strict_types=1);

namespace PublicWhip\Providers;

/**
 * Class MailerProvider
 *
 * @TODO Uses 'new' can we change to use DI?
 */
interface MailerProviderInterface
{

    /**
     * MailerProvider constructor.
     *
     * @param string[] $options Configuration options.
     */
    public function __construct(array $options);

    /**
     * Send a multipart email.
     *
     * @param string $subject The subject of the email.
     * @param string $toAddress Who it is going to.
     * @param string $toName The name of the person it is going to.
     * @param string[] $body What the body of the email is.
     *
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function sendMultipartMail(string $subject, string $toAddress, string $toName, array $body): int;

    /**
     * Send a simple plain text email.
     *
     * @param string $subject The subject of the email.
     * @param string $toAddress Who it is going to.
     * @param string $toName The name of the person it is going to.
     * @param string $body What the body of the email is.
     *
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function sendMail(string $subject, string $toAddress, string $toName, string $body): int;

    /**
     * Add a debugger.
     *
     * @param DebuggerProviderInterface $debugger The debugger to add.
     *
     * @return void
     */
    public function addToDebugger(DebuggerProviderInterface $debugger): void;
}
