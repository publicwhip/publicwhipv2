<?php
declare(strict_types=1);

namespace PublicWhip\Web\Controllers;

use Psr\Http\Message\ResponseInterface;
use PublicWhip\Services\DivisionServiceInterface;

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
     * @param DivisionServiceInterface $divisionService The devision service.
     * @param ResponseInterface $response The response to populate.
     *
     * @return ResponseInterface
     */
    public function lastDivisionParsedAction(
        DivisionServiceInterface $divisionService,
        ResponseInterface $response
    ): ResponseInterface
    {
        $body = $response->getBody();
        $body->write($divisionService->getNewestDivisionDate());
        return $response;
    }
}
