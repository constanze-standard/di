<?php

use ConstanzeStandard\DI\Interfaces\AnnotationResolverInterface;
use ConstanzeStandard\DI\Manager;
use ConstanzeStandard\DI\Resolver\ParameterResolver;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;

require_once __DIR__ . '/AbstractTest.php';

class T2
{
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
        $reflectionParameter2->expects($this->once())->method('isOptional')->willReturn(false);
        $reflectionParameter2->expects($this->once())->method('hasType')->willReturn(false);

        /** @var MockObject|ReflectionFunctionAbstract $reflection */
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
        $parameterResolver = $this->createParameterResolver();
        $reflection = $this->createReflectionWithNameB();
        $result = $parameterResolver->resolve($reflection, ['a' => 1, 2, 3]);
        $this->assertEquals([2], $result);
    }

    public function testResolveWithNumberIndexWithException()
    {
        $this->expectException(InvalidArgumentException::class);
        $parameterResolver = $this->createParameterResolver();
        $reflection = $this->createReflectionWithNameB();
        $parameterResolver->resolve($reflection, ['a' => 1]);
    }

    public function testResolveWithNumberIndexWithTypeWithException()
    {
        $this->expectException(Exception::class);
        $container = $this->createMock(ContainerInterface::class);
        $manager = new Manager($container);

        $manager->call(function(T2 $t2) {
            return $t2;
        });
    }

    private function createParameterResolver(): ParameterResolver
    {
        /** @var MockObject|ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);
        /** @var AnnotationResolverInterface $annotationResolver */
        $annotationResolver = $this->createMock(AnnotationResolverInterface::class);
        return new ParameterResolver($container, $annotationResolver);
    }

    private function createReflectionWithNameB(): MockObject|ReflectionFunctionAbstract
    {
        /** @var MockObject|ReflectionParameter $reflectionParameter2 */
        $reflectionParameter2 = $this->createMock(ReflectionParameter::class);
        $reflectionParameter2->expects($this->once())->method('getName')->willReturn('b');
        $reflectionParameter2->expects($this->once())->method('isOptional')->willReturn(false);
        $reflectionParameter2->expects($this->once())->method('hasType')->willReturn(false);

        /** @var MockObject|ReflectionFunctionAbstract $reflection */
        $reflection = $this->createMock(ReflectionFunctionAbstract::class);
        $reflection->expects($this->once())->method('getParameters')->willReturn([
            $reflectionParameter2
        ]);
        return $reflection;
    }
}
