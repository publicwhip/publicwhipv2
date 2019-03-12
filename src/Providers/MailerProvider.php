<?php
declare(strict_types=1);

namespace PublicWhip\Providers;

use DebugBar\Bridge\SwiftMailer\SwiftLogCollector;
use DebugBar\Bridge\SwiftMailer\SwiftMailCollector;
use DebugBar\DebugBarException;
use RuntimeException;
use Swift_Mailer;
use Swift_Message;
use Swift_NullTransport;
use Swift_SendmailTransport;
use Swift_SmtpTransport;

/**
 * Class MailerProvider
 * @package PublicWhip\Providers
 * @TODO Uses 'new'/factories: can we change to use DI?
 */
final class MailerProvider implements MailerProviderInterface
{

    /**
     * @var Swift_Mailer $mailer
     */
    private $mailer;

    /**
     * MailerProvider constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        $transport = $options['transport'] ?: '';
        switch ($transport) {
            case 'null':
                $transport = Swift_NullTransport::newInstance();
                break;
            case 'smtp':
                $transport = Swift_SmtpTransport::newInstance($options['host'], $options['port'])
                    ->setUsername($options['username'])
                    ->setPassword($options['password']);
                break;
            case 'sendmail':
                $transport = Swift_SendmailTransport::newInstance($options['command']);
                break;
            default:
                throw new RuntimeException('Unrecognised mail transport');
        }
        $this->mailer = Swift_Mailer::newInstance($transport);
    }

    /**
     * @param string $subject
     * @param string $to
     * @param array $body
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function sendMultipartMail(string $subject, string $to, array $body): int
    {
        $message = Swift_Message::newInstance($subject)->setTo($to);
        $first = true;
        foreach ($body as $part) {
            if ($first) {
                $message->setBody($part);
                $first = false;
            } else {
                $message->addPart($part);
            }
        }
        return $this->send($message);
    }

    /**
     * @param Swift_Message $message
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    private function send(Swift_Message $message): int
    {
        return $this->mailer->send($message);
    }

    /**
     * @param string $subject
     * @param string $to
     * @param string $body
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function sendMail(string $subject, string $to, string $body): int
    {
        $message = Swift_Message::newInstance($subject)->setTo($to);
        $message->setBody($body);
        return $this->send($message);
    }

    /**
     * @param DebuggerProviderInterface $debugger
     * @throws DebugBarException
     */
    public function addToDebugger(DebuggerProviderInterface $debugger): void
    {
        $debugger->addMessagesAggregateCollector(new SwiftLogCollector($this->mailer));
        $debugger->addDataCollector(new SwiftMailCollector($this->mailer));
    }
}
