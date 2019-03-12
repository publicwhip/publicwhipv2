<?php
declare(strict_types=1);

namespace PublicWhip\Providers;

use DebugBar\DebugBarException;
use Illuminate\Container\Container;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Events\Dispatcher;

/**
 * Interface DatabaseProviderInterface
 * @package PublicWhip\Providers
 */
interface DatabaseProviderInterface
{
    /**
     * DatabaseProvider constructor.
     * @param array $config
     */
    public function __construct(array $config);

    /**
     * @param array $config
     * @param string $name
     */
    public function addConnection(array $config, string $name = null);

    /**
     * @param string $name
     * @return Connection
     */
    public function getConnection(string $name = null): Connection;

    /**
     * @param string $table
     * @param Connection|null $connection
     * @return QueryBuilder
     */
    public function table(string $table, Connection $connection = null): QueryBuilder;

    /**
     * @param Connection|null $connection
     * @return SchemaBuilder
     */
    public function schema(Connection $connection = null): SchemaBuilder;

    /**
     * @param string $model
     * @return mixed
     */
    public function query(string $model);

    /**
     * @return Container
     */
    public function getContainer(): Container;

    /**
     * @param Container $container
     */
    public function setContainer(Container $container): void;

    /**
     * @return null|Dispatcher
     */
    public function getEventDispatcher(): ?Dispatcher;

    /**
     * @param Dispatcher $dispatcher
     */
    public function setEventDispatcher(Dispatcher $dispatcher): void;

    /**
     * @param string $fetchMode
     * @return DatabaseProviderInterface
     */
    public function setFetchMode(string $fetchMode): DatabaseProviderInterface;

    /**
     * @return DatabaseManager
     */
    public function getDatabaseManager(): DatabaseManager;

    /**
     * @param DebuggerProviderInterface $debugger
     * @throws DebugBarException
     */
    public function addToDebugger(DebuggerProviderInterface $debugger): void;
}
