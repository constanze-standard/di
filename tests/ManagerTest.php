<?php

use ConstanzeStandard\DI\Interfaces\ResolveableInterface;
use ConstanzeStandard\DI\Manager;
use Psr\Container\ContainerInterface;

require_once __DIR__ . '/AbstractTest.php';

class T { }

class InstanceT
{
    public function __construct($a = 1, $b, T $t)
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
        /** @var ContainerInterface $container */
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
}