<?php

use ConstanzeStandard\Container\Container;
use ConstanzeStandard\DI\Interfaces\AnnotationResolverInterface;
use ConstanzeStandard\DI\Manager;
use ConstanzeStandard\DI\Resolver\ParameterResolver;
use Psr\Container\ContainerInterface;

require_once __DIR__ . '/AbstractTest.php';

class T2
{
}

class T3
{
    public function t33(T2 $t2) {}
}

class ParameterResolverTest extends AbstractTest
{
    public function testResolveWithNumberIndexWithOtherParameters()
    {
        /** @var ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);
        /** @var AnnotationResolverInterface $annotationResolver */
        $annotationResolver = $this->createMock(AnnotationResolverInterface::class);
        $parameterResolver = new ParameterResolver($container, $annotationResolver);

        $reflectionParameter1 = $this->createMock(ReflectionParameter::class);
        $reflectionParameter1->expects($this->once())->method('getName')->willReturn('a');
        $reflectionParameter2 = $this->createMock(ReflectionParameter::class);
        $reflectionParameter2->expects($this->once())->method('getName')->willReturn('b');
        $reflectionParameter2->expects($this->once())->method('isDefaultValueAvailable')->willReturn(false);
        $reflectionParameter2->expects($this->once())->method('hasType')->willReturn(false);

        /** @var ReflectionFunctionAbstract $reflection */
        $reflection = $this->createMock(ReflectionFunctionAbstract::class);
        $reflection->expects($this->once())->method('getParameters')->willReturn([
            $reflectionParameter1,
            $reflectionParameter2
        ]);
        $result = $parameterResolver->resolve($reflection, ['a' => 1, 2, 3]);
        $this->assertEquals([1, 2], $result);
    }

    public function testResolveWithNumberIndexWithoutOtherParameters()
    {
        /** @var ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);
        /** @var AnnotationResolverInterface $annotationResolver */
        $annotationResolver = $this->createMock(AnnotationResolverInterface::class);
        $parameterResolver = new ParameterResolver($container, $annotationResolver);

        $reflectionParameter2 = $this->createMock(ReflectionParameter::class);
        $reflectionParameter2->expects($this->once())->method('getName')->willReturn('b');
        $reflectionParameter2->expects($this->once())->method('isDefaultValueAvailable')->willReturn(false);
        $reflectionParameter2->expects($this->once())->method('hasType')->willReturn(false);

        /** @var ReflectionFunctionAbstract $reflection */
        $reflection = $this->createMock(ReflectionFunctionAbstract::class);
        $reflection->expects($this->once())->method('getParameters')->willReturn([
            $reflectionParameter2
        ]);
        $result = $parameterResolver->resolve($reflection, ['a' => 1, 2, 3]);
        $this->assertEquals([2], $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testResolveWithNumberIndexWithException()
    {
        /** @var ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);
        /** @var AnnotationResolverInterface $annotationResolver */
        $annotationResolver = $this->createMock(AnnotationResolverInterface::class);
        $parameterResolver = new ParameterResolver($container, $annotationResolver);

        $reflectionParameter2 = $this->createMock(ReflectionParameter::class);
        $reflectionParameter2->expects($this->once())->method('getName')->willReturn('b');
        $reflectionParameter2->expects($this->once())->method('isDefaultValueAvailable')->willReturn(false);
        $reflectionParameter2->expects($this->once())->method('hasType')->willReturn(false);

        /** @var ReflectionFunctionAbstract $reflection */
        $reflection = $this->createMock(ReflectionFunctionAbstract::class);
        $reflection->expects($this->once())->method('getParameters')->willReturn([
            $reflectionParameter2
        ]);
        $result = $parameterResolver->resolve($reflection, ['a' => 1]);
    }

    /**
     * @expectedException \Exception
     */
    public function testResolveWithNumberIndexWithTypeWithException()
    {
        $container = new Container();
        $manager = new Manager($container);

        $manager->call(function(T2 $t2) {
            return $t2;
        });
    }
}
