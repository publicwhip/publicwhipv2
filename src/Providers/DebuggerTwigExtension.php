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
 *
 * @package PublicWhip\Providers
 */
class DebuggerTwigExtension extends AbstractExtension
{

    /**
     * @var UriInterface $uri
     */
    private $uri;

    /**
     * @var DebuggerProviderInterface $debugger
     */
    private $debugger;

    /**
     * DebuggerTwigExtension constructor.
     * @param RequestInterface $request
     * @param DebuggerProviderInterface $debugger
     */
    public function __construct(RequestInterface $request, DebuggerProviderInterface $debugger)
    {
        $this->uri = $request->getUri();
        $debugger->setBaseUrl($this->baseUrlFunction() . '/debugbar');
        $this->debugger = $debugger;
    }

    /**
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
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('debug_head', [$this, 'debugHeadFunction'], ['is_safe' => ['html']]),
            new TwigFunction('debug_bar', [$this, 'debugBarFunction'], ['is_safe' => ['html']])
        ];
    }

    /**
     * @return string
     */
    public function debugHeadFunction(): string
    {
        return $this->debugger->renderHead();
    }

    /**
     * @return string
     */
    public function debugBarFunction(): string
    {
        return $this->debugger->renderBar();
    }
}
