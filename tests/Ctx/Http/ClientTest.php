<?php

namespace Tests\Tree6bee\Support;

use Tree6bee\Support\Ctx\Http\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testRequest()
    {
        $curl = new Client;
        $url = 'http://test.sh7ne.dev/?xx=yy';
        $body = array('a' => 'b');
        $headers = array('User-Agent: CtxRpc 1.0');
        // $ret = $curl->request('post', $url, $body, $headers, [CURLOPT_HEADER => true])->result();
        // var_dump($ret);exit;
    }
}
