<?php
declare(strict_types=1);

namespace PublicWhip\Providers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class DebuggerTwigExtension.
 *
 * Adds the debug bar to twig.
 */
final class DebuggerTwigExtension extends AbstractExtension
{

    /**
     * @var UriInterface $uri Our current url.
     */
    private $uri;

    /**
     * @var DebuggerProviderInterface $debugger The debugger.
     */
    private $debugger;

    /**
     * DebuggerTwigExtension constructor.
     *
     * @param RequestInterface $request The request.
     * @param DebuggerProviderInterface $debugger The debugger.
     */
    public function __construct(RequestInterface $request, DebuggerProviderInterface $debugger)
    {
        $this->uri = $request->getUri();
        $debugger->setBaseUrl($this->baseUrlFunction() . '/debugbar');
        $this->debugger = $debugger;
    }

    /**
     * Get the base url.
     * @return string
     */
    public function baseUrlFunction(): string
    {
        if (method_exists($this->uri, 'getBaseUrl')) {
            return $this->uri->getBaseUrl();
        }
        return '';
    }

    /**
     * Get the twig functions
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('debug_head', [$this, 'debugHeadFunction'], ['is_safe' => ['html']]),
            new TwigFunction('debug_bar', [$this, 'debugBarFunction'], ['is_safe' => ['html']])
        ];
    }

    /**
     * Get the debug header.
     * @return string
     */
    public function debugHeadFunction(): string
    {
        return $this->debugger->renderHead();
    }

    /**
     * Get the debug bar main body.
     * @return string
     */
    public function debugBarFunction(): string
    {
        return $this->debugger->renderBar();
    }
}
