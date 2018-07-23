<?php

namespace Tree6bee\Support\Ctx\Db;

/**
 * 框架Pgsql数据库辅助类
 */
class Pgsql extends Connection
{
    public function insertGetId($query, $bindings = [], $primaryKey = 'id')
    {
        return $this->select($query . " returning " . $primaryKey, $bindings)[0][$primaryKey];
    }
}
