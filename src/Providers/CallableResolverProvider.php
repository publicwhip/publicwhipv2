<?php
declare(strict_types = 1);

namespace PublicWhip\Providers;

use Invoker\CallableResolver;
use Invoker\Exception\NotCallableException;

/**
 * Resolve middleware and route callables using PHP-DI.
 *
 * Coding standard disabled for Slim compatibility.
 * phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
 */
final class CallableResolverProvider implements CallableResolverProviderInterface
{
    /**
     * The resolver.
     *
     * @var CallableResolver
     */
    private $callableResolver;

    /**
     * @param CallableResolver $callableResolver The resolver.
     */
    public function __construct(CallableResolver $callableResolver)
    {
        $this->callableResolver = $callableResolver;
    }

    /**
     * Resolve the callable
     *
     * @param mixed $toResolve What to resolve.
     * @return callable The callable.
     * @throws NotCallableException
     */
    public function resolve($toResolve): callable
    {
        return $this->callableResolver->resolve($toResolve);
    }
}
