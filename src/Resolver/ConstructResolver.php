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
use ConstanzeStandard\DI\Interfaces\ResolverInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;

class ConstructResolver implements ResolverInterface
{
    /**
     * The parameter resolver.
     * 
     * @var ParameterResolverInterface
     */
    private $parameterResolver;

    public function __construct(ParameterResolverInterface $parameterResolver, ContainerInterface $container)
    {
        $this->parameterResolver = $parameterResolver;
        $this->container = $container;
    }

    /**
     * Resolve the handler.
     * 
     * @param string|object $source
     * @param array $parameters
     * 
     * @return object
     */
    public function resolve($source, array $parameters = [])
    {
        $args = [];
        $reflectionClass = new ReflectionClass($source);
        $constructor = $reflectionClass->getConstructor();
        if (! is_null($constructor)) {
            $args = $this->parameterResolver->resolve($constructor, $parameters);
        }
        return $reflectionClass->newInstanceArgs($args);
    }
}
