<?php
declare(strict_types = 1);

namespace PublicWhip\Providers;

/**
 * MailerProviderInterface.
 */
interface MailerProviderInterface
{
    /**
     * Setup the mailer.
     *
     * @param MailerTransportProviderInterface $mailerTransport Built transport.
     * @param string $fromName Default from name.
     * @param string $fromAddress Default from address.
     */
    public function __construct(
        MailerTransportProviderInterface $mailerTransport,
        string $fromName,
        string $fromAddress
    );

    /**
     * Send a multipart email.
     *
     * @param string $subject The subject of the email.
     * @param string $toAddress Who it is going to.
     * @param string $toName The name of the person it is going to.
     * @param array<string> $body What the body of the email is.
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
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function sendMail(string $subject, string $toAddress, string $toName, string $body): int;

    /**
     * Add a debugger.
     *
     * @param DebuggerProviderInterface $debugger The debugger to add.
     */
    public function addToDebugger(DebuggerProviderInterface $debugger): void;
}
