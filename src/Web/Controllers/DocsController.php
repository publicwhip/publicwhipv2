<?php
declare(strict_types=1);

namespace PublicWhip\Web\Controllers;

use Parsedown;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\NotFoundException;
use Slim\Views\Twig;

/**
 * Class DocsController
 * @package PublicWhip\Web\Controllers
 */
class DocsController
{

    /**
     * @var Parsedown $parseDown
     */
    private $parseDown;
    /**
     * @var string $fileRoot
     */
    private $fileRoot;
    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var Twig %twig
     */
    private $twig;

    /**
     * DocsController constructor.
     * @param Parsedown $parseDown
     * @param Twig $twig
     * @param LoggerInterface $logger
     */
    public function __construct(Parsedown $parseDown, Twig $twig, LoggerInterface $logger)
    {
        $this->parseDown = $parseDown;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->fileRoot = __DIR__ . DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
    }

    /**
     * Render a page.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param string $file
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function render(ServerRequestInterface $request, ResponseInterface $response, string $file)
    {
        if ('' === $file) {
            $file = 'README.md';
        }
        $allowedFiles = [
            'README.md',
            'docs/Milestones.md',
            'LICENSE.txt',
            'docs/CODE_OF_CONDUCT.md',
            'docs/CONTRIBUTING.md',
            'docs/Contact.md',
            'docs/QuickStart.md'
        ];
        if (!in_array($file, $allowedFiles)) {
            throw new NotFoundException($request, $response);
        }
        $fullPath = $this->fileRoot . $file;
        $this->logger->info('Reading {fullPath}', ['fullPath' => $fullPath]);
        $text = $this->parseDown->text(file_get_contents($fullPath));
        $this->logger->info('Read {fullPath} - parsed', ['fullPath' => $fullPath, 'text' => $text]);
        return $this->twig->render($response, 'DocsController/main.twig', [
            'file' => $file,
            'content' => $text
        ]);
    }
}
