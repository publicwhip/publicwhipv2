<?php
declare(strict_types = 1);

namespace PublicWhip\Providers;

use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\MessagesAggregateInterface;
use Throwable;

/**
 * DebuggerProviderInterface.
 *
 * Provides a debug bar common interface.
 */
interface DebuggerProviderInterface
{
    /**
     * @param bool $active Are we active?
     */
    public function __construct(bool $active);

    /**
     * Set the base url for output rendering.
     *
     * @param string $url Base Url.
     */
    public function setBaseUrl(string $url): void;

    /**
     * Add a new data collector to the debugger.
     *
     * @param DataCollectorInterface $collector The data collector we are adding.
     */
    public function addDataCollector(DataCollectorInterface $collector): void;

    /**
     * Add a new messages collector.
     *
     * @param MessagesAggregateInterface $collector The collector we are adding.
     */
    public function addMessagesAggregateCollector(MessagesAggregateInterface $collector): void;

    /**
     * Log a message.
     *
     * @param string $msg The message we are logging.
     * @param string|null $level The severity level we are logging.
     */
    public function addMessage(string $msg, ?string $level = null): void;

    /**
     * Add an exception.
     *
     * @param Throwable $throwable The throwable we are handling.
     */
    public function addThrowable(Throwable $throwable): void;

    /**
     * Get the base URL for output rendering.
     *
     * @return string
     */
    public function getBaseUrl(): string;

    /**
     * Get the base path for output rendering.
     *
     * @return string
     */
    public function getBasePath(): string;

    /**
     * Get the head text.
     *
     * @return string
     */
    public function renderHead(): string;

    /**
     * Get the actual debug bar.
     *
     * @return string
     */
    public function renderBar(): string;
}
