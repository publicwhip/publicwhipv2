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
     * @var Twig $twig Templating engine.
     */
    private $twig;

    /**
     * @var ServerRequestInterface $request
     */
    private $request;

    /**
     * @var DivisionServiceInterface $divisionService
     */
    private $divisionService;

    /**
     * DivisionController constructor.
     * @param Twig $twig
     * @param ServerRequestInterface $request
     * @param DivisionServiceInterface $divisionService
     */
    public function __construct(
        Twig $twig,
        ServerRequestInterface $request,
        DivisionServiceInterface $divisionService
    ) {
        $this->twig = $twig;
        $this->request = $request;
        $this->divisionService = $divisionService;
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function indexAction(ResponseInterface $response): ResponseInterface
    {
        return $this->twig->render($response, 'DivisionController/indexAction.twig', []);
    }


    /**
     * @param string $divisionId
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function showDivisionById(string $divisionId, ResponseInterface $response): ResponseInterface
    {
        $division = $this->divisionService->findByDivisionId((int)$divisionId);
        if (!$division) {
            throw new NotFoundException($this->request, $response);
        }
        return $this->twig->render(
            $response,
            'DivisionController/Division.twig',
            [
                'division' => $division
            ]
        );
    }

    /**
     * @param string $house
     * @param string $date
     * @param string $divisionId
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function showDivisionByDateAndNumberAction(
        string $house,
        string $date,
        string $divisionId,
        ResponseInterface $response
    ): ResponseInterface {
        $division = $this->divisionService->findByHouseDateAndNumber($house, $date, (int)$divisionId);
        if (!$division) {
            throw new NotFoundException($this->request, $response);
        }
        return $this->twig->render(
            $response,
            'DivisionController/Division.twig',
            [
                'division' => $division
            ]
        );
    }
}
