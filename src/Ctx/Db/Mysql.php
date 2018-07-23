<?php

namespace Tree6bee\Support\Ctx\Db;

use PDO;

/**
 * 框架MySql数据库辅助类
 * 每个实例代表一次数据库连接
 */
class Mysql extends Connection
{
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
                is_int($value) || is_float($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }
}
