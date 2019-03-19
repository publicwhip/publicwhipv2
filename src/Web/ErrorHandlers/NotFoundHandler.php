<?php
declare(strict_types = 1);

namespace PublicWhip\Web\ErrorHandlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PublicWhip\Providers\DebuggerProviderInterface;
use Slim\Handlers\AbstractHandler;
use Slim\Http\Body;
use UnexpectedValueException;

/**
 * Class NotFoundHandler
 *
 */
final class NotFoundHandler extends AbstractHandler
{

    /**
     * @var DebuggerProviderInterface The debugger.
     */
    private $debuggerProvider;

    /**
     * Constructor.
     *
     * @param DebuggerProviderInterface $debuggerProvider The debugger.
     */
    public function __construct(DebuggerProviderInterface $debuggerProvider)
    {
        $this->debuggerProvider = $debuggerProvider;
    }

    /**
     * Invoke not found handler
     *
     * @param ServerRequestInterface $request The most recent Request object
     * @param ResponseInterface $response The most recent Response object
     *
     * @return ResponseInterface
     * @throws UnexpectedValueException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        // set some defaults
        $contentType = 'text/plain';
        $output = self::renderPlainNotFoundOutput();

        if ('OPTIONS' !== $request->getMethod()) {
            $contentType = $this->determineContentType($request);
            switch ($contentType) {
                case 'application/json':
                    $output = self::renderJsonNotFoundOutput();
                    break;

                case 'text/html':
                    $output = $this->renderHtmlNotFoundOutput($request);
                    break;

                default:
                    throw new UnexpectedValueException('Cannot render unknown content type ' . $contentType);
            }
        }

        /** @var resource $fopen */
        $fopen = fopen('php://temp', 'rb+');
        $body = new Body($fopen);
        $body->write($output);

        return $response->withStatus(404)
            ->withHeader('Content-type', $contentType)
            ->withBody($body);
    }


    /**
     * Render plain not found message
     *
     * @return string
     */
    private static function renderPlainNotFoundOutput(): string
    {
        return 'Not found';
    }

    /**
     * Return a response for application/json content not found
     *
     * @return string
     */
    private static function renderJsonNotFoundOutput(): string
    {
        return '{"message":"Not found"}';
    }

    /**
     * Return a response for text/html content not found
     *
     * @param ServerRequestInterface $request The most recent Request object
     *
     * @return string
     */
    private function renderHtmlNotFoundOutput(ServerRequestInterface $request): string
    {
        $homeUrl = (string)$request->getUri()->withPath('')->withQuery('')->withFragment('');
        $text = <<<END
<html>
    <head>
        <title>PublicWhip Page Not Found</title>
        <style>
            body{
                margin:0;
                padding:30px;
                font:12px/1.5 Helvetica,Arial,Verdana,sans-serif;
            }
            h1{
                margin:0;
                font-size:48px;
                font-weight:normal;
                line-height:48px;
            }
            strong{
                display:inline-block;
                width:65px;
            }
        </style>
        %s
    </head>
    <body>
        <h1>PublicWhip Page Not Found</h1>
        <p>
            The page you are looking for could not be found. Check the address bar
            to ensure your URL is spelled correctly. If all else fails, you can
            visit our home page at the link below.
        </p>
        <a href="%s">Visit the Home Page</a>
        %s
    </body>
</html>
END;
        return sprintf($text, $this->debuggerProvider->renderHead(), $homeUrl, $this->debuggerProvider->renderBar());
    }
}
