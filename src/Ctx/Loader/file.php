<?php

namespace Tree6bee\Support\Ctx\Loader;

/**
 * Prevents access to $this/self from included files.
 *
 * 加载模块ctx入口会用到
 * 入口文件命名不符合自动加载，必须手动加载，同时阻止错误的模块调用方式
 *
 * 配置文件加载会用到
 * autoload会用到
 */
function includeFile($file)
{
    return include $file;
}
