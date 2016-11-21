<?php

namespace Tree6bee\Support\Helpers\Exceptions;

use ErrorException;
use Closure;

/**
 * 框架系统异常错误处理接管类
 * 参考 Laravel vendor/laravel/framework/src/Illuminate/Foundation/Bootstrap/HandleExceptions.php
 *
 * @copyright sh7ning 2016.1
 * @author    sh7ning
 * @version   0.0.1
 *
 * @example
 * $handler = new Handler('CtxFramework/1.1');
 * new HandleExceptions(true, function ($e) use ($handler) {
 *      call_user_func([$handler, 'handle'], $e);
 * });
 */
class HandleExceptions
{
    /**
     * @var $testing
     */
    protected $testing;

    protected $callback;

    public function __construct($testing, Closure $callback)
    {
        //初始化
        $this->testing = $testing;
        $this->callback = $callback;
        //接管
        $this->handle();
    }

    protected function handle()
    {
        error_reporting(-1);

        //错误捕获
        set_error_handler(array($this, 'handleError')); //接收所有的错误类型

        //异常捕获
        set_exception_handler(array($this, 'handleException'));

        //程序结束 第一个参数为回调，后续都是作为回调函数的参数
        register_shutdown_function(array($this, 'handleShutdown'), 0);

        //设置为On的时候,如果出现致命错误(fatal error)会在错误页面外多输出一次,所以基本都是Off
        if (! $this->testing) {
            ini_set('display_errors', 'Off');
        }
    }

    /**
     * 错误处理接管
     * 一般用于捕捉  E_NOTICE 、E_USER_ERROR、E_USER_WARNING、E_USER_NOTICE (trigger_error可以触发)
     * 不能捕捉: E_ERROR、 E_PARSE、 E_CORE_ERROR、 E_CORE_WARNING、 E_COMPILE_ERROR、 E_COMPILE_WARNING
     *          和在 调用 set_error_handler() 函数所在文件中产生的大多数 E_STRICT
     *
     * momo
     * $curerrorno = error_reporting();
     * if (($curerrorno & ~$errorno) == $curerrorno) {
     *     return true;
     * }
     */
    public function handleError($errorno, $errorstr, $errorfile, $errorline)
    {
        if (error_reporting() & $errorno) {
            throw new ErrorException($errorstr, 0, $errorno, $errorfile, $errorline);
        }
    }

    /**
     * 运行结束 或 致命错误退出 捕获
     */
    public function handleShutdown($status = 0)
    {
        // if (connection_aborted()) { //@todo 增加对用户主动取消的日志收集
        // }

        if (! is_null($error = error_get_last()) && self::isFatal($error['type'])) {
            $this->handleException(
                new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line'])
            );
        }
    }

    /**
     * Determine if the error type is fatal.
     * E_USER_ERROR E_RECOVERABLE_ERROR:
     */
    protected function isFatal($type)
    {
        return in_array($type, array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE));
    }

    /**
     * 异常处理接管
     */
    public function handleException($e)
    {
        call_user_func($this->callback, $e);
    }
}
