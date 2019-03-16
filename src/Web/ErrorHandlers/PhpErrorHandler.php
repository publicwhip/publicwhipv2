<?php
declare(strict_types=1);

namespace PublicWhip\Web\ErrorHandlers;

use PublicWhip\Providers\DebuggerProviderInterface;
use Slim\Handlers\PhpError;
use Throwable;

/**
 * Class PhpErrorHandler
 */
class PhpErrorHandler extends PhpError
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
     * Render HTML error page
     *
     * @param Throwable $error The error that was thrown.
     *
     * @return string
     */
    protected function renderHtmlErrorMessage(Throwable $error): string
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
            "<title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana," .
            "sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{" .
            "display:inline-block;width:65px;}</style>%s</head><body><h1>%s</h1>%s%s</body></html>",
            $title,
            $debugHead,
            $title,
            $html,
            $debugBar
        );

        return $output;
    }
}
