<?php
declare(strict_types=1);

namespace PublicWhip;

use DebugBar\Bridge\MonologCollector;
use DI\Container;
use Invoker\Invoker;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\ResolverChain;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
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
use PublicWhip\Providers\ControllerInvokerProvider;
use PublicWhip\Providers\DatabaseProvider;
use PublicWhip\Providers\DatabaseProviderInterface;
use PublicWhip\Providers\DebuggerProvider;
use PublicWhip\Providers\DebuggerProviderInterface;
use PublicWhip\Providers\DebuggerTwigExtension;
use PublicWhip\Providers\MailerProvider;
use PublicWhip\Providers\MailerProviderInterface;
use PublicWhip\Providers\TemplateProvider;
use PublicWhip\Providers\TemplateProviderInterface;
use PublicWhip\Providers\WikiParserProvider;
use PublicWhip\Providers\WikiParserProviderInterface;
use PublicWhip\Services\DivisionService;
use PublicWhip\Services\DivisionServiceInterface;
use PublicWhip\Web\ErrorHandlers\ErrorHandler;
use PublicWhip\Web\ErrorHandlers\NotFoundHandler;
use PublicWhip\Web\ErrorHandlers\PhpErrorHandler;
use Slim\Csrf\Guard;
use Slim\Flash\Messages;
use Slim\Handlers\NotAllowed;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\HttpCache\CacheProvider;
use Slim\Interfaces\RouterInterface;
use Slim\Router;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use function DI\autowire;
use function DI\create;
use function DI\get;

/**
 * Class Config.
 *
 * Handles setting up the services/settings which are needed. This primarily populates
 * a PHP-DI container.
 *
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
     * @return array<string, mixed>
     */
    public function getWebConfig(): array
    {
        $return = [
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
    /**
     * Disable this PHPMD warning for simplicity when building the environment using Request::.
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @return array<string, mixed>):
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
                'routerCacheFile' => get('settings.routerCacheFile'),
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
                    new DefaultValueResolver(),
                ];
                return new Invoker(new ResolverChain($resolvers), $container);
            },

            'callableResolver' => autowire(CallableResolverProvider::class),
        ];
    }

    /**
     * The general configurations needed to run the system (no matter what environment).
     *
     * @return array<string, mixed>
     */
    public function getGeneralConfig(): array
    {
        /**
         * Providers
         */
        $providers = [
            LoggerInterface::class => static function (ContainerInterface $container): LoggerInterface {
                $settings = $container->get('settings.logger');
                $logger = new Logger($settings['name']);
                $logger->pushProcessor(new UidProcessor());
                $logger->pushHandler(new StreamHandler($settings['path'], Logger::DEBUG));
                $debugger = $container->get(DebuggerProviderInterface::class);
                $debugger->addDataCollector(new MonologCollector($logger));
                return $logger;
            },

            DatabaseProviderInterface::class => create(DatabaseProvider::class)
                ->constructor(get('settings.db'))
                ->method('addToDebugger', get(DebuggerProviderInterface::class)),
            MailerProviderInterface::class => create(MailerProvider::class)
                ->constructor(get('settings.mail'))
                ->method('addToDebugger', get(DebuggerProviderInterface::class)),
            DebuggerProviderInterface::class => create(DebuggerProvider::class)
                ->constructor(get('settings.debug')),

            WikiParserProviderInterface::class => autowire(WikiParserProvider::class),


        ];
        /**
         * Factories
         */
        $factories = [
            EntityFactoryInterface::class => autowire(EntityFactory::class),
            DateTimeFactoryInterface::class => autowire(DateTimeFactory::class)
        ];
        /**
         * Services.
         */
        $services = [
            DivisionServiceInterface::class => autowire(DivisionService::class)
        ];
        return array_merge($providers, $factories, $services);
    }
}
