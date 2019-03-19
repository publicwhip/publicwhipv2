<?php
declare(strict_types = 1);

namespace PublicWhip\Providers;

use DebugBar\Bridge\SwiftMailer\SwiftMailCollector;
use DebugBar\DataCollector\MessagesCollector;
use Swift_Mailer;
use Swift_Message;
use Swift_Plugins_Logger;
use Swift_Plugins_LoggerPlugin;
use function is_string;

/**
 * Handles sending email.
 */
final class MailerProvider implements MailerProviderInterface
{
    /**
     * The mailing engine.
     *
     * @var Swift_Mailer $mailer
     */
    private $mailer;

    /**
     * Default from name.
     *
     * @var string
     */
    private $fromName;

    /**
     * Default from address.
     *
     * @var string
     */
    private $fromAddress;

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
    ) {
        $this->mailer = new Swift_Mailer($mailerTransport->getTransport());
        $this->fromName = $fromName;
        $this->fromAddress = $fromAddress;
    }

    /**
     * Send a multipart email.
     *
     * @param string $subject The subject of the email.
     * @param string $toAddress Who it is going to.
     * @param string $toName The name of the person it is going to.
     * @param array<string> $body What the body of the email is.
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function sendMultipartMail(string $subject, string $toAddress, string $toName, array $body): int
    {
        if (0 === count($body)) {
            return 0;
        }

        $message = (new Swift_Message($subject))->setTo([$toAddress => $toName]);
        $message->setFrom([$this->fromAddress => $this->fromName]);
        $message->addPart($body[0]);
        next($body);

        foreach ($body as $part) {
            $message->addPart($part);
        }

        return $this->mailer->send($message);
    }

    /**
     * Send a simple plain text email.
     *
     * @param string $subject The subject of the email.
     * @param string $toAddress Who it is going to.
     * @param string $toName The name of the person it is going to.
     * @param string $body What the body of the email is.
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function sendMail(string $subject, string $toAddress, string $toName, string $body): int
    {
        $message = (new Swift_Message($subject))->setTo([$toAddress => $toName]);
        $message->setFrom([$this->fromAddress => $this->fromName]);

        $message->setBody($body);

        return $this->mailer->send($message);
    }

    /**
     * Add a debugger.
     *
     * @param DebuggerProviderInterface $debugger The debugger to add.
     */
    public function addToDebugger(DebuggerProviderInterface $debugger): void
    {
        // the SwiftLogCollector with Debugbar is outdated.
        $logCollector = new class($this->mailer) extends MessagesCollector implements Swift_Plugins_Logger
        {
            /**
             * Registers our plugin.
             *
             * @param Swift_Mailer $mailer The mailer object.
             */
            public function __construct(Swift_Mailer $mailer)
            {
                $mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($this));
                parent::__construct($this->getName());
            }

            /**
             * Adds an entry
             *
             * Coding standard disabled for Swift compatibility.
             * phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
             * @param string $entry Details of message we are adding.
             */
            public function add($entry): void
            {
                $this->addMessage($entry);
            }

            // phpcs:enable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint

            /**
             * Diagnostics.
             *
             * @return string
             */
            public function dump(): string
            {
                $text = json_encode($this->messages);
                if (!is_string($text)) {
                    $text = '[Failed to encode]';
                }

                return $text;
            }

            /**
             * Get the name.
             *
             * @return string
             */
            public function getName(): string
            {
                return 'mailerProviderSwift';
            }
        };
        $debugger->addMessagesAggregateCollector($logCollector);
        $debugger->addDataCollector(new SwiftMailCollector($this->mailer));
    }
}
