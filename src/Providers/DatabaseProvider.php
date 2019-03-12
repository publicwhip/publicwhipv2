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
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Fluent;
use PDO;

/**
 * Class DatabaseProvider.
 * @package PublicWhip\Providers
 */
final class DatabaseProvider implements DatabaseProviderInterface
{

    /**
     * Name of the default connection.
     */
    private const DEFAULT_CONNECTION_NAME = 'default';

    /**
     * @var Container $container
     */
    private $container;

    /**
     * @var DatabaseManager $manager
     */
    private $manager;

    /**
     * DatabaseProvider constructor.
     * @param array $config
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
        Eloquent::setConnectionResolver($this->manager);
        $dispatcher = $this->getEventDispatcher();
        if (null !== $dispatcher) {
            Eloquent::setEventDispatcher($dispatcher);
        }
    }

    /**
     * @param array $config
     * @param string $name
     */
    public function addConnection(array $config, string $name = null): void
    {
        $name = $name ?: self::DEFAULT_CONNECTION_NAME;
        $connections = $this->container['config']['database.connections'];
        $connections[$name] = $config;
        $this->container['config']['database.connections'] = $connections;
    }

    /**
     * @param Dispatcher $dispatcher
     */
    public function setEventDispatcher(Dispatcher $dispatcher): void
    {
        $this->container->instance('events', $dispatcher);
    }

    /**
     * @return null|Dispatcher
     */
    public function getEventDispatcher(): ?Dispatcher
    {
        if ($this->container->bound('events')) {
            return $this->container['events'];
        }
        return null;
    }

    /**
     * @param string $table
     * @param Connection|null $connection
     * @return QueryBuilder
     */
    public function table(string $table, Connection $connection = null): QueryBuilder
    {
        $connectionName = $connection ? $connection->getName() : self::DEFAULT_CONNECTION_NAME;
        return $this->getConnection($connectionName)->table($table);
    }

    /**
     * @param string $name
     * @return Connection
     */
    public function getConnection(string $name = null): Connection
    {
        return $this->manager->connection($name);
    }

    /**
     * @param Connection|null $connection
     * @return SchemaBuilder
     */
    public function schema(Connection $connection = null): SchemaBuilder
    {
        $connectionName = $connection ? $connection->getName() : self::DEFAULT_CONNECTION_NAME;
        return $this->getConnection($connectionName)->getSchemaBuilder();
    }

    /**
     * @param string $model
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
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @param Container $container
     */
    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }

    /**
     * @param string $fetchMode
     * @return DatabaseProviderInterface
     */
    public function setFetchMode(string $fetchMode): DatabaseProviderInterface
    {
        $this->container['config']['database.fetch'] = $fetchMode;
        return $this;
    }

    /**
     * @return DatabaseManager
     */
    public function getDatabaseManager(): DatabaseManager
    {
        return $this->manager;
    }

    /**
     * Addable to a debugger.
     *
     * @param DebuggerProviderInterface $debugger
     * @throws DebugBarException
     */
    public function addToDebugger(DebuggerProviderInterface $debugger): void
    {
        $connection = $this->getConnection();
        $tpdo = new TraceablePDO($connection->getPdo());
        $connection->setPdo($tpdo);
        $debugger->addDataCollector(new PDOCollector($tpdo));
    }
}
