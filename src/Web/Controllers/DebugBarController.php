<?php
declare(strict_types=1);

namespace PublicWhip\Web\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use PublicWhip\Providers\DebuggerProviderInterface;
use Slim\Exception\NotFoundException;
use Slim\Http\Stream;
use Slim\HttpCache\CacheProvider;

/**
 * Class DebugBarController.
 *
 * Based off https://github.com/php-middleware/phpdebugbar .
 *
 * @package PublicWhip\Web\Controllers
 */
class DebugBarController
{
    /**
     * @var DebuggerProviderInterface $debuggerProvider
     */
    private $debuggerProvider;
    /**
     * @var LoggerInterface $logger
     */
    private $logger;
    /**
     * @var CacheProvider $cacheProvider
     */
    private $cacheProvider;

    /**
     * DebugBarController constructor.
     * @param DebuggerProviderInterface $debuggerProvider
     * @param LoggerInterface $logger
     * @param CacheProvider $cacheProvider
     */
    public function __construct(
        DebuggerProviderInterface $debuggerProvider,
        LoggerInterface $logger,
        CacheProvider $cacheProvider
    ) {
        $this->debuggerProvider = $debuggerProvider;
        $this->logger = $logger;
        $this->cacheProvider = $cacheProvider;
    }

    /**
     * Fetch a static file from the PHPDebugBar.
     *
     * Lots of security as we are exposing the file system to the public here.
     *
     * The debug bar should only be enabled on Development environments anyway.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param string $filePath
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function staticFileAction(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $filePath
    ): ResponseInterface {
        $fullPathToFile = $this->debuggerProvider->getBasePath() . DIRECTORY_SEPARATOR . $filePath;
        $this->logger->debug('Fetching {file}', ['file' => $fullPathToFile]);

        /**
         * Validate the path via realpath.
         */
        $realpath = realpath($fullPathToFile);
        if (false === $realpath
            || 0 !== strpos($realpath, $this->debuggerProvider->getBasePath() . DIRECTORY_SEPARATOR)
        ) {
            $this->logger->warning(
                'Invalid attempt to access debugbar file {file}: bad realpath',
                ['file' => $fullPathToFile]
            );
            throw new NotFoundException($request, $response);
        }


        /**
         * Check it actually exists (realpath should have done this anyway) and is readable.
         */
        if (!file_exists($realpath) || !is_readable($realpath)) {
            $this->logger->warning(
                'Invalid attempt to access debugbar file {file}: not readable',
                ['file' => $fullPathToFile]
            );
            throw new NotFoundException($request, $response);
        }
        /**
         * Get the content type, with more validation if we get something we don't recognise.
         */
        $contentType = $this->getContentTypeByFileName($realpath);
        if ('' === $contentType) {
            $this->logger->warning(
                'Invalid attempt to access debugbar file {file}: unrecognised content type',
                ['file' => $fullPathToFile]
            );
            throw new NotFoundException($request, $response);
        }

        $fileHandle = fopen($realpath, 'rb');
        if (false === $fileHandle) {
            $this->logger->warning(
                'Invalid attempt to access debugbar file {file}: could not open',
                ['file' => $fullPathToFile]
            );
            throw new NotFoundException($request, $response);
        }
        $stream = new Stream($fileHandle);
        $response = $response->withBody($stream)->withHeader('Content-Type', $contentType);
        return $this->cacheProvider->withExpires($response, time() + 60 * 60 * 6);
    }


    /**
     * @param string $filename
     * @return string
     */
    private function getContentTypeByFileName(string $filename): string
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $map = [
            'css' => 'text/css',
            'js' => 'text/javascript',
            'otf' => 'font/opentype',
            'eot' => 'application/vnd.ms-fontobject',
            'svg' => 'image/svg+xml',
            'ttf' => 'application/font-sfnt',
            'woff' => 'application/font-woff',
            'woff2' => 'application/font-woff2',
        ];
        return $map[$ext] ?? '';
    }
}
