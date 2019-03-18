<?php
declare(strict_types=1);

namespace PublicWhip\Providers;

use DebugBar\DataCollector\PDO\PDOCollector;
use DebugBar\DataCollector\PDO\TraceablePDO;
use DebugBar\DebugBarException;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Fluent;
use PDO;

/**
 * Class DatabaseProvider.
 *
 * We just really do the minimum to get Eloquent up and running, yet it's quite complex and lots
 * of coupling.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class DatabaseProvider implements DatabaseProviderInterface
{

    /**
     * Name of the default connection.
     */
    private const DEFAULT_CONNECTION_NAME = 'default';

    /**
     * @var Container $container An Illuminate Container.
     */
    private $container;

    /**
     * @var DatabaseManager $manager
     */
    private $manager;

    /**
     * DatabaseProvider constructor.
     *
     * Turn off some PHPMD warnings as Eloquent requires static settings. As a Provider,
     * this isn't too much of a problem.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @param string[] $config Configuration settings.
     *
     */
    public function __construct(array $config)
    {
        $this->container = new Container();
        $this->container->instance('config', new Fluent());
        $this->container['config']['database.fetch'] = PDO::FETCH_OBJ;
        $this->container['config']['database.default'] = self::DEFAULT_CONNECTION_NAME;

        /**
         * Do a bit of type-juggling as the database manager expects an Application, but
         * only needs a container.
         *
         * @var Application $app
         */
        $app = $this->container;
        $this->manager = new DatabaseManager($app, new ConnectionFactory($this->container));

        $this->addConnection($config);

        $this->setEventDispatcher(new Dispatcher($this->container));
        Model::setConnectionResolver($this->manager);
        $dispatcher = $this->getEventDispatcher();
        if (null === $dispatcher) {
            return;
        }

        Model::setEventDispatcher($dispatcher);
    }

    /**
     * Add a new connection.
     *
     * @param string[] $config Configuration settings.
     * @param string|null $name Name of the configuration.
     *     */
    public function addConnection(array $config, ?string $name = null): void
    {
        $name = $name ?: self::DEFAULT_CONNECTION_NAME;
        $connections = $this->container['config']['database.connections'];
        $connections[$name] = $config;
        $this->container['config']['database.connections'] = $connections;
    }

    /**
     * Set the event dispatcher.
     *
     * @param Dispatcher $dispatcher Dispatcher.
     *     */
    public function setEventDispatcher(Dispatcher $dispatcher): void
    {
        $this->container->instance('events', $dispatcher);
    }

    /**
     * Get the event dispatcher.
     *
     * @return Dispatcher|null
     */
    public function getEventDispatcher(): ?Dispatcher
    {
        if ($this->container->bound('events')) {
            return $this->container['events'];
        }
        return null;
    }

    /**
     * Get a table to start querying.
     *
     * @param string $table Name of the table.
     * @param Connection|null $connection Connection to use.
     *
     * @return QueryBuilder
     */
    public function table(string $table, ?Connection $connection = null): QueryBuilder
    {
        $connectionName = $connection ? $connection->getName() : self::DEFAULT_CONNECTION_NAME;
        return $this->getConnection($connectionName)->table($table);
    }

    /**
     * Get a named connection.
     *
     * @param string|null $name Name of the connection (or null for default)
     *
     * @return Connection
     */
    public function getConnection(?string $name = null): Connection
    {
        return $this->manager->connection($name);
    }

    /**
     * Get the schema builder.
     *
     * @param Connection|null $connection Connection to use.
     *
     * @return SchemaBuilder
     */
    public function schema(?Connection $connection = null): SchemaBuilder
    {
        $connectionName = $connection ? $connection->getName() : self::DEFAULT_CONNECTION_NAME;
        return $this->getConnection($connectionName)->getSchemaBuilder();
    }

    /**
     * Get an eloquent model.
     *
     * @param string $model Name of the model.
     *
     * @return mixed
     */
    public function query(string $model)
    {
        $entity = str_replace(':', '\\Model\\', '\\' . $model);
        /** @var Model $entity */
        $entity = new $entity();
        return $entity->newQuery();
    }

    /**
     * Get the eloquent container.
     *
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Set the container.
     *
     * @param Container $container Set the eloquent container.
     *     */
    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }

    /**
     * Set the fetch mode.
     *
     * @param string $fetchMode Fetch mode.
     *
     * @return DatabaseProviderInterface
     */
    public function setFetchMode(string $fetchMode): DatabaseProviderInterface
    {
        $this->container['config']['database.fetch'] = $fetchMode;
        return $this;
    }

    /**
     * Get the database manager.
     *
     * @return DatabaseManager
     */
    public function getDatabaseManager(): DatabaseManager
    {
        return $this->manager;
    }

    /**
     * Addable to a debugger.
     *
     * @param DebuggerProviderInterface $debugger Debugger to add.
     *     *
     * @throws DebugBarException
     */
    public function addToDebugger(DebuggerProviderInterface $debugger): void
    {
        $connection = $this->getConnection();
        $traceablePdo = new TraceablePDO($connection->getPdo());
        $connection->setPdo($traceablePdo);
        $debugger->addDataCollector(new PDOCollector($traceablePdo));
    }
}
