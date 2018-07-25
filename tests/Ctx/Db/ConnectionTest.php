<?php

namespace Tests\Tree6bee\Support;

use Tree6bee\Support\Ctx\Db\PostgresConnection;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    private $db;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        //初始化用于测试的config对象
        $this->db = new PostgresConnection('pgsql:host=192.168.3.165;port=5432;dbname=postgres', 'postgres', 'xxoo');
    }

    public function testQuery()
    {
//        $data = $this->db->pretend(function () {
//            $this->db->select('select * from users', []);
//        });

//        $data = $this->db->pretend(function () {
//            $this->db->insert('insert into users (name, password) values (?, ?), (?, ?)', ["tom", "a323", "李四", 'acb']);
//        });

//        $data = $this->db->insertGetId("insert into users(name, password) values('a', 'a'),('b', 'b'),('c', 'c'),('d', 'd')", [], 'id');
//        print_r($data);

        $data = [
            [
                'name'      => 'xx',
                'password'  => 'yy',
            ],
            [
                'name'      => 'aa',
                'password'  => 'bb',
            ],
            [
                'name'      => 'tt',
                'password'  => 'ss',
            ],
        ];

        $this->db->transaction(function () use ($data) {
            $ret = $this->db->table('users')->insert($data);
            print_r($this->db->getLastQueryLog());
            print_r($ret);
            throw new \Exception('test');
        });

        $this->assertNull(null);
    }
}
