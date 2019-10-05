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
use Psr\Container\ContainerInterface;
use ReflectionFunctionAbstract;
use ReflectionType;

class ParameterResolver implements ParameterResolverInterface
{
    /**
     * The PSR container.
     * 
     * @var ContaienrInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Resolve the handler and get parameters.
     * 
     * @param ReflectionFunctionAbstract $reflection
     * @param array $providedParameters
     * @param array $resolvedParameters
     * 
     * @return array
     */
    public function resolve(ReflectionFunctionAbstract $reflection, array $providedParameters = [])
    {
        $reflectionParameters = $reflection->getParameters();
        $args = [];
        
        $numArgs = [];
        foreach ($reflectionParameters as $index => $parameter) {
            $paramName = $parameter->getName();
            switch (true) {
                case in_array($paramName, array_keys($providedParameters)):
                    $args[$index] = $providedParameters[$paramName];
                    break;
                case $parameter->isDefaultValueAvailable():
                    $args[$index] = $parameter->getDefaultValue();
                    break;
                case $parameter->hasType():
                    $typeName = $parameter->getType();
                    $args[$index] = $this->getInstanceByName($typeName);
                    break;
                default:
                    $numArgs[] = $index;
                    break;
            }
        }

        $this->resolveMissMatchArges($numArgs, $args);

        // TODO: invoke by args.
    }

    private function resolveMissMatchArges($numArgs, &$args)
    {

    }

    /**
     * Build arguments from reflectionParameters.
     * assignment -> defaultValue -> type-hint
     * 
     * @param ReflectionParameter[] $reflectionParameters
     * @param array $params
     * 
     * @return array
     */
    private function reflectionParametersToArgs(array $reflectionParameters, array $params)
    {
        $numArgs = [];
        foreach ($reflectionParameters as $index => $parameter) {
            $paramName = $parameter->getName();
            if (is_numeric($index)) {

            } elseif (in_array($paramName, array_keys($params))) {
                $args[$index] = $params[$paramName];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $args[$index] = $parameter->getDefaultValue();
            } elseif ($parameter->hasType()) {
                $args[$index] = $this->getInstanceByName($parameter->getType());
            } else {
                throw new \Exception('The parameter '. $paramName. 'has no specified type and and no assignment.');
            }
        }
        return $args;
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