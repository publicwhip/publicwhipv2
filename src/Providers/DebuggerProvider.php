<?php
declare(strict_types=1);

namespace PublicWhip\Providers;

use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DataCollector\MessagesAggregateInterface;
use DebugBar\DebugBarException;
use DebugBar\JavascriptRenderer;
use DebugBar\StandardDebugBar;
use Exception;

/**
 * Class DebuggerProvider
 * @package PublicWhip\Providers
 */
final class DebuggerProvider implements DebuggerProviderInterface
{

    /**
     * @var StandardDebugBar|null $debugbar
     */
    private $debugbar;

    /**
     * @var JavascriptRenderer|null $renderer
     */
    private $renderer;


    /**
     * DebuggerProvider constructor.
     * @param bool $active
     */
    public function __construct(bool $active)
    {
        if ($active) {
            $this->debugbar = new StandardDebugBar();
            $this->renderer = $this->debugbar->getJavascriptRenderer();
            $this->setBaseUrl('/debugbar');
        }
    }

    /**
     * @param DataCollectorInterface $collector
     * @throws DebugBarException
     */
    public function addDataCollector(DataCollectorInterface $collector): void
    {
        if (null !== $this->debugbar) {
            $this->debugbar->addCollector($collector);
        }
    }

    /**
     * @param MessagesAggregateInterface $collector
     */
    public function addMessagesAggregateCollector(MessagesAggregateInterface $collector): void
    {
        if (null !== $this->debugbar) {
            $this->debugbar['message']->aggregate($collector);
        }
    }

    /**
     * @param string $msg
     * @param string $level
     */
    public function addMessage(string $msg, string $level = null): void
    {
        if (null !== $this->debugbar) {
            $level = $level ?: 'info';
            $this->debugbar['messages']->log($level, $msg);
        }
    }

    /**
     * @param Exception $e
     */
    public function addException(Exception $e) : void
    {
        if (null !== $this->debugbar) {
            $this->debugbar['exceptions']->addException($e);
        }
    }

    /**
     * @param string $url
     */
    public function setBaseUrl(string $url): void
    {
        if (null !== $this->renderer) {
            $this->renderer->setBaseUrl($url);
        }
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        if (null !== $this->renderer) {
            return $this->renderer->getBaseUrl();
        }
        return '';
    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        if (null !== $this->renderer) {
            return $this->renderer->getBasePath();
        }
        return '';
    }

    /**
     * @return string
     */
    public function renderHead(): string
    {
        if (null !== $this->renderer) {
            return $this->renderer->renderHead();
        }
        return '';
    }

    /**
     * @return string
     */
    public function renderBar(): string
    {
        if (null !== $this->renderer) {
            return $this->renderer->render();
        }
        return '';
    }
}
