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
        try {
            /** table structure:  id, name, password */
            $this->db->transaction(function () {
                //insert
                $insertCount = $this->db->insert('insert into users (name, password) values (?, ?), (?, ?)', [
                    'name1',
                    'password1',
                    'name2',
                    'password2',
                ]);
                $this->assertEquals(2, $insertCount);

                //insert get id
                $insertId = $this->db->insertGetId('insert into users (name, password) values (?, ?)', ['name3', 'password3',], 'id');

                //select
                $data = $this->db->select('select * from users where id = :id', [
                    ':id' => $insertId,
                ]);
                $this->assertEquals(1, count($data));
                $this->assertEquals('name3', $data[0]['name']);

                //update
                $updateCount = $this->db->update('update users set name = :name where password = :password', [
                    ':name'     => 'name2',
                    ':password' => 'password1',
                ]);
                $this->assertEquals(1, $updateCount);

                $data = $this->db->select('select * from users where name = :name', [
                    ':name' => 'name2',
                ]);
                $this->assertEquals(2, count($data));

                //delete
                $updateCount = $this->db->delete('delete from users where password = :password', [
                    ':password' => 'password1',
                ]);
                $this->assertEquals(1, $updateCount);

                $data = $this->db->select('select * from users where name = :name', [
                    ':name' => 'name2',
                ]);
                $this->assertEquals(1, count($data));
                $this->assertEquals('password2', $data[0]['password']);


                //query insert
                $insertCount = $this->db->table('users')->insert([
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
                ]);

                $this->assertEquals(3, $insertCount);

                throw new \Exception('db operate test done.');
            });
        } catch (\Throwable $e) {
            echo "\ndb test: " . $e->getMessage() . "\n";
            throw $e;
        }

        $this->assertNull(null);
    }
}
