<?php
declare(strict_types=1);

namespace PublicWhip\Web\Controllers;

use Psr\Http\Message\ResponseInterface;
use PublicWhip\Model\Division;
use PublicWhip\Services\DivisionServiceInterface;

/**
 * Class PingController.
 *
 * Uptime health checks.
 * @package PublicWhip\Web\Controllers
 */
class PingController
{

    /**
     * Simple uptime check.
     * @param ResponseInterface $response
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
     * @param DivisionServiceInterface $divisionService
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function lastDivisionParsedAction(
        DivisionServiceInterface $divisionService,
        ResponseInterface $response
    ): ResponseInterface {
        $body = $response->getBody();
        $body->write($divisionService->getNewestDivisionDate());
        return $response;
    }
}
