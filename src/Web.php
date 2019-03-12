<?php
declare(strict_types=1);

namespace PublicWhip;

use DI\Bridge\Slim\App;
use DI\ContainerBuilder;
use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use PublicWhip\Providers\DebuggerProviderInterface;
use PublicWhip\Web\Controllers\DebugBarController;
use PublicWhip\Web\Controllers\DivisionController;
use PublicWhip\Web\Controllers\DocsController;
use PublicWhip\Web\Controllers\IndexController;
use PublicWhip\Web\Controllers\PingController;
use RuntimeException;
use Slim\Exception\MethodNotAllowedException;
use Slim\Exception\NotFoundException;
use Slim\Http\Response;

/**
 * Class Web.
 *
 * Handles web access to PublicWhip.
 * @package PublicWhip
 */
class Web extends App
{

    /**
     * @var string
     */
    private $environment;

    /**
     * Web constructor.
     * @param string $environment
     */
    public function __construct(string $environment)
    {
        $this->environment = $environment;
        parent::__construct();
    }

    /**
     * Main runner.
     *
     * @param bool $silent
     * @return ResponseInterface|void
     * @throws MethodNotAllowedException
     * @throws NotFoundException
     */
    public function run($silent = false)
    {
        $this->setupRouting();
        $this->setupTrailingSlash();
        parent::run($silent);
    }

    /**
     * Setup routing.
     */
    public function setupRouting(): void
    {
        // uptime monitors
        $this->group(
            '/ping',
            function (App $app) {
                $app->get('/', [PingController::class, 'indexAction']);
                $app->get('/lastDivisionParsed/', [PingController::class, 'lastDivisionParsedAction']);
            }
        );

        // index page
        $this->get('/', [IndexController::class, 'indexAction']);
        // divisions
        $this->group(
            '/divisions',
            function (App $app) {
                $app->get('/', [DivisionController::class, 'indexAction']);
                $app->get(
                    '/' .
                    '{house:commons|lords|scotland}/' .
                    '{date:18|19|20[0-9][0-9]\-[0-1][0-9]\-[0-3][0-9]}/' .
                    '{divisionId:[0-9]+}/',
                    [DivisionController::class, 'showDivisionByDateAndNumberAction']
                );
            }
        );
        $this->get('/docs/{file:.*}', [DocsController::class, 'render']);
        /**
         * Register the debugbar only if we are in development/debug mode,
         */
        if ($this->getContainer()->get('settings.debug')) {
            $this->get('/debugbar/[{filePath:.*}]', [DebugBarController::class, 'staticFileAction']);
        }
    }

    /**
     * Handle optional trailing slashes on GET requests.
     */
    private function setupTrailingSlash(): void
    {
        $container = $this->getContainer();
        $this->add(
            function (RequestInterface $request, Response $response, callable $next) use ($container) {
                $uri = $request->getUri();
                $path = $uri->getPath();
                if (strlen($path) > 1) {
                    if ('/' !== substr($path, -1) && !pathinfo($path, PATHINFO_EXTENSION)) {
                        $path .= '/';
                    }
                } elseif ('' === $path) {
                    $path = '/';
                }
                $new = $uri->withPath($path);
                if ($uri->getPath() !== $path) {
                    $logger = $container->get(LoggerInterface::class);
                    $logger->debug('Redirecting from ' . $uri . ' to ' . $new);
                    if ('GET' === $request->getMethod()) {
                        return $response->withRedirect($new, 301);
                    }
                }
                return $next($request->withUri($new), $response);
            }
        );
    }

    /**
     * Call relevant handler from the Container if needed. If it doesn't exist,
     * then just re-throw.
     *
     * @param Exception $e
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     * @throws Exception if a handler is needed and not found
     */
    protected function handleException(Exception $e, ServerRequestInterface $request, ResponseInterface $response)
    {
        if ($this->getContainer()->has(DebuggerProviderInterface::class)) {
            $this->getContainer()->get(DebuggerProviderInterface::class)->addException($e);
        }
        return parent::handleException($e, $request, $response);
    }

    /**
     * Configure the dependency injector container.
     *
     * @TODO Add caching if appropriate on production.
     *
     * @param ContainerBuilder $builder
     */
    protected function configureContainer(ContainerBuilder $builder): void
    {
        $config = new Config();
        $builder->addDefinitions($config->getGeneralConfig());
        $builder->addDefinitions($config->getWebConfig());
        /**
         * Now load our specifics over the top of the prebuilt ones.
         */
        $environmentSettingsFile = __DIR__ .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . 'config' .
            DIRECTORY_SEPARATOR . $this->environment . '.php';
        if (file_exists($environmentSettingsFile) && is_readable($environmentSettingsFile)) {
            $builder->addDefinitions($environmentSettingsFile);
        } else {
            throw new RuntimeException('Could not find environment file: ' . $environmentSettingsFile);
        }
    }
}
