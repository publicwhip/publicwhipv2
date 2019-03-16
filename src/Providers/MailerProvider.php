<?php
declare(strict_types=1);

namespace PublicWhip\Providers;

use DebugBar\Bridge\SwiftMailer\SwiftLogCollector;
use DebugBar\Bridge\SwiftMailer\SwiftMailCollector;
use RuntimeException;
use Swift_Mailer;
use Swift_Message;
use Swift_NullTransport;
use Swift_SendmailTransport;
use Swift_SmtpTransport;

/**
 * Class MailerProvider
 *
 * @TODO Uses 'new' can we change to use DI?
 */
final class MailerProvider implements MailerProviderInterface
{

    /**
     * @var Swift_Mailer $mailer The mailing engine.
     */
    private $mailer;

    /**
     * MailerProvider constructor.
     *
     * @param string[] $options Configuration options.
     */
    public function __construct(array $options)
    {
        $transport = $options['transport'] ?: '';
        switch ($transport) {
            case 'null':
                $transport = new Swift_NullTransport();
                break;
            case 'smtp':
                $transport = (new Swift_SmtpTransport($options['host'], (int)$options['port']))
                    ->setUsername($options['username'])
                    ->setPassword($options['password']);
                break;
            case 'sendmail':
                $transport = new Swift_SendmailTransport($options['command']);
                break;
            default:
                throw new RuntimeException('Unrecognised mail transport');
        }
        $this->mailer = new Swift_Mailer($transport);
    }

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
    public function sendMultipartMail(string $subject, string $toAddress, string $toName, array $body): int
    {
        if (0 === count($body)) {
            return 0;
        }
        $message = Swift_Message::newInstance($subject)->setTo($toAddress, $toName);
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
     *
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function sendMail(string $subject, string $toAddress, string $toName, string $body): int
    {
        $message = (new Swift_Message($subject))->setTo($toAddress, $toName);
        $message->setBody($body);
        return $this->mailer->send($message);
    }

    /**
     * Add a debugger.
     *
     * @param DebuggerProviderInterface $debugger The debugger to add.
     *
     * @return void
     */
    public function addToDebugger(DebuggerProviderInterface $debugger): void
    {
        $debugger->addMessagesAggregateCollector(new SwiftLogCollector($this->mailer));
        $debugger->addDataCollector(new SwiftMailCollector($this->mailer));
    }
}
