<?php
declare(strict_types=1);

namespace PublicWhip\Web\Controllers;

use Psr\Http\Message\ResponseInterface;
use PublicWhip\Providers\TemplateProviderInterface;

/**
 * Class IndexController
 *
 */
class IndexController
{

    /**
     * @param TemplateProviderInterface $templateProvider The templating engine.
     * @param ResponseInterface $response The response to send back.
     *
     * @return ResponseInterface
     */
    public function indexAction(
        TemplateProviderInterface $templateProvider,
        ResponseInterface $response
    ): ResponseInterface
    {
        return $templateProvider->render($response, 'IndexController/indexAction.twig', []);
    }
}
