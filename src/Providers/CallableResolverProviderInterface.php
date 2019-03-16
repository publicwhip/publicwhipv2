<?php
declare(strict_types=1);

namespace PublicWhip\Providers;

use Invoker\CallableResolver;
use Invoker\Exception\NotCallableException;

/**
 * Resolve middleware and route callables using PHP-DI.
 */
interface CallableResolverProviderInterface
{

    /**
     * Constructor.
     *
     * @param CallableResolver $callableResolver The resolver.
     */
    public function __construct(CallableResolver $callableResolver);

    /**
     * @param mixed $toResolve What to resolve.
     *
     * @return callable The callable.
     * @throws NotCallableException
     */
    public function resolve($toResolve): callable;
}
