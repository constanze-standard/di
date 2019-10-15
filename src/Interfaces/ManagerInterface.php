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

namespace ConstanzeStandard\DI\Interfaces;

use Psr\Container\ContainerInterface;

interface ManagerInterface
{
    /**
     * Get the PSR 11 container.
     * 
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;

    /**
     * Get the callable resolver.
     * 
     * @return ResolveableInterface
     */
    public function getCallableResolver(callable $callable): ResolveableInterface;

    /**
     * Get the construct resolver.
     * 
     * @return ResolveableInterface
     */
    public function getConstructResolver(string $class): ResolveableInterface;

    /**
     * Get the annotation resolver.
     * 
     * @return AnnotationResolverInterface
     */
    public function getAnnotationResolver(): AnnotationResolverInterface;

    /**
     * Calling a function or callable object.
     * 
     * @param callable $callable
     * @param array $parameters
     * 
     * @return mixed
     */
    public function call(callable $callable, array $parameters = []);

    /**
     * Get the class instance.
     * 
     * @param string $class
     */
    public function instance(string $class, array $parameters = []): object;

    /**
     * Resolve propertys by annotation.
     * 
     * @param object $instance
     * 
     * @return object
     */
    public function resolvePropertyAnnotation(object $instance);
}
