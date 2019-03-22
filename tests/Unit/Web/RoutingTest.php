<?php
declare(strict_types = 1);

namespace PublicWhip\Tests\Unit\Web;

use function is_array;
use PHPUnit\Framework\TestCase;
use PublicWhip\Web\Controllers\DebugBarController;
use PublicWhip\Web\Controllers\DivisionController;
use PublicWhip\Web\Controllers\DocsController;
use PublicWhip\Web\Controllers\IndexController;
use PublicWhip\Web\Controllers\PingController;
use PublicWhip\Web\Routing;
use Slim\App;
use Slim\Route;
use Slim\Router;

/**
 * Routing test.
 *
 * @coversDefaultClass \PublicWhip\Web\Routing
 * @covers \PublicWhip\Web\Routing::<!public>
 * @SuppressWarnings(PHPMD.ElseExpression)
 */
final class RoutingTest extends TestCase
{
    /**
     * Our default routes.
     *
     * @return array<string,array<string,string>>
     */
    private function getExpectedDefaultRoutes(): array
    {
        return [
            'GET' => [
                '/' => IndexController::class . '::indexAction',
                '/divisions/' => DivisionController::class . '::indexAction',
                '/divisions/{divisionId:[0-9]+}/' => DivisionController::class . '::showDivisionById',
                '/divisions/{house:commons|lords|scotland}/' .
                '{date:18|19|20[0-9][0-9]\-[0-1][0-9]\-[0-3][0-9]}/' .
                '{divisionNumber:[0-9]+}/' => DivisionController::class . '::showDivisionByDateAndNumberAction',
                '/ping/' => PingController::class . '::indexAction',
                '/ping/lastDivisionParsed/' => PingController::class . '::lastDivisionParsedAction',
                '/ping/testmail/' => PingController::class . '::testMailAction',
                '/docs/{file:.*}' => DocsController::class . '::render',
            ]
        ];
    }

    /**
     * Test basic routing.
     * @covers ::setupRouting
     */
    public function testSetupRouting(): void
    {
        $this->checkRoutes($this->getExpectedDefaultRoutes(), []);
    }

    /**
     * Tests the routing with debug enabled.
     * @covers ::setupRouting
     */
    public function testSetupRoutingDebug(): void
    {
        $routes = $this->getExpectedDefaultRoutes();
        $routes['GET']['/debugbar/[{filePath:.*}]'] = DebugBarController::class . '::staticFileAction';
        $this->checkRoutes($routes, ['settings.debug' => true]);
    }

    /**
     * Setups up Slim, adds our routes and then validates them.
     *
     * @param array $routesToMatch List of routes to match.
     * @param array $settings Settings.
     */
    private function checkRoutes(array $routesToMatch, array $settings): void
    {
        $app = new App($settings);
        $routing = new Routing();
        $routing->setupRouting($app);
        /** @var Router $router */
        $router = $app->getContainer()->get('router');
        $simplifiedRoutes = [];
        /** @var Route $route */
        foreach ($router->getRoutes() as $route) {
            foreach ($route->getMethods() as $method) {
                $callable = $route->getCallable();
                if (is_array($callable)) {
                    $callable = implode('::', $callable);
                }
                if (!is_string($callable)) {
                    $callable='['.gettype($callable).']';
                }
                $simplifiedRoutes[$method][$route->getPattern()] = $callable;
            }
        }
        self::checkRoutesValidated($routesToMatch, $simplifiedRoutes);

    }
    /**
     * Performs the validation
     *
     * @param array $routesToMatch List of routes to match.
     * @param array<string,array<string,string>> $simplifiedRoutes The extracted roots.
     */
    private static function checkRoutesValidated(array $routesToMatch, array $simplifiedRoutes) : void
    {
        $unMatchedRoutes = [];
        /**
         * @var string $method Http method
         * @var array<string,string> $routeData The route data.
         */
        foreach ($simplifiedRoutes as $method => $routeData) {
            /**
             * @var string $pattern The pattern.
             * @var string $callable The name/path of the callable.
             */
            foreach ($routeData as $pattern => $callable) {
                if (isset($routesToMatch[$method][$pattern])) {
                    if ($callable === $routesToMatch[$method][$pattern]) {
                        unset($routesToMatch[$method][$pattern]);
                    } else {
                        $unMatchedRoutes[] = $method . ' ' . $pattern . ' => ' . $callable;
                    }
                }
            }
        }
        $output = [];
        foreach ($routesToMatch as $method => $routeData) {
            /**
             * @var string $pattern The pattern.
             * @var string $callable The name/path of the callable.
             */
            foreach ($routeData as $pattern => $callable) {
                $output[] = $method . ' ' . $pattern . ' => ' . $callable;
            }
        }
        self::assertEmpty($output, 'Unable to match routes: ' . implode(PHP_EOL, $output));
        self::assertEmpty($unMatchedRoutes, 'Found unchecked routes: ' . implode(PHP_EOL, $unMatchedRoutes));
    }

}