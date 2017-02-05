<?php

namespace Tests\Tree6bee\Support;

use Tree6bee\Support\Ctx\Db\Connection;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    private $db;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        //初始化用于测试的config对象
        $this->db = new Connection('pgsql:host=127.0.0.1;port=5432;dbname=dbname', 'develop', '');
    }

    public function testQuery()
    {
        //事务
        $id = rand(100, 1000);
        return ;    //屏蔽测试

        try {
            $this->db->transaction(function($db) use ($id) { 

                //写入
                var_dump( $db->insert('users', array(
                    array(
                        'id'     => $id,
                        'name'   => '张三',
                    ),
                ), false));
                print_r( $db->select('select * from users where id = ?', array($id)) );

                //更新
                var_dump($db->update(
                    'users',
                    array(
                        'name'  => '李四',
                    ), array(
                        'id'    => $id,
                    )
                ));
                print_r( $db->select('select * from users where id = ?', array($id)) );

                //删除
                var_dump($db->delete('users', array(
                    'id'    => $id,
                )));
                throw new \Exception('transaction test');
            });
        } catch (\Exception $e) {
            print_r( $this->db->select('select * from users where id = ?', array($id)) );
            echo $e->getMessage();
        }
    }
}
