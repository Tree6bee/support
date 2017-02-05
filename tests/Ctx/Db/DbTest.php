<?php

namespace Tests\Tree6bee\Support;

use Tree6bee\Support\Ctx\Db\Db;

class DbTest extends \PHPUnit_Framework_TestCase
{
    private $db;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        //初始化用于测试的config对象
        $this->db = new Db('pgsql:host=127.0.0.1;port=5432;dbname=dbname', 'develop', '');
    }

    public function testQuery()
    {
       // var_dump( $this->db->insert('users', array(
       //     array(
       //         'id'     => true,
       //         'name'   => '张三',
       //     ),
       // ), false));
       // var_dump($this->db->update(
       //     'users',
       //     array(
       //         'name'  => '李四',
       //     ), array(
       //         'id'    => 2,
       //     )
       // ));
       // var_dump($this->db->delete('users', array(
       //     'id'    => 2,
       // )));
       // $ret = $this->db->select('select * from users');
       // print_r($ret);
    }
}
