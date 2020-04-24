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

use ConstanzeStandard\DI\Interfaces\ParameterResolverInterface;
use ConstanzeStandard\DI\Interfaces\ResolveableInterface;
use ReflectionFunction;
use ReflectionMethod;

class CallableResolver implements ResolveableInterface
{
    /**
     * The instance of method, function is null.
     * 
     * @var object|null
     */
    private $instance;

    /**
     * The reflection for callable.
     * 
     * @var ReflectionFunction|ReflectionMethod
     */
    private $reflection;

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
        [$this->instance, $this->reflection] = $this->getReflection($callable);
        $this->parameterResolver = $parameterResolver;
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
        if ($this->instance) {
            return $this->reflection->invokeArgs($this->instance, $args);
        }
        return $this->reflection->invokeArgs($args);
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
        return $this->parameterResolver->resolve($this->reflection, $parameters);
    }

    /**
     * Get reflection by callable object.
     * 
     * @param callable $callable
     * 
     * @return ReflectionFunction|ReflectionMethod
     */
    private function getReflection(callable $callable)
    {
        if (is_string($callable)) {
            return [null , new ReflectionFunction($callable)];
        } elseif (is_array($callable)) {
            return [$callable[0], new ReflectionMethod($callable[0], $callable[1])];
        }
        return [$callable, new ReflectionMethod($callable, '__invoke')];
    }
}
