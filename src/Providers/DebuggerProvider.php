<?php
declare(strict_types=1);

namespace PublicWhip\Providers;

use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\MessagesAggregateInterface;
use DebugBar\DebugBarException;
use DebugBar\JavascriptRenderer;
use DebugBar\StandardDebugBar;
use Throwable;

/**
 * Class DebuggerProvider
 *
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
     *
     * @param bool $active Are we active?
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
     * Set the base url for output rendering.
     *
     * @param string $url Base Url.
     *
     * @return void
     */
    public function setBaseUrl(string $url): void
    {
        if (null !== $this->renderer) {
            $this->renderer->setBaseUrl($url);
        }
    }

    /**
     * Add a new data collector to the debugger.
     *
     * @param DataCollectorInterface $collector The data collector we are adding.
     *
     * @return void
     *
     * @throws DebugBarException
     */
    public function addDataCollector(DataCollectorInterface $collector): void
    {
        if (null !== $this->debugbar) {
            $this->debugbar->addCollector($collector);
        }
    }

    /**
     * Add a new messages collector.
     *
     * @param MessagesAggregateInterface $collector The collector we are adding.
     *
     * @return void
     */
    public function addMessagesAggregateCollector(MessagesAggregateInterface $collector): void
    {
        if (null !== $this->debugbar) {
            $this->debugbar['message']->aggregate($collector);
        }
    }

    /**
     * Log a message.
     *
     * @param string $msg The message we are logging.
     * @param string|null $level The severity level we are logging.
     *
     * @return void
     */
    public function addMessage(string $msg, ?string $level = null): void
    {
        if (null !== $this->debugbar) {
            $level = $level ?: 'info';
            $this->debugbar['messages']->log($level, $msg);
        }
    }

    /**
     * Add an exception.
     *
     * @param Throwable $throwable The throwable we are handling.
     *
     * @return void
     */
    public function addThrowable(Throwable $throwable): void
    {
        if (null !== $this->debugbar) {
            $this->debugbar['exceptions']->addException($throwable);
        }
    }

    /**
     * Get the base URL for output rendering.
     *
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
     * Get the base path for output rendering.
     *
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
     * Get the head text.
     *
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
     * Get the actual debug bar.
     *
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
