<?php
declare(strict_types=1);

namespace PublicWhip\Providers;

use Invoker\CallableResolver;
use Invoker\Exception\NotCallableException;
use Slim\Interfaces\CallableResolverInterface;

/**
 * Resolve middleware and route callables using PHP-DI.
 */
final class CallableResolverProvider implements CallableResolverInterface, CallableResolverProviderInterface
{

    /**
     * @var CallableResolver
     */
    private $callableResolver;

    /**
     * Constructor.
     *
     * @param CallableResolver $callableResolver The resolver.
     */
    public function __construct(CallableResolver $callableResolver)
    {
        $this->callableResolver = $callableResolver;
    }

    /**
     * @param mixed $toResolve What to resolve.
     *
     * @return callable The callable.
     * @throws NotCallableException
     */
    public function resolve($toResolve): callable
    {
        return $this->callableResolver->resolve($toResolve);
    }
}
