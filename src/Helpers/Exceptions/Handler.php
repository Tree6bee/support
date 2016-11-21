<?php

namespace Tree6bee\Support\Helpers\Exceptions;

use Tree6bee\Support\Helpers\Exceptions\Reporter\Debuger;

class Handler
{
    protected $collapseDir;

    protected $cfVersion;

    /**
     * @param string $collapseDir 错误显示页面中需要折叠的代码目录
     * @param string $cfVersion 错误显示页面的框架标识
     */
    public function __construct($collapseDir = '', $cfVersion = 'CtxFramework/1.0')
    {
        $this->collapseDir = $collapseDir;
        $this->cfVersion = $cfVersion;
    }

    /**
     * 异常接管
     * 1. 记录框架异常日志
     * 2. 展示:
     *      * 命令行:直接输出
     *      * http方式:
     *          * 测试环境：输出错误页面
     *          * 其它环境(如 正式环境)：
     *              * 框架错误：框架处理
     *              * 其它：直接返回500错误
     */
    public function handle($e)
    {
        //有可能为 Exception 也有可能为 Throwable 需要进行转化为 Exception
        // 如果没有这两个函数的调用，那么在后续的错误处理过程中，当再次产生异常或是错误时，可能造成死循环
        restore_error_handler();
        restore_exception_handler();

        $ex = $this->getLogOfException($e);
        $this->report($ex, 'exception');

        $this->render($e);
    }

    protected function render($e)
    {
        if (php_sapi_name() == 'cli') { //命令行模式
            echo "\nerror trace:\n" .
                print_r(array_slice($e->getTrace(), 0, 6), true) . "\n";
            return true;
        } else {    //web运行方式
            if ($this->wantsJson()) {
                //@todo
            } else {
                return (new Debuger($this->collapseDir, $this->cfVersion))->displayException($e);
            }
        }
    }

    /**
     * 获取记录日志用的异常字符串
     */
    protected function getLogOfException($e)
    {
        return '(' . get_class($e) . ':' . $e->getCode() . ') ' .
            $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
    }

    /**
     * 判断是否需要返回json
     *
     * @todo 完善后迁移到request中
     * 判断 header中Content-Type是否包含'/json' 或 '+json'
     * 判断是否ajax请求 XMLHttpRequest
     */
    protected function wantsJson()
    {
        //isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'XMLHttpRequest' == $_SERVER['HTTP_X_REQUESTED_WITH']
        return false;
    }

    /**
     * 错误日志记录
     */
    protected function report($ex, $fileName = 'error', $cutDate = true)
    {
        //附加时间
        $content = '[' . date('Y-m-d H:i:s') . ' ' . date_default_timezone_get() . '] ';
        //错误信息
        $content .= $ex;
        //附加uri
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'Unkown';
        $content .= ' [' . $request_uri . ']' .PHP_EOL;

        //根据情况决定是否记录超全局变量，方便排查用户访问错误
        // gethostname();   //服务器主机名，方便排查集群中的具体机器错误
        // $GLOBALS $_SERVER $_REQUEST $_POST $_GET $_FILES $_ENV $_COOKIE $_SESSION
        // $_SERVER['REQUEST_URI'] $_SERVER['SCRIPT_NAME'] $_SERVER['HTTP_REFERER']
        // var_export($_SERVER, true);
        // var_export($_COOKIE, true);
        // var_export($_REQUEST, true);

        //@todo 
        // return Log::write($content, $fileName, $cutDate);
    }
}
