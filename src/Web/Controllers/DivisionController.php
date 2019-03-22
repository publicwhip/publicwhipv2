<?php
declare(strict_types = 1);

namespace PublicWhip\Web\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PublicWhip\Entities\HansardEntity;
use PublicWhip\Providers\TemplateProviderInterface;
use PublicWhip\Services\DivisionVoteSummaryServiceInterface;
use PublicWhip\Services\HansardServiceInterface;
use PublicWhip\Services\MotionServiceInterface;
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
     * @var HansardServiceInterface $hansardService
     */
    private $hansardService;

    /**
     * @var DivisionVoteSummaryServiceInterface
     */
    private $divisionVoteSummaryService;

    /**
     * @var MotionServiceInterface
     */
    private $motionService;
    /**
     * DivisionController constructor.
     *
     * @param TemplateProviderInterface $templateProvider The templating system provider.
     * @param ServerRequestInterface $request The actual server request information.
     * @param HansardServiceInterface $hansardService The division service.
     */
    public function __construct(
        TemplateProviderInterface $templateProvider,
        ServerRequestInterface $request,
        HansardServiceInterface $hansardService,
    DivisionVoteSummaryServiceInterface $divisionVoteSummaryService,
    MotionServiceInterface $motionService
    )
    {
        $this->templateProvider = $templateProvider;
        $this->request = $request;
        $this->hansardService = $hansardService;
        $this->divisionVoteSummaryService=$divisionVoteSummaryService;
        $this->motionService=$motionService;
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
     * Populate a division with summary and motion text.
     *
     * @param HansardEntity $hansard Input division.
     * @return array Ready for output to templates or JSON.
     */
    private function populateDivisionArray(HansardEntity $hansard) {

        $motion=$this->motionService->getLatestForDivision($hansard->getId());
        $return=[
            'id'=>$hansard->getId(),
            'date'=>$hansard->getDate(),
            'number'=>$hansard->getNumber(),
            'house'=>$hansard->getHouse(),
            'title'=>$motion->getTitle(),
            'description'=>$motion->getMotion(),
            'officialTitle'=>$hansard->getTitle(),
            'officialText'=>$hansard->getText(),
            'debateUrl'=>$hansard->getDebateUrl(),
            'sourceUrl'=>$hansard->getSourceUrl()
        ];
        $summaryVotes=$this->divisionVoteSummaryService->getForDivision($hansard->getId());
        if ($summaryVotes) {
            $return['possibleTurnout']=$summaryVotes->getPossibleTurnout();
            $return['turnout']=$summaryVotes->getTurnout();
            $return['rebellions']=$summaryVotes->getRebellions();
            $return['ayeMajority']=$summaryVotes->getAyeMajority();
        }
        return $return;
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
        $hansard = $this->hansardService->findByDivisionId((int)$divisionId);
        if (!$hansard) {
            throw new NotFoundException($this->request, $response);
        }
        return $this->templateProvider->render(
            $response,
            'DivisionController/Division.twig',
            [
                'division' => $this->populateDivisionArray($hansard)
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
    public function editDivisionByDateAndNumberAction(
        string $house,
        string $date,
        string $divisionNumber,
        ResponseInterface $response
    ): ResponseInterface
    {
        $hansard = $this->hansardService->findByHouseDateAndNumber($house, $date, (int)$divisionNumber);
        if (!$hansard) {
            throw new NotFoundException($this->request, $response);
        }
        return $this->templateProvider->render(
            $response,
            'DivisionController/DivisionEdit.twig',
            [
                'division' => $this->populateDivisionArray($hansard)
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
        $hansard = $this->hansardService->findByHouseDateAndNumber($house, $date, (int)$divisionNumber);
        if (!$hansard) {
            throw new NotFoundException($this->request, $response);
        }
        return $this->templateProvider->render(
            $response,
            'DivisionController/Division.twig',
            [
                'division' => $this->populateDivisionArray($hansard)
            ]
        );
    }
}
