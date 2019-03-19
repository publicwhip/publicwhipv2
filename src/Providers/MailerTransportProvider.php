<?php
declare(strict_types = 1);

namespace PublicWhip\Providers;

use InvalidArgumentException;
use RuntimeException;
use Swift_NullTransport;
use Swift_SendmailTransport;
use Swift_SmtpTransport;
use Swift_Transport;

/**
 * MailerTransportProvider.
 */
class MailerTransportProvider implements MailerTransportProviderInterface
{
    /**
     * The built transport.
     *
     * @var Swift_Transport
     */
    private $transport;

    /**
     * Setup the transport.
     *
     * @param array<string,string> $options Configuration options.
     */
    public function __construct(array $options)
    {
        $transport = $options['transport'] ?: '';

        switch ($transport) {
            case 'null':
                $this->transport = new Swift_NullTransport();

                break;
            case 'smtp':
                $this->buildSmtp($options);

                break;
            case 'sendmail':
                $this->buildSendmail($options);

                break;
            default:
                throw new RuntimeException('Unrecognised mail transport');
        }
    }

    /**
     * Build SMTP transport.
     *
     * @param array<string,string> $options Configuration options
     */
    private function buildSmtp(array $options): void
    {
        if (!isset($options['host'])) {
            throw new InvalidArgumentException('Missing host for smtp transport');
        }
        if (!isset($options['port'])) {
            throw new InvalidArgumentException('Missing port for smtp transport');
        }
        if (!isset($options['username'])) {
            throw new InvalidArgumentException('Missing username for smtp transport');
        }
        if (!isset($options['password'])) {
            throw new InvalidArgumentException('Missing password for smtp transport');
        }
        $this->transport = (new Swift_SmtpTransport($options['host'], (int)$options['port']))
            ->setUsername($options['username'])
            ->setPassword($options['password']);
    }

    /**
     * Build sendmail.
     *
     * @param array<string,string> $options Configuration options.
     */
    private function buildSendmail(array $options): void
    {
        if (!isset($options['command'])) {
            throw new InvalidArgumentException('Missing command for sendmail transport');
        }
        $this->transport = new Swift_SendmailTransport($options['command']);
    }

    /**
     * Get the configured transport.
     *
     * @return Swift_Transport
     */
    public function getTransport(): Swift_Transport
    {
        return $this->transport;
    }
}
