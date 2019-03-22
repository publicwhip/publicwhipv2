<?php
declare(strict_types = 1);

namespace PublicWhip;

use DebugBar\Bridge\MonologCollector;
use DI\Container;
use DI\Definition\Helper\CreateDefinitionHelper;
use Invoker\Invoker;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\ResolverChain;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;
use Parsedown;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use PublicWhip\Factories\DateTimeFactory;
use PublicWhip\Factories\DateTimeFactoryInterface;
use PublicWhip\Factories\EntityFactory;
use PublicWhip\Factories\EntityFactoryInterface;
use PublicWhip\Providers\CallableResolverProvider;
use PublicWhip\Providers\CheckTypeProvider;
use PublicWhip\Providers\CheckTypeProviderInterface;
use PublicWhip\Providers\ControllerInvokerProvider;
use PublicWhip\Providers\DatabaseProvider;
use PublicWhip\Providers\DatabaseProviderInterface;
use PublicWhip\Providers\DebuggerProvider;
use PublicWhip\Providers\DebuggerProviderInterface;
use PublicWhip\Providers\DebuggerTwigExtension;
use PublicWhip\Providers\MailerProvider;
use PublicWhip\Providers\MailerProviderInterface;
use PublicWhip\Providers\MailerTransportProvider;
use PublicWhip\Providers\MailerTransportProviderInterface;
use PublicWhip\Providers\TemplateProvider;
use PublicWhip\Providers\TemplateProviderInterface;
use PublicWhip\Providers\WikiParserProvider;
use PublicWhip\Providers\WikiParserProviderInterface;
use PublicWhip\Services\DivisionVoteSummaryService;
use PublicWhip\Services\DivisionVoteSummaryServiceInterface;
use PublicWhip\Services\HansardService;
use PublicWhip\Services\HansardServiceInterface;
use PublicWhip\Services\MotionService;
use PublicWhip\Services\MotionServiceInterface;
use PublicWhip\Web\ErrorHandlers\ErrorHandler;
use PublicWhip\Web\ErrorHandlers\NotFoundHandler;
use PublicWhip\Web\ErrorHandlers\PhpErrorHandler;
use RuntimeException;
use Slim\Csrf\Guard;
use Slim\Flash\Messages;
use Slim\Handlers\NotAllowed;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\HttpCache\CacheProvider;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\RouterInterface;
use Slim\Router;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use function DI\autowire;
use function DI\create;
use function DI\get;
use function is_array;
use function is_string;

/**
 * Handles setting up the services/settings which are needed. This primarily populates
 * a PHP-DI container.
 * Since we configure practically everything here, we have a high coupling between objects. We'll
 * tell PHPMD this is okay.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Config
{
    /**
     * Settings/services configuration needed for the web environment.
     *
     * @return array<string, array|bool|(callable)|object|int|string>
     */
    public function getWebConfig(): array
    {
        $return = [
            'settings.isWeb' => true,
            'determineRouteBeforeAppMiddleware' => false,
            'settings.displayErrorDetails' => get('settings.debug'),
            // setup our twig extension
            DebuggerTwigExtension::class => autowire(),
            // setup twig
            TwigExtension::class => static function (ContainerInterface $container): TwigExtension {
                $router = $container->get('router');
                $uri = $container->get('request')->getUri();

                return new TwigExtension($router, $uri);
            },
            TemplateProviderInterface::class => autowire(TemplateProvider::class),
            Twig::class => create()
                ->constructor(
                    __DIR__ . DIRECTORY_SEPARATOR . 'Web' . DIRECTORY_SEPARATOR . 'Templates',
                    [
                        'cache' => get('settings.templates.cache'),
                        'debug' => get('settings.debug')
                    ]
                )
                ->method('addExtension', get(DebuggerTwigExtension::class))
                ->method('addExtension', get(TwigExtension::class)),
            // setup router interface to point to Slim's already existing router.
            RouterInterface::class => get('router'),
            // setup request interface to point to Slim's already existing request
            RequestInterface::class => get('request'),
            ServerRequestInterface::class => get('request'),
            // setup response interface to point to Slim's already existing response
            ResponseInterface::class => get('response'),
            // setup a CSRF guard
            Guard::class => create(Guard::class),
            // setup flash messaging
            Messages::class => create(Messages::class),
            // setup a cache middleware for Slim
            CacheProvider::class => create(),
            // setup error handlers
            'errorHandler' => create(ErrorHandler::class)
                ->constructor(get(DebuggerProviderInterface::class), get('settings.debug')),
            'notFoundHandler' => autowire(NotFoundHandler::class),
            'phpErrorHandler' => create(PhpErrorHandler::class)
                ->constructor(get(DebuggerProviderInterface::class), get('settings.debug')),
            // setup parsedown for markdown formatting.
            Parsedown::class => create()
        ];

        return array_merge($this->generalSlimSetup(), $return);
    }

    /**
     * Get the general Slim settings.
     * /**
     * Disable this PHPMD warning for simplicity when building the environment using Request::.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @return array<string, array|bool|(callable)|object|int|string>
     */
    private function generalSlimSetup(): array
    {
        return [

            // Settings that can be customized by users
            'settings.httpVersion' => '1.1',
            'settings.responseChunkSize' => 4096,
            'settings.outputBuffering' => 'append',
            'settings.determineRouteBeforeAppMiddleware' => false,
            'settings.displayErrorDetails' => false,
            'settings.addContentLengthHeader' => true,
            'settings.routerCacheFile' => false,

            'settings' => [
                'httpVersion' => get('settings.httpVersion'),
                'responseChunkSize' => get('settings.responseChunkSize'),
                'outputBuffering' => get('settings.outputBuffering'),
                'determineRouteBeforeAppMiddleware' => get('settings.determineRouteBeforeAppMiddleware'),
                'displayErrorDetails' => get('settings.displayErrorDetails'),
                'addContentLengthHeader' => get('settings.addContentLengthHeader'),
                'routerCacheFile' => get('settings.routerCacheFile')
            ],

            // Default Slim services
            'router' => create(Router::class)
                ->method('setContainer', get(Container::class))
                ->method('setCacheFile', get('settings.routerCacheFile')),

            Router::class => get('router'),

            'notAllowedHandler' => create(NotAllowed::class),

            'environment' => static function () {
                return new Environment($_SERVER);
            },

            'request' => static function (ContainerInterface $container) {
                return Request::createFromEnvironment($container->get('environment'));
            },

            'response' => static function (ContainerInterface $container) {
                $headers = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);

                return (new Response(200, $headers))->withProtocolVersion($container->get('settings')['httpVersion']);
            },

            'foundHandler' => create(ControllerInvokerProvider::class)
                ->constructor(get('foundHandler.invoker')),
            'foundHandler.invoker' => static function (ContainerInterface $container) {
                $resolvers = [
                    // Inject parameters by name first
                    new AssociativeArrayResolver(),
                    // Then inject services by type-hints for those that weren't resolved
                    new TypeHintContainerResolver($container),
                    // Then fall back on parameters default values for optional route parameters
                    new DefaultValueResolver()
                ];

                return new Invoker(new ResolverChain($resolvers), $container);
            },

            CallableResolverInterface::class => autowire(CallableResolverProvider::class),

            'callableResolver' => get(CallableResolverInterface::class),
        ];
    }

    /**
     * Get the general configuration loggers
     *
     * @return array<string, (Closure(Psr\Container\ContainerInterface):Monolog\Logger)|DI\Definition\Reference>
     */
    private function getGeneralLoggers(): array
    {
        return [
            'logger.default' => static function (ContainerInterface $container): Logger {
                $settings = $container->get('settings.logger');
                $logger = new Logger('default');
                $logger->pushProcessor(new UidProcessor());
                $logger->pushProcessor(new MemoryUsageProcessor());
                $logger->pushHandler(new StreamHandler($settings['path'], Logger::DEBUG));
                $logger->pushProcessor(new IntrospectionProcessor());

                if ($container->get('settings.isWeb') && $container->has('environment')) {
                    $logger->pushProcessor(new WebProcessor($container->get('environment')));
                }

                // add it to the debugger
                $debugger = $container->get(DebuggerProviderInterface::class);
                $debugger->addDataCollector(new MonologCollector($logger));

                return $logger;
            },
            'logger.providers' => static function (ContainerInterface $container): Logger {
                return $container->get('logger.default')->withName('providers');
            },
            'logger.services' => static function (ContainerInterface $container): Logger {
                return $container->get('logger.default')->withName('services');
            },
            'logger.factories' => static function (ContainerInterface $container): Logger {
                return $container->get('logger.default')->withName('factories');
            },
            LoggerInterface::class => get('logger.default')
        ];
    }

    /**
     * Get the general configuration providers.
     *
     * @return array<string,callable|CreateDefinitionHelper> The providers.
     */
    private function getGeneralProviders(): array
    {
        return [
            DatabaseProviderInterface::class => create(DatabaseProvider::class)
                ->constructor(get('settings.db'))
                ->method('addToDebugger', get(DebuggerProviderInterface::class)),

            MailerProviderInterface::class => static function (ContainerInterface $container): MailerProviderInterface {
                /** @var array<string,string>|null $settings */
                $settings = $container->get('settings.mail');
                if (!is_array($settings)) {
                    throw new RuntimeException('Missing settings.mail');
                }
                if (!isset($settings['fromname']) || !is_string($settings['fromname'])) {
                    throw new RuntimeException('Missing settings.mail.fromname');
                }
                if (!isset($settings['fromaddress']) || !is_string($settings['fromaddress'])) {
                    throw new RuntimeException('Missing settings.mail.fromaddress');
                }
                $provider = new MailerProvider(
                    $container->get(MailerTransportProviderInterface::class),
                    $settings['fromname'],
                    $settings['fromaddress']
                );
                $provider->addToDebugger($container->get(DebuggerProviderInterface::class));

                return $provider;
            },
            MailerTransportProviderInterface::class => create(MailerTransportProvider::class)
                ->constructor(get('settings.mail')),
            DebuggerProviderInterface::class => create(DebuggerProvider::class)
                ->constructor(get('settings.debug')),
            CheckTypeProviderInterface::class => autowire(CheckTypeProvider::class)
                ->constructorParameter('logger', get('logger.providers')),
            WikiParserProviderInterface::class => autowire(WikiParserProvider::class)
                ->constructorParameter('logger', get('logger.providers'))

        ];
    }

    /**
     * The general configurations needed to run the system (no matter what environment).
     *
     * @return array<string, array|bool|callable|object|int|string>
     */
    public function getGeneralConfig(): array
    {
        /**
         * Default settings.
         */
        $defaultSettings = [
            'settings.isWeb' => false
        ];

        /**
         * Factories
         */
        $factories = [
            EntityFactoryInterface::class => autowire(EntityFactory::class)
                ->constructorParameter('logger', get('logger.factories')),
            DateTimeFactoryInterface::class => autowire(DateTimeFactory::class)
                ->constructorParameter('logger', get('logger.factories'))
        ];
        /**
         * Services.
         */
        $services = [
            HansardServiceInterface::class => autowire(HansardService::class)
                ->constructorParameter('logger', get('logger.services')),
            DivisionVoteSummaryServiceInterface::class => autowire(DivisionVoteSummaryService::class)
                ->constructorParameter('logger', get('logger.services')),
            MotionServiceInterface::class => autowire(MotionService::class)
                ->constructorParameter('logger', get('logger.services'))
        ];

        return array_merge(
            $defaultSettings,
            $this->getGeneralLoggers(),
            $this->getGeneralProviders(),
            $factories,
            $services
        );
    }
}
