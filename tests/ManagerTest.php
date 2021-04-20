<?php

use ConstanzeStandard\DI\Interfaces\AnnotationResolverInterface;
use ConstanzeStandard\DI\Interfaces\ParameterResolverInterface;
use ConstanzeStandard\DI\Interfaces\ResolveableInterface;
use ConstanzeStandard\DI\Manager;
use ConstanzeStandard\DI\Resolver\CallableResolver;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;

require_once __DIR__ . '/AbstractTest.php';

/**
 * @return int
 * @noinspection PhpUnused
 */
function t1(): int
{
    return 1;
}

class TC2
{
    public function ts(): int
    {
        return 1;
    }
}

class T { }

class InstanceT
{
    public function __construct($b, T $t, $a = 1)
    {
        $this->a = $a;
        $this->b = $b;
        $this->t = $t;
    }
}

class ManagerTest extends AbstractTest
{
    public function testGetContainer()
    {
        /** @var ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);
        $manager = new Manager($container);

        $this->assertEquals($container, $manager->getContainer());
    }

    public function testGetCallableResolver()
    {
        /** @var ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);
        $manager = new Manager($container);

        $result = $manager->getCallableResolver(function() {
            return 1;
        });
        $this->assertInstanceOf(ResolveableInterface::class, $result);
    }

    public function testGetConstructResolver()
    {
        /** @var ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);
        $manager = new Manager($container);

        $result = $manager->getConstructResolver('aa');
        $this->assertInstanceOf(ResolveableInterface::class, $result);
    }

    public function testResolveWithStringCallable()
    {
        /** @var ParameterResolverInterface $parameterResolver */
        $parameterResolver = $this->createMock(ParameterResolverInterface::class);
        $callableResolver = new CallableResolver('t1', $parameterResolver);
        $result = $callableResolver->resolve();
        $this->assertEquals(1, $result);
    }

    public function testResolveWithArrayCallable()
    {
        /** @var ParameterResolverInterface $parameterResolver */
        $parameterResolver = $this->createMock(ParameterResolverInterface::class);
        $callableResolver = new CallableResolver([new TC2, 'ts'], $parameterResolver);
        $result = $callableResolver->resolve();
        $this->assertEquals(1, $result);
    }

    public function testCall()
    {
        /** @var ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);
        $manager = new Manager($container);

        $result = $manager->call(function($a) {
            return $a;
        }, ['a' => 'test']);

        $this->assertEquals('test', $result);
    }

    public function testInstance()
    {
        $t = new T;
        /** @var MockObject|ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('get')->with(T::class)->willReturn($t);
        $container->expects($this->once())->method('has')->with(T::class)->willReturn(true);
        $manager = new Manager($container);

        $result = $manager->instance(InstanceT::class, ['b' => 'bb']);
        $this->assertInstanceOf(InstanceT::class, $result);
        $this->assertEquals('bb', $result->b);
        $this->assertEquals(1, $result->a);
        $this->assertInstanceOf(T::class, $result->t);
    }

    public function testResolvePropertyAnnotation()
    {
        $instance = new stdClass;
        /** @var MockObject|ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);
        /** @var MockObject|AnnotationResolverInterface $annotationResolver */
        $annotationResolver = $this->createMock(AnnotationResolverInterface::class);
        $annotationResolver->expects($this->once())->method('resolveProperty')->with($instance)->willReturn($instance);
        $manager = new Manager($container, null, $annotationResolver);
        $result = $manager->resolvePropertyAnnotation($instance);
        $this->assertEquals($result, $instance);
    }
}
