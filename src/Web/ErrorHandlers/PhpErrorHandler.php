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

/**
 * Class PhpErrorHandler
 */
final class PhpErrorHandler extends AbstractError
{

    /**
     * @var DebuggerProviderInterface The debugger.
     */
    private $debuggerProvider;

    /**
     * Constructor.
     *
     * @param DebuggerProviderInterface $debuggerProvider The debugger.
     * @param bool $displayErrorDetails Should we display error messages (should be disabled in production).
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
     * @param Throwable $error The caught Throwable object
     *
     * @return ResponseInterface
     * @throws UnexpectedValueException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, Throwable $error)
    {
        $contentType = $this->determineContentType($request);
        switch ($contentType) {
            case 'application/json':
                $output = $this->renderJsonErrorMessage($error);
                break;

            case 'text/html':
                $output = $this->renderHtmlErrorMessage($error);
                break;
            default:
                throw new UnexpectedValueException('Cannot render unknown content type ' . $contentType);
        }

        $this->writeToErrorLog($error);

        /** @var resource $fopen */
        $fopen = fopen('php://temp', 'rb+');
        $body = new Body($fopen);
        $body->write($output);

        return $response
            ->withStatus(500)
            ->withHeader('Content-type', $contentType)
            ->withBody($body);
    }

    /**
     * Render HTML error page
     *
     * @param Throwable $error The error that was thrown.
     *
     * @return string
     */
    private function renderHtmlErrorMessage(Throwable $error): string
    {
        $title = 'PublicWhip Application Error - Major Error';

        $html = '<p>A website error has occurred. Sorry for the temporary inconvenience.</p>';
        if ($this->displayErrorDetails) {
            $html = '<p>The application could not run because of the following error:</p>';
            $html .= '<h2>Details</h2>';
            $html .= $this->renderHtmlError($error);

            $error = $error->getPrevious();
            while ($error) {
                $html .= '<h2>Previous error</h2>';
                $html .= $this->renderHtmlError($error);
                $error = $error->getPrevious();
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
     * Render error as HTML.
     *
     * @param Throwable $error
     *
     * @return string
     */
    private function renderHtmlError(Throwable $error): string
    {
        $html = sprintf('<div><strong>Type:</strong> %s</div>', get_class($error));

        $code = $error->getCode();
        if ($code) {
            $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        }

        $message = $error->getMessage();
        if ($message) {
            $html .= sprintf('<div><strong>Message:</strong> %s</div>', htmlentities($message, ENT_QUOTES));
        }
        $file = $error->getFile();
        if ($file) {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
        }
        $line = $error->getLine();
        if ($line) {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
        }
        $trace = $error->getTraceAsString();
        if ($trace) {
            $html .= '<h2>Trace</h2>';
            $html .= sprintf('<pre>%s</pre>', htmlentities($trace, ENT_QUOTES));
        }

        return $html;
    }

    /**
     * Render JSON error
     *
     * @param Throwable $error
     *
     * @return string
     */
    private function renderJsonErrorMessage(Throwable $error): string
    {
        $json = [
            'message' => 'PublicWhip Application Error'
        ];

        if ($this->displayErrorDetails) {
            $json['error'] = [];

            $error = $error->getPrevious();
            while ($error) {
                $json['error'][] = [
                    'type' => get_class($error),
                    'code' => $error->getCode(),
                    'message' => $error->getMessage(),
                    'file' => $error->getFile(),
                    'line' => $error->getLine(),
                    'trace' => explode("\n", $error->getTraceAsString())
                ];
                $error = $error->getPrevious();
            }
        }

        $encoded = json_encode($json, JSON_PRETTY_PRINT);
        if (false === $encoded) {
            return '{"message":"Unrecoverable server error"}';
        }
        return $encoded;
    }
}
