<?php

use ConstanzeStandard\DI\Annotation\Params;
use ConstanzeStandard\DI\Annotation\Property;

require_once __DIR__ . '/AbstractTest.php';

class AnnotationTest extends AbstractTest
{
    public function testPropertyGetName()
    {
        $property = new Property(['value' => 1]);
        $this->assertEquals(1, $property->getName());
    }

    public function testParamsGetParams()
    {
        $property = new Params(['v1' => 1]);
        $this->assertEquals(['v1' => 1], $property->getParams());
    }
}
