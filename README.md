DI 是一个 PHP 依赖注入管理组件，它与 PSR-11 标准配合，为你的程序提供多种形式的依赖注入解决方案：

## 它的主要功能包括：
- 根据类型提示注入参数 (type-hint)
- 通过注解 (Annotation) 的形式注入
- 对类的属性进行注入
- 通过多种形式向程序中注入自定义的参数
- 获取参数列表

## 基本示例
通过 DI 管理器调用函数，并向函数中注入参数：
```php
<?php

use ConstanzeStandard\Container\Container;
use ConstanzeStandard\DI\Annotation\Params;
use ConstanzeStandard\DI\Manager;

require __DIR__ . '/../vendor/autoload.php';


$injectionTest = new class() {

    /** @Params(arg1 = "foo") */
    public function __invoke($arg1)
    {
        echo $arg1;
    }
};

$container = new Container();
$container->add('foo', 'bar');

$manager = new Manager($container);
$manager->call($injectionTest);
// 输出： bar
```
DI\Manager 可以直接调用一个可调用 (callable) 对象，这个过程中，你可以选择一种方式将参数注入到程序中。接下来我们将由浅入深介绍更多的使用技巧。

## 谁来提供依赖项？
依赖项的来源有两个，一是容器 (`container`)，如上例所示，`@Params(arg1 = "foo")` 就是将名为 `arg1` 的参数与名为 "foo" 的容器数据绑定起来；二是当我们通过 Manager 或 Resolver 调用程序时，也可以手动传入额外的自定义参数。

## 自定义参数
call 方法的第二个参数是自定义参数，我们有两种方式传递自定义参数：
1. 传递以参数名称作为键值的自定义参数：
```php
function injection_test($foo) {
    echo $foo;
}

$manager->call('injection_test', ['foo' => 'bar']);
// 输出： bar
```
这种形式的传参，无论参数在参数列表的什么位置都可以直接绑定。

2. 传递无指向的自定义参数数组：
```php
function injection_test($foo) {
    echo $foo;
}

$manager->call('injection_test', ['bar']);
// 输出： bar
```
这种形式的传参，会按参数列表的顺序，将值依次传递进去。

这两种形式可以结合使用，Manager 会首先注入指定名称(键)的自定义参数，之后会将剩余的，没有任何其他形式的注入，也没有默认值的参数，依次与自定义参数列表中剩余的参数进行绑定。
```php
function injection_test($a, $b, $c) {
    echo $a, $b, $c;
}

$manager->call('injection_test', [1, 'a' => 2, 3]);
// 输出： 213
```
大部分情况下，我们并不推荐你将两种方式结合使用，传参应该按照一定的规则进行，否则便会造成视觉上的混乱。

## 根据类型提示注入参数
`DI\Manager` 支持类型提示形式的参数注入：
```php
class Serve {
    public $foo = 'bar';
}

$container->add(Serve::class, new Serve);

function injection_test(Serve $serve) {
    echo $serve->foo;
};

$manager->call('injection_test');
// 输出： bar
```
如上所示，通过 call 方法运行的程序，加类型声明会被判定为需要注入依赖项的参数，之后 Manager 会从容器中取出对应类型名称的依赖项传入。所以，如果你的参数不需要依赖注入，就不要加任何的类型声明。

## 通过注解 (Annotation) 的形式注入参数
DI 引入了 Annotation 组件，来解析<u>类方法</u>的注解，从而得到依赖关系，完成参数注入，这是一种比较高级的用法。
```php
$injectionTest = new class() {
    /**
     * @Params(arg1 = "foo")
     */
    public function __invoke($arg1)
    {
        echo $arg1;
    }
};

$container = new Container();
$container->add('foo', 'bar');

$manager = new Manager($container);
$manager->call($injectionTest);
// 输出： bar
```
通过注解法，我们可以摆脱类型声明的约束，将普通的容器索引与参数绑定，耦合度将进一步降低。
但需要注意的是，注解法只适用于类方法，并不适用于函数和闭包。

## 参数注入的顺序与规则
现在我们知道了很多种参数注入的方式，你可以随意的将它们搭配使用，并不会产生冲突。但当多种注入方法指向同一参数时，也会按照一定的优先级顺序进行注入。

Manager 在注入参数之前，按顺序做了以下几件事：
- 解析方法的注解，并获取参数列表
- 将注解得到的参数列表与自定义参数列表合并得到最终的参数列表（自定义参数列表会覆盖注解）。
- 将参数列表中，与参数名称相对应的项注入给对应的参数。
- 检测方法参数中，具有默认值的参数，然后跳过它们。
- 检测参数的类型声明，然后根据参数类型，从容器中获取对应的数据并注入给参数。
- 如果方法中有剩余的参数没有被处理，会将参数列表中没有指定名称(键)的参数，按顺序赋给它们。如果参数列表中没有足够的数据，将抛出一个 `\InvalidArgumentException`。

所以，注入方式的优先级，按照从大到小的顺序，依次为：
```sh
与参数名称相对应的自定义参数 > 注解 > 参数默认值 > 类型提示 > 未指定参数名称的自定义参数
```

## 类的实例化与构造方法注入
Manager 除了可以调用程序之外，也可以实例化一个对象，在实例化的同时，对构造方法进行参数注入。
```php
class Client
{
    /**
     * @Params(foo = "foo")
     */
    public function __construct($foo)
    {
        $this->foo = $foo;
    }
}

$container->add('foo', 'bar');

$client = $manager->instance(Client::class);
echo $client->foo;
// 输出：bar
```
`instance` 方法与 call 方法的第一个参数是类的名称，第二个参数是向构造方法传递的自定义参数数组。

通过 `instance` 方法对构造方法的参数注入与普通方法相同，但 instance 方法返回的是实例化后的对象实例。

## 对类属性进行依赖注入
我们可以通过注解 (Annotation) 对类的属性进行依赖注入。
```php
use ConstanzeStandard\DI\Annotation\Property;

class Client
{
    /** @Property("foo") */
    public $foo;
}

$container->add('foo', 'bar');

$client = new Client();
$manager->resolvePropertyAnnotation($client);

echo $client->foo;
// 输出：bar
```
通过 `Property` 注解，将类属性与一个容器的数据绑定，并在调用 `resolvePropertyAnnotation` 方法时将依赖项注入到属性中。

# Resolver
Manager 封装了比较常用的依赖注入操作，如果你还需要更加细化的功能，可以使用不同的 Resolver 进行控制。

## 获取参数列表
使用 `CallableResolver` 获取 Resolver 对象，然后你可以通过它获取可调用对象的参数列表：
```php

class serve { }

$container->add(serve::class, new Serve());

function resolver_test(Serve $serve, $foo) { }

$resolver = $manager->getCallableResolver('resolver_test');
$params = $resolver->resolveParameters(['foo' => 'bar']);

print_r($params);
/* 输出：
Array
(
    [0] => serve Object
        (
        )

    [1] => bar
)
*/
```

获取构造方法的参数列表与可调用对象相似，但你需要通过 `ConstructResolver` 进行操作：
```php
class Client
{
    public function __construct(serve $serve, $foo)
    {
        
    }
}

$resolver = $manager->getConstructResolver(Client::class);
$params = $resolver->resolveParameters(['foo' => 'bar']);

print_r($params);
/* 输出：
Array
(
    [0] => serve Object
        (
        )

    [1] => bar
)
*/
```

## AnnotationResolver
`AnnotationResolver` 是针对注解形式的依赖注入进行控制的组件。
`AnnotationResolver` 可以帮助你对类属性进行注入、获取在方法上注解的参数、对方法进行注解形式的依赖注入并调用。
## 根据类属性获取 Property 对象：
获取 Property 对象需要借助 PHP 的 Reflection 机制：
```php
use ConstanzeStandard\DI\Annotation\Property;

class Client
{
    /** @Property("foo") */
    public $property1;
}

$instance = new Client();
$reflectionClass = new \ReflectionClass($instance);
$reflectionProperty = $reflectionClass->getProperty('property1');

$resolver = $manager->getAnnotationResolver();
$property = $resolver->getProperty($reflectionProperty);

$name = $property->getName();  // foo
```

## 类属性注入
通过 Property 对类属性进行依赖注入，这与 Manager 相同（实际上 Manager 在内部调用了 Resolver）。
```php
$container->add('foo', 'bar');

$resolver = $manager->getAnnotationResolver();
$resolver->resolveProperty($instance);

echo $instance->property1;  // 输出：bar
```

## 获取类类方法上注解的参数列表
获取注解参数需要借助 PHP 的 Reflection 机制：
```php
use ConstanzeStandard\Container\Container;
use ConstanzeStandard\DI\Annotation\Params;

class Client
{
    /**
     * @Params(
     *  foo = "foo"
     * )
     */
    public function test($foo, $abc = 1)
    {

    }
}

$container = new Container();
$container->add('foo', 'bar');

$instance = new Client();
$reflectionMethod = new \ReflectionMethod($instance, 'test');

$resolver = $manager->getAnnotationResolver();
$parameters = $resolver->resolveMethodParameters($reflectionMethod);

print_r($parameters);
/* 输出：
Array
(
    [foo] => bar
)
*/
```
需要注意的是，`resolveMethodParameters` 方法只获取注解的参数列表，其他参数将被忽略。

## 自定义 Annotation Reader
DI 集成了 `doctrine/annotations` 帮助 Resolver 解析注释，这个组件的消耗比较大，所以通常情况下，需要使用一种 Annotation 缓存来规避性能损耗。这时，你可以将自定义的 Annotation Reader 传入 Manager 构造方法的第三个参数中，以替换默认的 `\Doctrine\Common\Annotations\AnnotationReader`.
```php
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\PhpFileCache;

$reader = new CachedReader(
    new AnnotationReader(),
    new PhpFileCache(__DIR__ . '/cache'),
    $debug = true
);
$annotationResolver = new AnnotationResolver($container, $reader);

$manager = new Manager($container, null, $annotationResolver);
```

这样就可以使用带有缓存功能的 `CachedReader` 以提升性能了。
