<?php
declare(strict_types=1);

namespace PublicWhip\Web\ErrorHandlers;

use Exception;
use PublicWhip\Providers\DebuggerProviderInterface;
use Slim\Handlers\Error;

/**
 * Class ErrorHandler/
 * @package PublicWhip\Web\ErrorHandlers
 */
class ErrorHandler extends Error
{
    /**
     * @var DebuggerProviderInterface $debuggerProvider
     */
    private $debuggerProvider;

    /**
     * Constructor.
     * @param DebuggerProviderInterface $debuggerProvider
     * @param bool $displayErrorDetails
     */
    public function __construct(DebuggerProviderInterface $debuggerProvider, bool $displayErrorDetails)
    {
        $this->debuggerProvider = $debuggerProvider;
        parent::__construct($displayErrorDetails);
    }

    /**
     * Render HTML error page
     *
     * @param Exception $exception
     *
     * @return string
     */
    protected function renderHtmlErrorMessage(Exception $exception): string
    {
        $title = 'PublicWhip Application Exception';
        if ($this->displayErrorDetails) {
            $html = '<p>The application could not run because of the following exception:</p>';
            $html .= '<h2>Details</h2>';
            $html .= $this->renderHtmlException($exception);

            $previous = $exception->getPrevious();
            while ($previous instanceof Exception) {
                $html .= '<h2>Previous exception</h2>';
                $html .= $this->renderHtmlException($previous);
                $previous = $previous->getPrevious();
            }
        } else {
            $html = '<p>A website error has occurred. Sorry for the temporary inconvenience.</p>';
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
