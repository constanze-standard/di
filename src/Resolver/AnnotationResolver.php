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

use ConstanzeStandard\DI\Annotation\Params;
use ConstanzeStandard\DI\Annotation\Property;
use ConstanzeStandard\DI\Interfaces\AnnotationResolverInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;

class AnnotationResolver implements AnnotationResolverInterface
{
    /**
     * The psr container.
     * 
     * @var ContainerInterface
     */
    private $container;

    /**
     * The annotation reader.
     * 
     * @var Reader
     */
    private $reader;

    /**
     * @param ContainerInterface $container
     * @param Reader|null $reader
     */
    public function __construct(ContainerInterface $container, ?Reader $reader = null)
    {
        $this->container = $container;
        $this->reader = $reader ?? new AnnotationReader();

        AnnotationRegistry::registerFile(__DIR__ . '/../Annotation/Property.php');
        AnnotationRegistry::registerFile(__DIR__ . '/../Annotation/Params.php');
    }

    /**
     * Resolve the property of object.
     * 
     * @param object $instance
     * 
     * @return object
     */
    public function resolveProperty(object $instance): object
    {
        $reflectionClass = new ReflectionClass($instance);

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $property = $this->reader->getPropertyAnnotation(
                $reflectionProperty,
                Property::class
            );

            if ($property instanceof Property) {
                $entry = $this->container->get($property->getName());
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($instance, $entry);
            }
        }

        return $instance;
    }

    /**
     * Resolve the method parameters of reflection.
     * 
     * @param ReflectionMethod $reflectionMethod
     * 
     * @return array
     */
    public function resolveMethodParameters(ReflectionMethod $reflectionMethod): array
    {
        $parameters = [];
        if ($params = $this->reader->getMethodAnnotation($reflectionMethod, Params::class)) {
            foreach ($params->getParams() as $key => $name) {
                $parameters[$key] = $this->container->get($name);
            }
        }

        return $parameters;
    }

    /**
     * Resolve the method.
     * 
     * @param object $instance
     * @param string $method
     * @param array $parameters
     * 
     * @return mixed
     */
    public function resolveMethod(object $instance, string $method, array $parameters = [])
    {
        $reflectionMethod = new ReflectionMethod($instance, $method);
        $parameters = array_merge($this->resolveMethodParameters($reflectionMethod), $parameters);
        return $reflectionMethod->invokeArgs($instance, $parameters);
    }
}
