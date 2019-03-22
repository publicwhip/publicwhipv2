<?php
declare(strict_types = 1);

namespace PublicWhip\Providers;

use DebugBar\DebugBarException;
use Illuminate\Container\Container;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Events\Dispatcher;

/**
 * DatabaseProviderInterface.
 *
 * Common interface for anything that provides databases.
 */
interface DatabaseProviderInterface
{
    /**
     *  Configuration settings.
     *
     * @param array<string,string> $config
     */
    public function __construct(array $config);

    /**
     * Add a new connection.
     *
     * @param array<string,string> $config Configuration settings.
     * @param string|null $name Name of the configuration.
     */
    public function addConnection(array $config, ?string $name = null): void;

    /**
     * Set the event dispatcher.
     *
     * @param Dispatcher $dispatcher Dispatcher.
     */
    public function setEventDispatcher(Dispatcher $dispatcher): void;

    /**
     * Get the event dispatcher.
     *
     * @return Dispatcher|null
     */
    public function getEventDispatcher(): ?Dispatcher;

    /**
     * Get a table to start querying.
     *
     * @param string $table Name of the table.
     * @param Connection|null $connection Connection to use.
     * @return QueryBuilder
     */
    public function table(string $table, ?Connection $connection = null): QueryBuilder;

    /**
     * Get a named connection.
     *
     * @param string|null $name Name of the connection (or null for default)
     * @return Connection
     */
    public function getConnection(?string $name = null): Connection;

    /**
     * Get the schema builder.
     *
     * @param Connection|null $connection Connection to use.
     * @return SchemaBuilder
     */
    public function schema(?Connection $connection = null): SchemaBuilder;

    /**
     * Get an eloquent model.
     *
     * @param string $model Name of the model.
     * @return EloquentBuilder
     */
    public function query(string $model): EloquentBuilder;

    /**
     * Get the eloquent container.
     *
     * @return Container
     */
    public function getContainer(): Container;

    /**
     * Set the container.
     *
     * @param Container $container Set the eloquent container.
     */
    public function setContainer(Container $container): void;

    /**
     * Set the fetch mode.
     *
     * @param string $fetchMode Fetch mode.
     * @return DatabaseProviderInterface
     */
    public function setFetchMode(string $fetchMode): DatabaseProviderInterface;

    /**
     * Get the database manager.
     *
     * @return DatabaseManager
     */
    public function getDatabaseManager(): DatabaseManager;

    /**
     * Addable to a debugger.
     *
     * @param DebuggerProviderInterface $debugger Debugger to add.
     * @throws DebugBarException
     */
    public function addToDebugger(DebuggerProviderInterface $debugger): void;
}
