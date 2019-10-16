DI 是一个 PHP 依赖注入管理组件，它与 PSR-11 标准配合，为你的程序提供多种形式的依赖注入解决方案：

## 它的主要功能包括：
- 根据类型提示注入参数 (type-hint)
- 通过注解 (Annotation) 的形式注入参数
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

## container 和 DI 的关系
