<?php
declare(strict_types=1);

namespace PublicWhip;

use DebugBar\Bridge\MonologCollector;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Parsedown;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use PublicWhip\Providers\DatabaseProvider;
use PublicWhip\Providers\DatabaseProviderInterface;
use PublicWhip\Providers\DebuggerProvider;
use PublicWhip\Providers\DebuggerProviderInterface;
use PublicWhip\Providers\DebuggerTwigExtension;
use PublicWhip\Providers\HydratorProvider;
use PublicWhip\Providers\HydratorProviderInterface;
use PublicWhip\Providers\MailerProvider;
use PublicWhip\Providers\MailerProviderInterface;
use PublicWhip\Services\DivisionService;
use PublicWhip\Services\DivisionServiceInterface;
use PublicWhip\Web\ErrorHandlers\ErrorHandler;
use PublicWhip\Web\ErrorHandlers\NotFoundHandler;
use PublicWhip\Web\ErrorHandlers\PhpErrorHandler;
use Slim\Csrf\Guard;
use Slim\Flash\Messages;
use Slim\HttpCache\CacheProvider;
use Slim\Interfaces\RouterInterface;
use Slim\Views\Twig;
use function DI\autowire;
use function DI\create;
use function DI\get;

/**
 * Class Config.
 *
 * Handles setting up the services/settings which are needed. This primarily populates
 * a PHP-DI container.
 *
 * @package PublicWhip
 */
class Config
{
    /**
     * Settings/services configuration needed for the web environment.
     *
     * @return array
     */
    public function getWebConfig(): array
    {
        $return = [
            'determineRouteBeforeAppMiddleware' => false,
            'settings.displayErrorDetails' => get('settings.debug'),
            // setup our twig extension
            DebuggerTwigExtension::class => autowire(),
            // setup twig
            Twig::class => create()
                ->constructor(
                    __DIR__ . DIRECTORY_SEPARATOR . 'Web' . DIRECTORY_SEPARATOR . 'Templates',
                    [
                        'cache' => get('settings.templates.cache'),
                        'debug' => get('settings.debug')
                    ]
                )
                ->method('addExtension', get(DebuggerTwigExtension::class)),
            // setup router interface to point to Slim's already existing router.
            RouterInterface::class => get('router'),
            // setup request interface to point to Slim's already existing request
            RequestInterface::class => get('request'),
            // setup response interface to point to SLim's already existing response
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
        return $return;
    }

    /**
     * The general configurations needed to run the system (no matter what environment).
     *
     * @return array
     */
    public function getGeneralConfig(): array
    {
        $return = [
            /**
             * Providers
             */
            LoggerInterface::class => function (ContainerInterface $c): LoggerInterface {
                $settings = $c->get('settings.logger');
                $logger = new Logger($settings['name']);
                $logger->pushProcessor(new UidProcessor());
                $logger->pushHandler(new StreamHandler($settings['path'], Logger::DEBUG));
                $debugger = $c->get(DebuggerProviderInterface::class);
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
            HydratorProviderInterface::class => autowire(HydratorProvider::class),
            /**
             * Services.
             */
            DivisionServiceInterface::class => autowire(DivisionService::class)
        ];
        return $return;
    }
}
