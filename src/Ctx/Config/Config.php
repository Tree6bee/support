<?php

namespace Tree6bee\Support\Ctx\Config;

use Tree6bee\Support\Ctx\Config\Repository as ConfigRepository;
use Exception;

/**
 * 框架配置辅助类
 *
 * @example
 * print_r($this->ctx->Ctx->getConf('upload.ip@common/main'));
 * $this->ctx->Ctx->setConf('upload.ip@common/main', '123');
 * print_r($this->ctx->Ctx->getConf('upload.ip@common/main'));
 * var_dump($this->ctx->Ctx->getSConf('default.master.host@db'));
 */
class Config
{
    /**
     * config 目录
     */
    private $configDir;

    /**
     * 配置的数据仓库
     */
    private $repository;

    /**
     * 构造函数
     */
    public function __construct($configDir)
    {
        $this->configDir = realpath($configDir);
        $this->repository = new ConfigRepository;
    }

    /**
     * 获取配置
     * $this->getConfig('upload.ip@common/main');
     */
    public function getConfig($item, $default = null)
    {
        $options = $this->parseItem($item);
        return $this->getConfigWithOptions($options, $default);
    }

    private function parseItem($item)
    {
        $itemArr = explode('@', $item);
        $file = $this->configDir . '/' . $itemArr[1];
        $option = $itemArr[0];
        return compact('file', 'option');
    }

    /**
     * 根据文件名和配置option获取具体配置
     */
    private function getConfigWithOptions($options, $default = null)
    {
        if ($this->repository->has($options['file'])) {
            if (empty($options['option'])) {
                return $this->repository->get($options['file'], $default);
            } else {
                return $this->repository->get($options['file'] . '.' . $options['option'], $default);
            }
        }
        $file = $options['file'] . '.php';
        // if (! file_exists($file)) {  //采用了require
        //     throw new Exception($file . ' :config file do not exist.');
        // }
        $config = require $file;
        $this->repository->set($options['file'], $config);

        return $this->getConfigWithOptions($options, $default);
    }

    /**
     * 动态修改配置
     *
     * @example
     * $this->ctx->Ctx->config->setConfig($item, $config, 'app');
     * 设置安全配置
     * $this->ctx->Ctx->config->setConfig($item, $config, 'security');
     */
    public function setConfig($item, $config)
    {
        $options = $this->parseItem($item);
        if (empty($options['option'])) {
            $this->repository->set($options['file'], $config);
        } else {
            if (! $this->repository->has($options['file'])) {
                //预热配置，防止错误的覆盖
                $this->getConfigWithOptions($options);
            }
            $this->repository->set($options['file'] . '.' . $options['option'], $config);
        }
    }
}
