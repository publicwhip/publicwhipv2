<?php
declare(strict_types = 1);

namespace PublicWhip\Web\Controllers;

use Psr\Http\Message\ResponseInterface;
use PublicWhip\Providers\MailerProviderInterface;
use PublicWhip\Services\HansardServiceInterface;

/**
 * Class PingController.
 *
 * Uptime health checks.
 *
 */
class PingController
{

    /**
     * Simple uptime check.
     *
     * @param ResponseInterface $response The response to populate.
     *
     * @return ResponseInterface
     */
    public function indexAction(ResponseInterface $response): ResponseInterface
    {
        $body = $response->getBody();
        $body->write('ready');
        return $response;
    }

    /**
     * Returns the date of the last division processed.
     *
     * @param HansardServiceInterface $divisionService The devision service.
     * @param ResponseInterface $response The response to populate.
     *
     * @return ResponseInterface
     */
    public function lastDivisionParsedAction(
        HansardServiceInterface $divisionService,
        ResponseInterface $response
    ): ResponseInterface
    {
        $body = $response->getBody();
        $body->write($divisionService->getNewestDivisionDate());
        return $response;
    }

    /**
     * Send a test mail.
     *
     * @param MailerProviderInterface $mailer
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function testMailAction(MailerProviderInterface $mailer, ResponseInterface $response): ResponseInterface
    {
        $count = $mailer->sendMail(
            'Test subject',
            'test@example.com',
            'Testing name',
            'Generated at '.time()
        );
        $body = $response->getBody();
        $body->write('Sent ' . $count . ' test mails');
        return $response;
    }
}
