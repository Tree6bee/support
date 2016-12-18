<?php

namespace Tree6bee\Support\Ctx\Http;

use Exception;

/**
 * 简单curl实现
 */
class EasyCurl
{
    //请求超时时间
    public $timeout = 3;

    //代理
    // public $proxy = null;

    //发送header
    // $user_agent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3
    // (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4';
    // $header = array(
    //     "User-Agent: $user_agent",
    //     "User-Agent: " . $_SERVER['HTTP_USER_AGENT'],
    // );

    //是否接受header
    private $withHeader = false;

    //指定最多的 HTTP 重定向次数
    public $maxRedirs = 0;

    //cookieFile 保存cookie的文件
    public $cookieFile;

    //cookieJar 发起请求的cookie的文件
    public $cookieJar;

    //cookie 如 $cookie = 'mycookie=123;name1=var1; name2=var2;';
    public $cookie;

    /**
     *
     * CURL-post方式获取数据
     * 可以上传文件 image/jpg 等
     * 如 "@{$file};type=audio/amr" 或 "@{$file}"
     *
     * @param string $method
     * @param string $url URL
     * @param array  $body POST数据
     * @param array $headers
     *
     * @return array
     * @throws Exception
     */
    public function request($method = 'get', $url, $body = array(), $headers = array())
    {
        if (empty($url)) {
            throw new Exception('缺少url参数');
        }

        $ch = curl_init();

        if ($method == 'get') {
            $param = http_build_query($body);  //可以不用做这个操作
            $url = strpos($url, '?') ? $url . '&' . $param : $url . '?' . $param;
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
            $body = http_build_query($body);
            curl_setopt($ch, CURLOPT_POST, true); //发送一个常规的Post请求
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);//Post提交的数据包
        }

        //设置请求的Header
        if (! empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        //代理的处理
        // if (!is_null($proxy)) curl_setopt ($ch, CURLOPT_PROXY, $this->proxy);

        //https的处理
        // $ssl = stripos($url, 'https://') === 0 ? true : false;
        // if ($ssl) {
        //  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        // 	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        // }
        //https不验证证书
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        //cookie
        //CURLOPT_COOKIE : 设定HTTP请求中"Cookie: "部分的内容。多个cookie用分号分隔，分号后带一个空格(例如， "fruit=apple; colour=red")。
        if (! empty($this->cookieFile)) {
            //包含cookie数据的文件名，cookie文件的格式可以是Netscape格式，或者只是纯HTTP头部信息存入文件。
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        }
        if (! empty($this->cookieJar)) {
            //连接结束后保存cookie信息的文件。
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieJar);
        }

        if (! empty($this->cookie)) {
            curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
        }

        //跳转后是否接着
        //启用时会将服务器服务器返回的"Location: "放在header中递归的返回给服务器，
        //使用CURLOPT_MAXREDIRS可以限定递归返回的数量。
        if ($this->maxRedirs > 0) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, $this->maxRedirs);
        }

        //启用时会将头文件的信息作为数据流输出。
        if ($this->withHeader) {
            curl_setopt($ch, CURLOPT_HEADER, true);
        } else {
            curl_setopt($ch, CURLOPT_HEADER, false);
        }
        //忽略数据体，只要http头
        // curl_setopt($ch, CURLOPT_NOBODY, true);

        //将curl_exec()获取的信息以文件流的形式返回，而不是直接输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //设置cURL执行超时时间，允许执行的最长秒数
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        //http://www.laruence.com/2014/01/21/2939.html
        // curl_setopt($ch, CURLOPT_NOSIGNAL, 1);

        $response = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        if ($curl_errno > 0) {
            $error = sprintf("curl error=%s, errno=%d.", curl_error($ch), $curl_errno);
            curl_close($ch);
            throw new Exception($error);
        }
        //$curl_info 包含信息包括:url, content_type, http_code, header_size, request_size, total_time
        $curl_info = curl_getinfo($ch);
        curl_close($ch);

        //每个的后边都带了空格 " mycookie=deleted; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0"
        //解决办法:substr($cookie, 1)
        // preg_match_all('/Set\-Cookie:(.*)/i', $header, $matches);
        // if (! empty($matches[1])) {
        //     var_dump($matches[1]);
        // }
        if ($this->withHeader) {
            $headerSize = $curl_info['header_size'];
            $header = $this->parseHeader(substr($response, 0, $headerSize));
            $body = substr($response, $headerSize);
        } else {
            $header = array();
            $body = $response;
        }

        return array(
            'http_code' => $curl_info['http_code'],
            'header'    => $header,
            'body'      => $body,
            'curl_info' => $curl_info,
            'total_time' => $curl_info['total_time'],
        );
    }

    /**
     * 拆解http头
     */
    private function parseHeader($header = '')
    {
        $headers = array();
        $ret = explode("\r\n\r\n", trim($header));
        foreach ($ret as $row) {
            $headers[] = explode("\r\n", trim($row));
        }
        if (count($ret) == 1) {
            return reset($headers);
        } else {
            return $headers;
        }
    }
}
