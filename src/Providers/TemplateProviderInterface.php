<?php
declare(strict_types = 1);

namespace PublicWhip\Providers;

use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;

/**
 * Templating engine provider.
 * TemplateProviderInterface.
 */
interface TemplateProviderInterface
{
    /**
     * @param Twig $twig The template engine. Only twig currently supported.
     */
    public function __construct(Twig $twig);

    /**
     * Output rendered template
     *
     * @param ResponseInterface $response Our response to populate.
     * @param string $template Template pathname relative to templates directory
     * @param array<string, string|int|array|bool|object>|null $data Associative array of template variables
     * @return ResponseInterface
     */
    public function render(ResponseInterface $response, string $template, ?array $data = null): ResponseInterface;
}
