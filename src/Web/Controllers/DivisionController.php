<?php
declare(strict_types=1);

namespace PublicWhip\Web\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PublicWhip\Services\DivisionServiceInterface;
use Slim\Exception\NotFoundException;
use Slim\Views\Twig;

/**
 * Class DivisionController.
 *
 * @package PublicWhip\Web\Controllers
 */
class DivisionController
{

    /**
     * @param Twig $twig
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function indexAction(Twig $twig, ResponseInterface $response): ResponseInterface
    {
        return $twig->render($response, 'DivisionController/indexAction.twig', []);
    }


    /**
     * @param string $house
     * @param string $date
     * @param string $divisionId
     * @param DivisionServiceInterface $divisionService
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param Twig $twig
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function showDivisionByDateAndNumberAction(
        string $house,
        string $date,
        string $divisionId,
        DivisionServiceInterface $divisionService,
        ServerRequestInterface $request,
        ResponseInterface $response,
        Twig $twig
    ): ResponseInterface {
        $division = $divisionService->findByHouseDateAndNumber($house, $date, (int)$divisionId);
        if (!$division) {
            throw new NotFoundException($request, $response);
        }
        return $twig->render(
            $response,
            'DivisionController/Division.twig',
            [
                'division' => $division
            ]
        );
    }
}
