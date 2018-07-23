<?php

namespace Tests\Tree6bee\Support;

use Tree6bee\Support\Ctx\Db\Connection;
use Tree6bee\Support\Ctx\Db\Pgsql;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    private $db;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        //初始化用于测试的config对象
        $this->db = new Pgsql('pgsql:host=192.168.3.165;port=5432;dbname=postgres', 'postgres', 'xxoo');
    }

    public function testQuery()
    {
//        $data = $this->db->pretend(function () {
//            $this->db->select('select * from users', []);
//        });

//        $data = $this->db->pretend(function () {
//            $this->db->insert('insert into users (name, password) values (?, ?), (?, ?)', ["tom", "a323", "李四", 'acb']);
//        });

//        $data = $this->db->insertGetId('insert into xxx (xx, id) values (?, ?)', ["tom", "a323"], "tt");
//
//        echo "\n --- ";
//        print_r($data);
//        echo "\n --- ";
//
//        exit;
//        $this->db->query()
//            ->table('users')
//            ->get();

        $this->assertNull(null);
    }
}
