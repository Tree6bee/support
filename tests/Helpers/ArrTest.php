<?php

namespace Tests\Tree6bee\Support;

use Tree6bee\Support\Helpers\Arr;

class ArrTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $arr = array(
            'a' => 1,
        );

        $this->assertEquals(1, Arr::get($arr, 'a'));
        $this->assertEquals(2, Arr::get($arr, 'b', 2));
    }

    public function testSet()
    {
        $arr = array(
            'a' => 1,
        );

        Arr::set($arr, 'a', 2);

        $this->assertEquals(2, Arr::get($arr, 'a'));
    }
}
