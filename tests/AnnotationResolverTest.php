<?php

use ConstanzeStandard\DI\Annotation\Params;
use ConstanzeStandard\DI\Annotation\Property;
use ConstanzeStandard\DI\Resolver\AnnotationResolver;
use Doctrine\Common\Annotations\Reader;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;

require_once __DIR__ . '/AbstractTest.php';

class Property_T
{
    /**
     * @Property("key1")
     */
    private $foo;

    public function getFoo()
    {
        return $this->foo;
    }


    /**
     * @Params(
     *  a = "key2"
     * )
     */
    public function method1_t($a)
    {
        return $a;
    }
}

class AnnotationResolverTest extends AbstractTest
{
    public function testResolveProperty()
    {
        /** @var MockObject|ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('get')->with('key1')->willReturn(1);
        /** @var MockObject|Property $property */
        $property = $this->createMock(Property::class);
        $property->expects($this->once())->method('getName')->willReturn('key1');
        /** @var MockObject|Reader $reader */
        $reader = $this->createMock(Reader::class);
        $reader->expects($this->once())->method('getPropertyAnnotation')->willReturn($property);

        $property_T = new Property_T();
        $annotationResolver = new AnnotationResolver($container, $reader);
        $result = $annotationResolver->resolveProperty($property_T);
        $this->assertEquals($result, $property_T);
        $this->assertEquals(1, $result->getFoo());
    }

    /**
     * @throws ReflectionException
     */
    public function testResolveMethodParameters()
    {
        /** @var MockObject|ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('get')->with('key2')->willReturn(1);
        /** @var MockObject|Params $params */
        $params = $this->createMock(Params::class);
        $params->expects($this->once())->method('getParams')->willReturn([
            'a' => 'key2'
        ]);
        /** @var MockObject|Reader $reader */
        $reader = $this->createMock(Reader::class);
        $reader->expects($this->once())->method('getMethodAnnotation')->willReturn($params);

        $property_T = new Property_T();
        $annotationResolver = new AnnotationResolver($container, $reader);

        $reflectionMethod = new ReflectionMethod($property_T, 'method1_t');
        $result = $annotationResolver->resolveMethodParameters($reflectionMethod);
        $this->assertEquals(['a' => 1], $result);
    }

    // public function testResolveMethod()
    // {
    //     /** @var ContainerInterface $container */
    //     $container = $this->createMock(ContainerInterface::class);
    //     $container->expects($this->once())->method('get')->with('key2')->willReturn(1);
    //     /** @var Params $params */
    //     $params = $this->createMock(Params::class);
    //     $params->expects($this->once())->method('getParams')->willReturn([
    //         'a' => 'key2'
    //     ]);
    //     /** @var Reader $reader */
    //     $reader = $this->createMock(Reader::class);
    //     $reader->expects($this->once())->method('getMethodAnnotation')->willReturn($params);

    //     $property_T = new Property_T();
    //     $annotationResolver = new AnnotationResolver($container, $reader);

    //     $result = $annotationResolver->resolveMethod($property_T, 'method1_t');
    //     $this->assertEquals($result, 1);
    // }
}
