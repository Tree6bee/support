<?php

namespace Tree6bee\Support\Ctx\Cache;

use Redis as RedisBase;
use RedisException;
use Exception;

/**
 * 框架Redis辅助类
 * 一些命令说明：BITCOUNT (版本> 2.6.0)
 */
class Redis
{
    /**
     * pdo实例
     */
    private $redis = '';

    /**
     * 实例化数据库
     */
    public function __construct($host, $port = 6379, $timeout = 3)
    {
        $this->redis = new RedisBase();
        try {
            //@todo 增加重连机制
            $this->redis->pconnect($host, $port, $timeout);
        } catch (RedisException $e) {
            throw new Exception('Redis连接失败:' . $e->getMessage());
        }
    }

    /**
     * 调用方法
     */
    public function __call($method, $args)
    {
        try {
            return call_user_func_array(array($this->redis, $method), $args);
        } catch (RedisException $e) {
            //@todo 记录日志等
            throw new Exception('Redis错误:' . $e->getMessage());
        }
    }

    //---以下为一些非正常方法--

    /**
     * 给数组加前缀
     */
    // private function padArr($arr, $pre = '')
    // {
    //     if (empty($pre)) {
    //         return $arr;
    //     }
    //     foreach ($arr as &$v) {
    //         $v = $pre . $v;
    //     }
    //     unset($v);
    //     return $arr;
    // }
    /**
     * 批量获取 redis key值 关联返回
     *
     * @param   array  $arr 要查询的数组
     * @param   string $pre 要查询的缓存key前缀
     * @return  array  关联的缓存结果
     * @example 参数为:_mgetAssoc(array('a', 'b'), 'prefix:'); 返回值为 array('a' => 'v1', 'b' => 'v2')
     */
    // public function mgetAssoc($arr, $pre = '')
    // {
    //     if (empty($arr)) {
    //         return array();
    //     }
    //     $arrKey = $this->padArr($arr, $pre);
    //     $ret = $this->redis->mget($arrKey); //此处大写 mGet
    //     return array_combine($arr, $ret);
    // }
    //
    // /**
    //  * 管道执行举例
    //  */
    // public function _hget2($arr) {
    //     $pipe = $this->redis->multi(Redis::PIPELINE);
    //     foreach ($arr as $key) {
    //         $pipe->get($key);
    //     }
    //     $list = $pipe->exec();
    //     return array_combine($arr, $list);
    // }
    // /**
    //  * 管道执行举例
    //  */
    // public function _hget1($arr) {
    //     $pipe = $this->redis->pipeline();
    //     foreach($arr as $key) {
    //         $pipe->get($key);
    //     }
    //     // $list = $this->redis->exec();
    //     $list = $pipe->exec();
    //     return array_combine($arr, $list);
    // }
    // /**
    //  * 事务执行举例
    //  */
    // public function _hget3($arr) {
    // 	$this->redis->multi();
    // 	foreach ($arr as $key) {
    // 		$this->redis->get($key);
    // 	}
    // 	$list = $this->redis->exec();
    //     if (null === $list) {
    //         throw new Exception('_hget3 失败:' . print_r($arr, true));
    //     }
    //     if (count($list) != count($arr)) {
    //         throw new Exception('_hget3 失败,返回不一致:' . print_r($arr, true));
    //     }
    //     return array_combine($arr, $list);
    // }
}
