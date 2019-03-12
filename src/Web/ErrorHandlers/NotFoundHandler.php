<?php
declare(strict_types=1);

namespace PublicWhip\Web\ErrorHandlers;

use Psr\Http\Message\ServerRequestInterface;
use PublicWhip\Providers\DebuggerProviderInterface;
use Slim\Handlers\NotFound;

/**
 * Class NotFoundHandler
 * @package PublicWhip\Web\ErrorHandlers
 */
class NotFoundHandler extends NotFound
{

    /**
     * @var DebuggerProviderInterface
     */
    private $debuggerProvider;

    /**
     * Constructor.
     * @param DebuggerProviderInterface $debuggerProvider
     */
    public function __construct(DebuggerProviderInterface $debuggerProvider)
    {
        $this->debuggerProvider = $debuggerProvider;
    }

    // phpcs:disable -- Slim currently has the wrong typehint on this method. Disabled CodeSniffer for now

    /**
     * Return a response for text/html content not found
     *
     * @codingStandardsIgnoreStart
     * @param ServerRequestInterface $request The most recent Request object
     *
     * @return string
     */
    protected function renderHtmlNotFoundOutput(ServerRequestInterface $request): string
    {
        $homeUrl = (string)$request->getUri()->withPath('')->withQuery('')->withFragment('');
        $text = <<<END
<html>
    <head>
        <title>Page Not Found</title>
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
        <h1>Page Not Found</h1>
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
    /**
     * phpcs:enable
     * @codingStandardsIgnoreEnd
     */
}
