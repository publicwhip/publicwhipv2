<?php
declare(strict_types = 1);

namespace PublicWhip\Providers;

use InvalidArgumentException;
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
     * Configuration options.
     *
     * @var array<string,string> $options
     */
    private $options;

    /**
     * Setup the transport.
     *
     * @param array<string,string> $options Configuration options.
     */
    public function __construct(array $options)
    {
        $options['transport'] = $options['transport'] ?? '';

        $this->options = $options;

        if ('null' === $options['transport']) {
            return;
        }

        switch ($options['transport']) {
            case 'smtp':
                $this->validateSmtp();

                break;
            case 'sendmail':
                $this->validateSendmail();

                break;
            default:
                throw new InvalidArgumentException('Unrecognised mail transport');
        }
    }

    /**
     * Validates the SMTP configuration.
     */
    private function validateSmtp(): void
    {
        if (!isset($this->options['host'])) {
            throw new InvalidArgumentException('Missing host for smtp transport');
        }
        if (!isset($this->options['port'])) {
            throw new InvalidArgumentException('Missing port for smtp transport');
        }
        if (!isset($this->options['username'])) {
            throw new InvalidArgumentException('Missing username for smtp transport');
        }
        if (!isset($this->options['password'])) {
            throw new InvalidArgumentException('Missing password for smtp transport');
        }
    }

    /**
     * Build SMTP transport.
     */
    private function buildSmtp(): void
    {
        $this->transport = (new Swift_SmtpTransport($this->options['host'], (int)$this->options['port']))
            ->setUsername($this->options['username'])
            ->setPassword($this->options['password']);
    }

    /**
     * Validate the sendmail configuration.
     */
    private function validateSendmail(): void
    {
        if (!isset($this->options['command'])) {
            throw new InvalidArgumentException('Missing command for sendmail transport');
        }
    }

    /**
     * Build sendmail.
     */
    private function buildSendmail(): void
    {
        $this->transport = new Swift_SendmailTransport($this->options['command']);
    }

    /**
     * Get the configured transport.
     *
     * @return Swift_Transport
     */
    public function getTransport(): Swift_Transport
    {
        if (null !== $this->transport) {
            return $this->transport;
        }
        switch ($this->options['transport']) {
            case 'null':
                $this->transport = new Swift_NullTransport();

                break;
            case 'smtp':
                $this->buildSmtp();

                break;
            case 'sendmail':
                $this->buildSendmail();

                break;
        }

        return $this->transport;
    }
}
