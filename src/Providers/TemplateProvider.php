<?php
declare(strict_types=1);

namespace PublicWhip\Providers;

use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;

/**
 * Class TemplateProvider
 */
final class TemplateProvider implements TemplateProviderInterface
{

    /**
     * @var Twig $twig Our rendering engine.
     */
    protected $twig;

    /**
     * TemplateProvider constructor.
     *
     * @param Twig $twig The template engine. Only twig currently supported.
     */
    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Output rendered template
     *
     * @param ResponseInterface $response Our response to populate.
     * @param string $template Template pathname relative to templates directory
     * @param array<string, mixed> $data Associative array of template variables
     *
     * @return ResponseInterface
     */
    public function render(ResponseInterface $response, string $template, array $data = []): ResponseInterface
    {
        return $this->twig->render($response, $template, $data);
    }
}
