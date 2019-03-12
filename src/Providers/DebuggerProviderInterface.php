<?php
declare(strict_types=1);

namespace PublicWhip\Providers;

use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\MessagesAggregateInterface;
use DebugBar\DebugBarException;
use Exception;

/**
 * Class DebuggerProviderInterface
 * @package PublicWhip\Providers
 */
interface DebuggerProviderInterface
{

    /**
     * DebuggerProvider constructor.
     * @param bool $active
     */
    public function __construct(bool $active);

    /**
     * @param DataCollectorInterface $collector
     * @throws DebugBarException
     */
    public function addDataCollector(DataCollectorInterface $collector): void;

    /**
     * @param MessagesAggregateInterface $collector
     */
    public function addMessagesAggregateCollector(MessagesAggregateInterface $collector): void;

    /**
     * @param string $msg
     * @param string $level
     */
    public function addMessage(string $msg, string $level = null): void;


    /**
     * @param Exception $e
     */
    public function addException(Exception $e) : void;

    /**
     * @param string $url
     */
    public function setBaseUrl(string $url): void;

    /**
     * @return string
     */
    public function getBaseUrl(): string;

    /**
     * @return string
     */
    public function getBasePath(): string;

    /**
     * @return string
     */
    public function renderHead(): string;

    /**
     * @return string
     */
    public function renderBar(): string;
}
