<?php
declare(strict_types = 1);

namespace PublicWhip\Providers;

use Swift_Transport;

/**
 * MailerTransportProvider.
 */
interface MailerTransportProviderInterface
{
    /**
     * Get the configured transport.
     *
     * @return Swift_Transport
     */
    public function getTransport(): Swift_Transport;
}
