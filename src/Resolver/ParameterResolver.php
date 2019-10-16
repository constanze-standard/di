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

use ConstanzeStandard\Container\Container;
use ConstanzeStandard\DI\Annotation\Params;
use ConstanzeStandard\DI\Interfaces\AnnotationResolverInterface;
use ConstanzeStandard\DI\Interfaces\ParameterResolverInterface;
use Doctrine\Common\Annotations\Reader;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionType;
use TypeError;

class ParameterResolver implements ParameterResolverInterface
{
    /**
     * The PSR container.
     * 
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var AnnotationResolverInterface
     */
    private $annotationResolver;

    /**
     * @param ContainerInterface $container
     * @param Reader $reader
     */
    public function __construct(
        ContainerInterface $container,
        AnnotationResolverInterface $annotationResolver
    )
    {
        $this->container = $container;
        $this->annotationResolver = $annotationResolver;
    }

    /**
     * Resolve the handler and get parameters.
     * 
     * @param ReflectionFunctionAbstract $reflection
     * @param array $parameters
     * 
     * @return array
     */
    public function resolve(ReflectionFunctionAbstract $reflection, array $parameters = []): array
    {
        if ($reflection instanceof ReflectionMethod === true) {
            $parameters = array_merge(
                $this->annotationResolver->resolveMethodParameters($reflection),
                $parameters
            );
        }

        $args = [];
        $numArgs = [];

        foreach ($reflection->getParameters() as $index => $parameter) {
            $paramName = $parameter->getName();
            switch (true) {
                case array_key_exists($paramName, $parameters):
                    $args[$index] = $parameters[$paramName];
                    break;
                case $parameter->isDefaultValueAvailable():
                    $args[$index] = $parameter->getDefaultValue();
                    break;
                case $parameter->hasType():
                    $reflectionType = $parameter->getType();
                    $args[$index] = $this->getInstanceByName($reflectionType);
                    break;
                default:
                    $numArgs[] = $index;
                    break;
            }
        }

        $numParams = array_values(
            array_filter($parameters, 'is_numeric', ARRAY_FILTER_USE_KEY)
        );
        if ($numArgs && (count($numArgs) <= count($numParams))) {
            foreach ($numArgs as $key => $numArg) {
                $args[$numArg] = $numParams[$key];
            }
            ksort($args);
            return $args;
        }

        throw new InvalidArgumentException('args number error.');
    }

    /**
     * Type to value process.
     * 
     * @param ReflectionType $reflectionType
     * 
     * @throws \Exception
     * 
     * @return mixed
     */
    private function getInstanceByName(ReflectionType $reflectionType)
    {
        $name = $reflectionType->getName();
        if ($this->container->has($name)) {
            return $this->container->get($name);
        }

        throw new \Exception('There is no processor for the parameter type '. $name);
    }
}