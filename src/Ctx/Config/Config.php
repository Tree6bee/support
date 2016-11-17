<?php

namespace Ctx\Service\Ctx;

use Tree6bee\Support\Ctx\Config\Repository as ConfigRepository;
use Tree6bee\Ctx\Loader;
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
     * code_base 目录
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
        $this->configDir = $configDir;
        $this->repository = new ConfigRepository;
    }

    /**
     * 获取配置
     * $this->getConfig('upload.ip@common/main');
     */
    public function getConfig($item, $default = null, $level = 'app')
    {
        $options = $this->parseItem($item, $level);
        return $this->getConfigWithOptions($options, $default);
    }

    private function parseItem($item, $level = 'app')
    {
        $basedir = $this->getConfigBaseDir($level);
        if (empty($basedir)) {
            throw new Exception('config base directory do not exist.');
        }
        $basedir = realpath($basedir);
        $itemArr = explode('@', $item);
        $file = $basedir . '/' . $itemArr[1];
        $option = $itemArr[0];
        return compact('file', 'option');
    }

    /**
     * 获取配置文件的基础目录
     */
    private function getConfigBaseDir($level = 'app')
    {
        if ('app' == $level) {
            return $this->configDir;
        }
        return $this->getConfig('security_path@Ctx/main');
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
        if (! file_exists($file)) {
            throw new Exception($file . ' :config file do not exist.');
        }
        $config = Loader\includeFile($file);
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
    public function setConfig($item, $config, $level = 'app')
    {
        $options = $this->parseItem($item, $level);
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
