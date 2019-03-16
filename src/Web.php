<?php
declare(strict_types=1);

namespace PublicWhip;

use DI\ContainerBuilder;
use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use PublicWhip\Exceptions\MissingConfigurationException;
use PublicWhip\Web\Routing;
use Slim\App;
use Slim\Exception\MethodNotAllowedException;
use Slim\Exception\NotFoundException;
use Slim\Http\Response;

/**
 * Class Web.
 *
 * Handles web access to PublicWhip.
 *
 */
class Web
{

    /**
     * @var string
     */
    private $environment;

    /**
     * @var App The Slim Application.
     */
    private $app;

    /**
     * Web constructor.
     *
     * @param string $environment The name of the environment are setting up.
     * @param App|null $app The optional pre-built app.
     *
     * @throws Exception
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
     * Main runner.
     **
     *
     * @return ResponseInterface The response interface.
     *
     * @throws MethodNotAllowedException
     * @throws NotFoundException
     */
    public function run(): ResponseInterface
    {
        $routing = new Routing();
        $routing->getRouting($this->app);
        $routing->setupTrailingSlash($this->app);
        return $this->app->run(false);
    }

    /**
     * Configure the dependency injector container.
     *
     * @TODO Add caching if appropriate on production.
     *
     * @param ContainerBuilder $builder The container we we populating.
     *
     * @return void
     *
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
}
