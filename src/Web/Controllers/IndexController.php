<?php
declare(strict_types=1);

namespace PublicWhip\Web\Controllers;

use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;

/**
 * Class IndexController
 * @package PublicWhip\Web\Controllers
 */
class IndexController
{

    /**
     * @param Twig $twig
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function indexAction(Twig $twig, ResponseInterface $response)
    {
        return $twig->render($response, 'IndexController/indexAction.twig', []);
    }
}
