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

use ConstanzeStandard\DI\Interfaces\ConstructResolverInterface;
use ConstanzeStandard\DI\Interfaces\ParameterResolverInterface;
use ConstanzeStandard\DI\Interfaces\ResolveableInterface;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use ReflectionClass;

class ConstructResolver implements ResolveableInterface
{
    /**
     * The class name or instance.
     * 
     * @var string
     */
    private $class;

    /**
     * The parameter resolver.
     * 
     * @var ParameterResolverInterface
     */
    private $parameterResolver;

    /**
     * @param string $class
     * @param ParameterResolverInterface $parameterResolver
     */
    public function __construct(string $class, ParameterResolverInterface $parameterResolver)
    {
        $this->class = $class;
        $this->parameterResolver = $parameterResolver;
    }

    /**
     * Resolve the handler and get the instant.
     * 
     * @param string|object $class
     * @param array $parameters
     * 
     * @return object
     */
    public function resolve(array $parameters = [])
    {
        $args = $this->resolveParameters($parameters);
        $class = $this->class;
        return new $class(...$args);
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
        $args = [];
        $reflectionClass = new ReflectionClass($this->class);
        $constructor = $reflectionClass->getConstructor();
        if (! is_null($constructor)) {
            $args = $this->parameterResolver->resolve($constructor, $parameters);
        }
        return $args;
    }
}
