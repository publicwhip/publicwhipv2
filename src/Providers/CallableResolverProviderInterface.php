<?php
declare(strict_types = 1);

namespace PublicWhip\Providers;

use Invoker\CallableResolver;
use Invoker\Exception\NotCallableException;
use Slim\Interfaces\CallableResolverInterface;

/**
 * Resolve middleware and route callables using PHP-DI.
 *
 * Coding standard disabled for Slim compatibility.
 * phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
 */
interface CallableResolverProviderInterface extends CallableResolverInterface
{
    /**
     * @param CallableResolver $callableResolver The resolver.
     */
    public function __construct(CallableResolver $callableResolver);

    /**
     * Resolve the callable.
     *
     * @param mixed $toResolve What to resolve.
     * @return callable The callable.
     * @throws NotCallableException
     */
    public function resolve($toResolve): callable;
}
