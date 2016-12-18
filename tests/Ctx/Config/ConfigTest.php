<?php

namespace Tests\Tree6bee\Support;

use Tree6bee\Support\Ctx\Config\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    private $config;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        //初始化用于测试的config对象
        $this->config = new Config(__DIR__ . '/config');
    }

    public function testGetConfig()
    {
        //测试修改配置
        $this->config->setConfig('a@main', 2);
        $this->assertEquals(2, $this->config->getConfig('a@main'));
        //测试获取子配置
        $this->assertEquals(2, $this->config->getConfig('b.ba@main'));
        //测试获取不存在的配置
        $this->assertEquals(3, $this->config->getConfig('b.bb@main', 3));
    }
}
