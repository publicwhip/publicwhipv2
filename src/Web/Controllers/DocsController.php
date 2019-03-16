<?php
declare(strict_types=1);

namespace PublicWhip\Web\Controllers;

use Parsedown;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use PublicWhip\Providers\TemplateProviderInterface;
use Slim\Exception\NotFoundException;

/**
 * Class DocsController
 *
 */
class DocsController
{

    /**
     * @var Parsedown $parseDown Our markdown parser.
     */
    private $parseDown;

    /**
     * @var string $fileRoot The root of our files.
     */
    private $fileRoot;

    /**
     * @var LoggerInterface $logger The logger.
     */
    private $logger;

    /**
     * @var TemplateProviderInterface $templateProvider The templating engine.
     */
    private $templateProvider;

    /**
     * DocsController constructor.
     *
     * @param Parsedown $parseDown The Markdown parser.
     * @param TemplateProviderInterface $templateProvider The templating engine provider.
     * @param LoggerInterface $logger The logger.
     */
    public function __construct(
        Parsedown $parseDown,
        TemplateProviderInterface $templateProvider,
        LoggerInterface $logger
    )
    {
        $this->parseDown = $parseDown;
        $this->templateProvider = $templateProvider;
        $this->logger = $logger;
        $this->fileRoot = __DIR__ . DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
    }

    /**
     * Render a page.
     *
     * @param ServerRequestInterface $request The inbound request.
     * @param ResponseInterface $response The response to populate.
     * @param string $file Which file was requested.
     *
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function render(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $file
    ): ResponseInterface
    {
        if ('' === $file) {
            $file = 'README.md';
        }
        $allowedFiles = [
            'README.md',
            'docs/Milestones.md',
            'docs/ChangeLog.md',
            'LICENSE.txt',
            'docs/CODE_OF_CONDUCT.md',
            'docs/CONTRIBUTING.md',
            'docs/Contact.md',
            'docs/QuickStart.md'
        ];
        if (!\in_array($file, $allowedFiles, true)) {
            throw new NotFoundException($request, $response);
        }
        $fullPath = $this->fileRoot . $file;
        $this->logger->info('Reading {fullPath}', ['fullPath' => $fullPath]);
        $text = $this->parseDown->text(file_get_contents($fullPath));
        $this->logger->info('Read {fullPath} - parsed', ['fullPath' => $fullPath, 'text' => $text]);
        return $this->templateProvider->render($response, 'DocsController/main.twig', [
            'file' => $file,
            'markdownText' => $text
        ]);
    }
}
