<?php

/**
 * Copyright 2019 Constanze Standard <omytty.alex@gmail.com>
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace ConstanzeStandard\DI\Resolver;

use Closure;
use ConstanzeStandard\DI\Interfaces\ParameterResolverInterface;
use ConstanzeStandard\DI\Interfaces\ResolveableInterface;
use ReflectionMethod;

class CallableResolver implements ResolveableInterface
{
    /**
     * closure for object.
     * 
     * @var Closure
     */
    private $closure;

    /**
     * The parameter resolver.
     * 
     * @var ParameterResolverInterface
     */
    private $parameterResolver;

    /**
     * @param callable $callable
     * @param ParameterResolverInterface $parameterResolver
     */
    public function __construct(callable $callable, ParameterResolverInterface $parameterResolver)
    {
        $this->closure = Closure::fromCallable($callable);
        $this->parameterResolver = $parameterResolver;
    }

    /**
     * Get closure from callable.
     * 
     * @return Closure
     */
    public function getClosure(): Closure
    {
        return $this->closure;
    }

    /**
     * Resolve the callable object.
     * 
     * @param array $parameters
     * 
     * @return mixed
     */
    public function resolve(array $parameters = [])
    {
        $args = $this->resolveParameters($parameters);
        return $this->getClosure()->__invoke(...$args);
    }

    /**
     * Resolve parameters.
     * 
     * @param array $parameters
     * 
     * @return array
     */
    public function resolveParameters(array $parameters = []): array
    {
        $reflection = new ReflectionMethod($this->getClosure(), '__invoke');
        return $this->parameterResolver->resolve($reflection, $parameters);
    }
}
