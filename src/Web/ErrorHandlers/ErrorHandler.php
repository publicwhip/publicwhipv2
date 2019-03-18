<?php
declare(strict_types=1);

namespace PublicWhip\Web\ErrorHandlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PublicWhip\Providers\DebuggerProviderInterface;
use Slim\Handlers\AbstractError;
use Slim\Http\Body;
use Throwable;
use UnexpectedValueException;
use function get_class;
use function is_string;

/**
 * Class ErrorHandler.
 *
 * Mainly based straight of Slim's Error class for now.
 *
 */
final class ErrorHandler extends AbstractError
{

    /**
     * @var DebuggerProviderInterface $debuggerProvider The debugger.
     */
    private $debuggerProvider;

    /**
     * Constructor.
     *
     * @param DebuggerProviderInterface $debuggerProvider The debugger.
     * @param bool $displayErrorDetails Should we show error details. Should be false on production.
     */
    public function __construct(DebuggerProviderInterface $debuggerProvider, bool $displayErrorDetails)
    {
        $this->debuggerProvider = $debuggerProvider;
        parent::__construct($displayErrorDetails);
    }

    /**
     * Invoke error handler
     *
     * @param ServerRequestInterface $request The most recent Request object
     * @param ResponseInterface $response The most recent Response object
     * @param Throwable $throwable The caught Throwable object
     *
     * @return ResponseInterface
     * @throws UnexpectedValueException
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        Throwable $throwable
    ): ResponseInterface
    {
        $contentType = $this->determineContentType($request);
        switch ($contentType) {
            case 'application/json':
                $output = $this->renderJsonErrorMessage($throwable);
                break;

            case 'text/html':
                $output = $this->renderHtmlErrorMessage($throwable);
                break;

            default:
                throw new UnexpectedValueException('Cannot render unknown content type ' . $contentType);
        }

        $this->writeToErrorLog($throwable);

        /** @var resource $fopen */
        $fopen = fopen('php://temp', 'rb+');
        $body = new Body($fopen);
        $body->write($output);

        return $response->withStatus(500)
            ->withHeader('Content-type', $contentType)
            ->withBody($body);
    }

    /**
     * Render HTML error page
     *
     * @param Throwable $throwable The thrown item.
     *
     * @return string
     */
    private function renderHtmlErrorMessage(Throwable $throwable): string
    {
        $title = 'PublicWhip Application Exception';
        $html = '<p>A website error has occurred. Sorry for the temporary inconvenience.</p>';

        if ($this->displayErrorDetails) {
            $html = '<p>The application could not run because of the following exception:</p>';
            $html .= '<h2>Details</h2>';
            $html .= $this->renderHtmlExceptionOrError($throwable);

            $previous = $throwable->getPrevious();
            while ($previous instanceof Throwable) {
                $html .= '<h2>Previous exception</h2>';
                $html .= $this->renderHtmlExceptionOrError($previous);
                $previous = $previous->getPrevious();
            }
        }
        $debugHead = $this->debuggerProvider->renderHead();
        $debugBar = $this->debuggerProvider->renderBar();

        $output = sprintf(
            "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" .
            '<title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,' .
            'sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{' .
            'display:inline-block;width:65px;}</style>%s</head><body><h1>%s</h1>%s%s</body></html>',
            $title,
            $debugHead,
            $title,
            $html,
            $debugBar
        );

        return $output;
    }

    /**
     * Render exception or error as HTML.
     *
     * @param Throwable $throwable The thrown item.
     *
     * @return string
     */
    private function renderHtmlExceptionOrError(Throwable $throwable): string
    {
        $html = sprintf('<div><strong>Type:</strong> %s</div>', get_class($throwable));

        if ($throwable->getCode()) {
            $html .= sprintf('<div><strong>Code:</strong> %s</div>', $throwable->getCode());
        }

        if ($throwable->getMessage()) {
            $html .= sprintf(
                '<div><strong>Message:</strong> %s</div>',
                htmlentities($throwable->getMessage(), ENT_QUOTES)
            );
        }

        if ($throwable->getFile()) {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $throwable->getFile());
        }

        if ($throwable->getLine()) {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', $throwable->getLine());
        }

        if ($throwable->getTraceAsString()) {
            $html .= '<h2>Trace</h2>';
            $html .= sprintf(
                '<pre>%s</pre>',
                htmlentities($throwable->getTraceAsString(), ENT_QUOTES)
            );
        }

        return $html;
    }

    /**
     * Render JSON error
     *
     * @param Throwable $throwable The thrown item.
     *
     * @return string
     */
    private function renderJsonErrorMessage(Throwable $throwable): string
    {
        $error = [
            'message' => 'PublicWhip Application Error'
        ];

        if ($this->displayErrorDetails) {
            $error['exception'] = [];

            do {
                $error['exception'][] = [
                    'type' => get_class($throwable),
                    'code' => $throwable->getCode(),
                    'message' => $throwable->getMessage(),
                    'file' => $throwable->getFile(),
                    'line' => $throwable->getLine(),
                    'trace' => explode("\n", $throwable->getTraceAsString())
                ];
                $throwable = $throwable->getPrevious();
            } while ($throwable);
        }

        $encoded = json_encode($error, JSON_PRETTY_PRINT);
        if (!is_string($encoded)) {
            $encoded = '[]';
        }
        return $encoded;
    }
}
