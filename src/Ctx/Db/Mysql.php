<?php

namespace Tree6bee\Support\Ctx\Db;

use PDO;
use PDOException;
use Exception;

/**
 * 框架MySql数据库辅助类
 * 增加更多的封装，如 transaction 回滚
 */
class Mysql
{
    /**
     * pdo实例
     * exec() | query() | quote() | fetchAll() | fetchColumn() | lastInsertId()
     * beginTransaction() | rollBack() | commit() |
     * errorInfo() | getAttribute(constant('PDO::ATTR_' . $value()
     */
    private $pdo;

    /**
     * 实例化数据库
     * @param string $charset emoji:utf8mb4 MySQL 5.5.3+
     */
    public function __construct($config)
    {
        extract($config);   //@todo 直接采用挨着传参的方式
        // $host, $dbname, $user, $psw, $port = 3306, $charset = 'utf8', $timeout = 3;
        $dsn = $this->getDsn($host, $dbname, $port);
        try {
            $this->pdo = new PDO(
                $dsn,   //PHP 5.3.6+ 对才charset支持
                $user,
                $psw,
                array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset}",   //编码
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => $timeout,
                    // PDO::ATTR_PERSISTENT => TRUE,   //是否使用长连接
                    //在DSN中指定charset的作用只是告诉PDO, 本地驱动转义时使用指定的字符集（并不是设定mysql server通信字符集）
                    //设置mysql server通信字符集，还得使用set names <charset>指令。
                    PDO::ATTR_STRINGIFY_FETCHES => false,   //提取的时候将数值转换为字符串
                    // PDO::ATTR_CASE => PDO::CASE_NATURAL,    //强制列名为指定的大小写
                    PDO::ATTR_EMULATE_PREPARES => false,    //防止本地进行参数转义，不能完全防止sql注入
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                )
            );
            // $this->pdo->exec(
            //     'set time_zone="'.$config['timezone'].'"'
            // );
            // $this->pdo->exec("set session sql_mode='STRICT_ALL_TABLES'");
            // $this->pdo->exec("set session sql_mode=''");
        } catch (PDOException $e) {
            throw new Exception('数据库连接失败:' . $e->getMessage());
        }
    }

    /**
     * 析构函数
     * 关闭数据库连接
     */
    public function __destruct()
    {
        $this->pdo = null;
    }

    /**
     * 获取dsn
     */
    private function getDsn($host, $dbname, $port = 3306)
    {
        // $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
        return "mysql:host={$host};port={$port};dbname={$dbname}";
    }

    /**
     * 查询
     * 屏蔽掉pdo本身的方法
     * 防止错误的使用
     * $ret = $mdb->query('select * from tree6bee where `int` = ?', array(22));
     * print_r($ret);
     */
    public function query($sql, array $bindings)
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($bindings);
        return $statement->fetchAll();
        // return $statement;
    }

    /**
     * 插入数据
     * echo $mdb->insert('tree6bee', array(
     *     array(
     *         'int'   => 69,
     *         'var'   => 'xxxooo',
     *         'boolean'   => true,
     *     ),
     * ));
     */
    public function insert($table, array $values, $ignore = false)
    {
        // $sql = 'INSERT INTO tb (`c1`, `c2`) VALUES (?, ?), (?, ?)';
        if (! is_array(reset($values))) {
            $values = array($values);
        }
        //insert ignore into 的方式需要自己手动封装
        if ($ignore) {
            $sql = 'INSERT IGNORE INTO `' . $table . '` ';
        } else {
            $sql = 'INSERT INTO `' . $table . '` ';
        }
        $sql .= $this->getColumns(array_keys(reset($values)));  //columns
        $sql .= ' VALUES ';

        $ret = $this->getPlaceholdersAndBindings($values);

        $sql .= $this->getInsertPlaceholders($ret['placeholders']);  //columns
        $bindings = $ret['bindings'];
        $statement = $this->pdo->prepare($sql);
        $statement->execute($bindings);
        return $statement->rowCount();
        // $this->pdo->lastInsertId();
    }

    /**
     * 更新
     * echo $mdb->update(
     *     'tree6bee',
     *     array(
     *         'var'   => 'ooo',
     *         'boolean'   => 1,
     *     ),
     *     array(
     *         'id'    => 1,
     *         'int'   => 111,
     *     ), 'limit 1'
     * );
     */
    public function update($table, array $values, array $where, $etc = '')
    {
        // $sql = 'UPDATE tb SET `c1` = ?, `c2` = ? WHERE c3 = ?'
        if (empty($where)) {
            throw new Exception('必须指定数据库更新的条件');
        }
        $sql = 'UPDATE `' . $table . '` SET ';
        $sql .= $this->getPlaceholders($values);
        $sql .= ' WHERE ';
        $sql .= $this->where($where);
        $sql .= ' ' . $etc;
        $bindings = array_merge(array_values($values), array_values($where));
        $statement = $this->pdo->prepare($sql);
        $statement->execute($bindings);
        // $this->pdo = null;
        return $statement->rowCount();  //其实这里没有连接mysql
    }

    /**
     * 删除
     * echo $mdb->del('tree6bee', array(
     *     'int'   => 111,
     * ));
     */
    public function del($table, array $where, $etc = '')
    {
        // $sql = 'DELETE FROM tb WHERE `c1` = 'xyz'';
        if (empty($where)) {
            throw new Exception('必须指定数据库删除的条件');
        }
        $sql = 'DELETE FROM `' . $table . '` WHERE ';
        $sql .= $this->where($where);
        $sql .= ' ' . $etc;
        $bindings = array_values($where);
        $statement = $this->pdo->prepare($sql);
        $statement->execute($bindings);
        // $this->pdo = null;
        return $statement->rowCount();  //其实这里没有连接mysql
    }

    private function getPlaceholders($values)
    {
        $placeholders = array();
        foreach ($values as $column => $binding) {
            $placeholders[] = '`' . $column . '` = ?';
        }
        return implode(', ', $placeholders);
    }

    /**
     * 获取where条件占位
     * array('id'   => 1, 'c1'  => 'abc')
     *      => `id` = ? and `c1` = ?
     */
    private function where(array $values = array())
    {
        $placeholders = array();
        foreach ($values as $column => $binding) {
            $placeholders[] = '`' . $column . '` = ?';
        }
        return implode(' AND ', $placeholders);
    }

    private function getColumns($columns)
    {
        return '( `' . implode('`, `', $columns) . '` )';
    }

    private function getInsertPlaceholders($row)
    {
        // (?, ?),
        return ' (' . implode('), (', $row) . ') ';
    }

    private function getPlaceholdersAndBindings($values)
    {
        $placeholders = array();
        $bindings = array();
            // $placeholders[] = array();
        foreach ($values as $row => $rowValue) {
            $placeholder = array();
            foreach ($rowValue as $index => $val) {
                $bindings[] = $val;
                $placeholder[] = '?';
            }
            $placeholders[] = implode(', ', $placeholder);
        }
        return array(
            'placeholders'  => $placeholders,
            'bindings'      => $bindings,
        );
    }

    public function info()
    {
        $output = array(
            'server' => 'SERVER_INFO',
            'driver' => 'DRIVER_NAME',
            'client' => 'CLIENT_VERSION',
            'version' => 'SERVER_VERSION',
            'connection' => 'CONNECTION_STATUS'
        );
        foreach ($output as $key => $value) {
            $output[$key] = $this->pdo->getAttribute(constant('PDO::ATTR_' . $value));
        }
        return $output;
    }

    /**
     * 调用方法
     */
    public function __call($method, $args)
    {
        //@todo 记录日志
        return call_user_func_array(array($this->pdo, $method), $args);
    }
}
