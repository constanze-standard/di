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

namespace ConstanzeStandard\DI;

use ConstanzeStandard\Container\Container;
use ConstanzeStandard\DI\Interfaces\CallableResolverInterface;
use ConstanzeStandard\DI\Interfaces\ConstructResolverInterface;
use ConstanzeStandard\DI\Interfaces\InvokerInterface;
use ConstanzeStandard\DI\Interfaces\ParameterResolverInterface;
use ConstanzeStandard\DI\Interfaces\ResolveableInterface;
use ConstanzeStandard\DI\Resolver\CallableResolver;
use ConstanzeStandard\DI\Resolver\ConstructResolver;
use ConstanzeStandard\DI\Resolver\ParameterResolver;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Reflection;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionObject;

class Manager
{
    /**
     * The PSR container.
     * 
     * @var ContainerInterface
     */
    private $container;

    /**
     * The parameter resolver.
     * 
     * @var ParameterResolverInterface
     */
    private $parameterResolver;

    /**
     * @param ContainerInterface $container
     * @param ParameterResolverInterface|null $parameterResolver
     */
    public function __construct(
        ContainerInterface $container,
        ?ParameterResolverInterface $parameterResolver = null
    )
    {
        $this->container = $container;
        $this->parameterResolver = $parameterResolver ?? new ParameterResolver($container);
    }

    /**
     * Get the callable resolver.
     * 
     * @return ResolveableInterface
     */
    public function getCallableResolver(callable $callable): ResolveableInterface
    {
        return new CallableResolver($callable, $this->parameterResolver);
    }

    /**
     * Get the construct resolver.
     * 
     * @return ResolveableInterface
     */
    public function getConstructResolver(string $class): ResolveableInterface
    {
        return new ConstructResolver($class, $this->parameterResolver);
    }

    /**
     * Calling a function or callable object.
     * 
     * @param callable $callable
     * @param array $parameters
     * 
     * @return mixed
     */
    public function call(callable $callable, array $parameters = [])
    {
        return $this->getCallableResolver($callable)->resolve($parameters);
    }

    /**
     * Get the class instance.
     * 
     * @param string $class
     */
    public function instance(string $class, array $parameters = []): object
    {
        return $this->getConstructResolver($class)->resolve($parameters);
    }

    /**
     * Get the PSR 11 container.
     * 
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
