<?php

namespace Tree6bee\Support\Ctx\Db;

use PDO;
use Exception;
use Throwable;
use Closure;

/**
 * 框架MySql数据库辅助类
 * 每个实例代表一次数据库连接
 */
class Connection
{
    /**
     * pdo实例
     * exec() | query() | quote() | fetchAll() | fetchColumn() | lastInsertId()
     * beginTransaction() | rollBack() | commit() |
     * errorInfo() | getAttribute(constant('PDO::ATTR_' . $value()
     */
    protected $pdo;

    /**
     * The number of active transactions.
     * 如果大于 0 则不用多次调用开启事务
     *
     * @var int
     */
    protected $transactions = 0;

    /**
     * 超时时间
     */
    protected $timeout = 3;

    /**
     * Db constructor.
     * @param string $dsn dsn
     *        - mysql
     *          mysql:host=%s;port=%s;dbname=%s;charset={$charset}
     *        - pgsql
     *          pgsql:host=%s;port=%s;dbname=%s
     *        - sqlite3
     *          sqlite:/db_dir/mydb.db  //文件
     *          sqlite::memory: //内存中
     * @param string $user
     * @param string $password
     * @param array $options
     */
    public function __construct($dsn, $user = null, $password = null, array $options = array())
    {
        $options = $this->getDefaultOptions() + $options + array(PDO::ATTR_TIMEOUT => $this->timeout);
        $this->pdo = new PDO($dsn, $user, $password, $options);
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
     * @return array
     */
    protected function getDefaultOptions()
    {
        return array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,    //防止本地进行参数转义，不能完全防止sql注入
            // PDO::ATTR_PERSISTENT => TRUE,   //是否使用长连接
            //PDO::ATTR_STRINGIFY_FETCHES => false,   //提取的时候将数值转换为字符串
            // PDO::ATTR_CASE => PDO::CASE_NATURAL,    //强制列名为指定的大小写
            //在DSN中指定charset的作用只是告诉PDO, 本地驱动转义时使用指定的字符集（并不是设定mysql server通信字符集）
            //设置mysql server通信字符集，还得使用set names <charset>指令。
            //PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset}",   //编码
        );
    }

    /**
     * 查询
     * $ret = $mdb->select('select * from tree6bee where id = ?', array(22));
     *
     * @param string $sql 查询的 sql 如 select * from tree6bee where id = ?
     * @param array $bindings 绑定的参数 如 array(22)
     * @param bool $statement 是否返回 PDOStatement，不返回则直接返回查询的结果数组
     * @return array|\PDOStatement
     */
    public function select($sql, array $bindings = array(), $statement = false)
    {
        $stmt = $this->pdo->prepare($sql);
        $this->bindValues($stmt, $bindings);
        $stmt->execute();

        return $statement ? $stmt : $stmt->fetchAll();
    }

    /**
     * 插入数据
     * @example
     * echo $mdb->insert('tree6bee', array(
     *     array(
     *         'int'   => 69,
     *         'var'   => 'xxxooo',
     *         'boolean'   => true,
     *     ),
     * ));
     *
     * @param string $table 表名
     * @param array $values 插入的数据
     * @param bool $insertId 是否返回 insert id，返回会增加一次 mysql 请求
     *                       false的时候返回影响的条数
     * @return int|string
     */
    public function insert($table, array $values, $insertId = false)
    {
        // $sql = 'INSERT INTO tb (c1, c2) VALUES (?, ?), (?, ?)';
        if (! is_array(reset($values))) {
            $values = array($values);
        }
        //insert ignore into 的方式需要自己手动封装
        $sql = 'INSERT INTO ' . $table;
        $sql .= $this->getColumns(array_keys(reset($values)));  //columns
        $sql .= ' VALUES ';

        $ret = $this->getPlaceholdersAndBindings($values);

        $sql .= $this->getInsertPlaceholders($ret['placeholders']);  //columns
        $bindings = $ret['bindings'];
        $stmt = $this->pdo->prepare($sql);
        $this->bindValues($stmt, $bindings);
        $stmt->execute();

        return $insertId ? $this->getLastInsertId() : $stmt->rowCount();  //受影响的行数:1
    }

    /**
     *
     * 更新
     * echo $mdb->update(
     *     'tree6bee',
     *     array( //data
     *         'var'   => 'ooo',
     *         'boolean'   => 1,
     *     ),
     *     array( //where
     *         'id'    => 1,
     *         'int'   => 111,
     *     ), 'limit 1'
     * );
     *
     * @param $table
     * @param array $values
     * @param array $where
     * @param string $etc
     * @return int
     * @throws Exception
     */
    public function update($table, array $values, array $where, $etc = '')
    {
        // $sql = 'UPDATE tb SET c1 = ?, c2 = ? WHERE c3 = ?'
        if (empty($where)) {
            throw new Exception('必须指定数据库更新的条件');
        }
        $sql = 'UPDATE ' . $table . ' SET ';
        $sql .= $this->getPlaceholders($values);
        $sql .= ' WHERE ';
        $sql .= $this->where($where);
        $sql .= ' ' . $etc;
        $bindings = array_merge(array_values($values), array_values($where));
        $stmt = $this->pdo->prepare($sql);
        $this->bindValues($stmt, $bindings);
        $stmt->execute();
        // $this->pdo = null;
        return $stmt->rowCount();  //其实这里没有连接mysql
    }

    /**
     * 删除数据
     * echo $mdb->del('tree6bee', array(
     *     'int'   => 111,
     * ));
     *
     * @param $table
     * @param array $where
     * @param string $etc
     * @return int
     * @throws Exception
     */
    public function delete($table, array $where, $etc = '')
    {
        // $sql = 'DELETE FROM tb WHERE c1 = 'xyz'';
        if (empty($where)) {
            throw new Exception('必须指定数据库删除的条件');
        }
        $sql = 'DELETE FROM ' . $table . ' WHERE ';
        $sql .= $this->where($where);
        $sql .= ' ' . $etc;
        $bindings = array_values($where);
        $stmt = $this->pdo->prepare($sql);
        $this->bindValues($stmt, $bindings);
        $stmt->execute();
        // $this->pdo = null;
        return $stmt->rowCount();  //其实这里没有连接mysql
    }

    /**
     * Execute a Closure within a transaction.
     * @todo 考虑是否需要在死锁的时候重试，参考laravel
     * 最佳实践: 在事务回调方法中严禁调用其他模块只建议数据库操作
     *
     * 因为方法封装里边不包含了 savepoint，防止在回调函数中对不同模块进行调用
     * 原因在于，可能其他模块对当前连接和另外的连接 开启了事务
     * 从而导致另外的连接没回滚导致数据出错
     *
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Exception|\Throwable
     */
    public function transaction(Closure $callback)
    {
            $this->pdo->beginTransaction();

            // We'll simply execute the given callback within a try / catch block
            // and if we catch any exception we can rollback the transaction
            // so that none of the changes are persisted to the database.
            try {
                $result = $callback($this);

                $this->pdo->commit();
            }

            // If we catch an exception, we will roll back so nothing gets messed
            // up in the database. Then we'll re-throw the exception so it can
            // be handled how the developer sees fit for their applications.
            catch (Exception $e) {
                $this->pdo->rollBack();

                throw $e;
            } catch (Throwable $e) {
                $this->pdo->rollBack();

                throw $e;
            }

            return $result;
    }

    /**
     * 获取最近一次的 insert id
     *
     * @return string
     */
    public function getLastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Bind values to their parameters in the given statement.
     *
     * @param  \PDOStatement $statement
     * @param  array  $bindings
     * @return void
     */
    public function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1, $value,
                //PDO::PARAM_BOOL PDO::PARAM_NULL
                //is_int($value) || is_float($value)
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }

    protected function getColumns($columns)
    {
        return '( ' . implode(', ', $columns) . ' )';
    }

    protected function getPlaceholdersAndBindings($values)
    {
        $placeholders = array();
        $bindings = array();
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

    protected function getInsertPlaceholders($row)
    {
        // (?, ?),
        return ' (' . implode('), (', $row) . ') ';
    }

    protected function getPlaceholders($values)
    {
        $placeholders = array();
        foreach ($values as $column => $binding) {
            $placeholders[] = '' . $column . ' = ?';
        }
        return implode(', ', $placeholders);
    }

    /**
     * 获取where条件占位
     * array('id'   => 1, 'c1'  => 'abc')
     *      => id = ? and c1 = ?
     *
     * @param array $values
     * @return string
     */
    protected function where(array $values = array())
    {
        $placeholders = array();
        foreach ($values as $column => $binding) {
            $placeholders[] = $column . ' = ?';
        }
        return implode(' AND ', $placeholders);
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
     * @deprecated 不建议直接使用
     */
    public function getPdo()
    {
        return $this->pdo;
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
