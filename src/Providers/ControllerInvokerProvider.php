<?php
declare(strict_types=1);

namespace PublicWhip\Providers;

use Invoker\Exception\InvocationException;
use Invoker\Exception\NotCallableException;
use Invoker\Exception\NotEnoughParametersException;
use Invoker\InvokerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\InvocationStrategyInterface;

/**
 * Class ControllerInvokerProvider.
 *
 * Providers a controller invoker compatible with PHP-DI.
 */
class ControllerInvokerProvider implements InvocationStrategyInterface
{

    /**
     * @var InvokerInterface $invoker The PHP-DI invoker.
     */
    private $invoker;

    /**
     * Constructor.
     *
     * @param InvokerInterface $invoker The invoker.
     */
    public function __construct(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
    }

    /**
     * Invoke a route callable.
     *
     * @param callable $callable The callable to invoke using the strategy.
     * @param ServerRequestInterface $request The request object.
     * @param ResponseInterface $response The response object.
     * @param string[] $routeArguments The route's placeholder arguments
     *
     * @return ResponseInterface|string The response from the callable.
     * @throws InvocationException
     * @throws NotCallableException
     * @throws NotEnoughParametersException
     */
    public function __invoke(
        callable $callable,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $routeArguments
    )
    {
        // Inject the request and response by parameter name
        $parameters = [
            'request' => $request,
            'response' => $response,
        ];

        // Inject the route arguments by name
        $parameters += $routeArguments;

        // Inject the attributes defined on the request
        $parameters += $request->getAttributes();

        return $this->invoker->call($callable, $parameters);
    }
}
