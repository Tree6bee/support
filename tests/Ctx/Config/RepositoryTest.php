<?php

namespace Tests\Tree6bee\Support;

use Tree6bee\Support\Ctx\Config\Repository as ConfigRepository;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    private $config;

    private $file = __DIR__ . '/data/config';

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        //初始化用于测试的config对象
        $this->config = new ConfigRepository();
        $this->config->set($this->file, include $this->file . '.php');
    }

    public function testGetConfig()
    {
        //测试修改配置
        $this->config->set($this->file . '.a', 2);
        $this->assertEquals(2, $this->config->get($this->file . '.a'));
        //测试获取子配置
        $this->assertEquals(2, $this->config->get($this->file . '.b.ba'));
        //测试获取不存在的配置
        $this->assertEquals(3, $this->config->get($this->file . '.b.bb', 3));
    }
}
