<?php
declare(strict_types = 1);

namespace PublicWhip;

use DI\ContainerBuilder;
use Psr\Http\Message\ResponseInterface;
use PublicWhip\Exceptions\MissingConfigurationException;
use PublicWhip\Web\Routing;
use Slim\App;
use Slim\Exception\MethodNotAllowedException;
use Slim\Exception\NotFoundException;
use Throwable;

/**
 *  Handles web access to PublicWhip.
 */
class Web
{
    /**
     * Environment details.
     *
     * @var string
     */
    private $environment;

    /**
     * The Slim Application.
     *
     * @var App
     */
    private $app;

    /**
     * Sets up the system.
     *
     * @param string $environment The name of the environment are setting up.
     * @param App|null $app The optional pre-built app.
     * @throws Throwable
     */
    public function __construct(string $environment, ?App $app = null)
    {
        $this->environment = $environment;

        if (null === $app) {
            $containerBuilder = new ContainerBuilder();
            $this->configureContainer($containerBuilder);
            $container = $containerBuilder->build();
            $app = new App($container);
        }

        $this->app = $app;
    }

    /**
     * Configure the dependency injector container.
     *
     * @TODO Add caching if appropriate on production.
     * @param ContainerBuilder $builder The container we we populating.
     * @throws MissingConfigurationException
     */
    protected function configureContainer(ContainerBuilder $builder): void
    {
        $config = new Config();
        $builder->addDefinitions($config->getGeneralConfig());
        $builder->addDefinitions($config->getWebConfig());
        /**
         * Now load our specifics over the top of the prebuilt ones.
         */
        $settingsFile = __DIR__ .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . 'config' .
            DIRECTORY_SEPARATOR . $this->environment . '.php';

        if (!file_exists($settingsFile) && is_readable($settingsFile)) {
            throw new MissingConfigurationException(
                sprintf(
                    'Could not find environment file: %s',
                    $settingsFile
                )
            );
        }

        $builder->addDefinitions($settingsFile);
    }

    /**
     * Main runner.
     **
     *
     * @return ResponseInterface The response interface.
     * @throws MethodNotAllowedException
     * @throws NotFoundException
     */
    public function run(): ResponseInterface
    {
        $routing = new Routing();
        $routing->setupRouting($this->app);
        $routing->setupTrailingSlash($this->app);

        return $this->app->run();
    }
}
