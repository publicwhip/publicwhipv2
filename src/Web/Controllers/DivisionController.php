<?php
declare(strict_types=1);

namespace PublicWhip\Web\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PublicWhip\Providers\TemplateProviderInterface;
use PublicWhip\Services\DivisionServiceInterface;
use ReflectionException;
use Slim\Exception\NotFoundException;

/**
 * Class DivisionController.
 *
 */
class DivisionController
{

    /**
     * @var TemplateProviderInterface $templateProvider Templating engine.
     */
    private $templateProvider;

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
     *
     * @param TemplateProviderInterface $templateProvider The templating system provider.
     * @param ServerRequestInterface $request The actual server request information.
     * @param DivisionServiceInterface $divisionService The division service.
     */
    public function __construct(
        TemplateProviderInterface $templateProvider,
        ServerRequestInterface $request,
        DivisionServiceInterface $divisionService
    )
    {
        $this->templateProvider = $templateProvider;
        $this->request = $request;
        $this->divisionService = $divisionService;
    }

    /**
     * Just show a basic page for now.
     *
     * @param ResponseInterface $response The inbound request.
     *
     * @return ResponseInterface
     */
    public function indexAction(ResponseInterface $response): ResponseInterface
    {
        return $this->templateProvider->render($response, 'DivisionController/indexAction.twig');
    }

    /**
     * Show a division by it's id.
     *
     * @param string $divisionId Has to support string as this is passed from the frontend.
     * @param ResponseInterface $response The response to populate.
     *
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function showDivisionById(string $divisionId, ResponseInterface $response): ResponseInterface
    {
        $division = $this->divisionService->findByDivisionId((int)$divisionId);
        if (!$division) {
            throw new NotFoundException($this->request, $response);
        }
        return $this->templateProvider->render(
            $response,
            'DivisionController/Division.twig',
            [
                'division' => $division
            ]
        );
    }

    /**
     * Show a division by it's house, date and number.
     *
     * @param string $house Name of the house.
     * @param string $date Date (in YYYY-MM-DD format)
     * @param string $divisionNumber The division number.
     * @param ResponseInterface $response The response to populate.
     *
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function showDivisionByDateAndNumberAction(
        string $house,
        string $date,
        string $divisionNumber,
        ResponseInterface $response
    ): ResponseInterface
    {
        $division = $this->divisionService->findByHouseDateAndNumber($house, $date, (int)$divisionNumber);
        if (!$division) {
            throw new NotFoundException($this->request, $response);
        }
        return $this->templateProvider->render(
            $response,
            'DivisionController/Division.twig',
            [
                'division' => $division
            ]
        );
    }
}
