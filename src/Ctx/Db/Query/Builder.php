<?php

namespace Tree6bee\Support\Ctx\Db\Query;

use Tree6bee\Support\Ctx\Db\Connection;
use Tree6bee\Support\Ctx\Db\Query\Grammars\Grammar;

class Builder
{
    /**
     * @var Connection
     */
    protected $conn;

    /**
     * @var Grammar
     */
    protected $grammar;

    /**
     * @var string
     */
    public $table;

    public function __construct(Connection $conn, Grammar $grammar)
    {
        $this->conn = $conn;
        $this->grammar = $grammar;
    }

    /**
     * @param $table
     * @return $this
     */
    public function table($table)
    {
        $this->table = $table;

        return $this;
    }

//    public function get()
//    {
//        return $this->conn->select($this->toSql(), $this->getBindings());
//    }

    public function insert(array $values)
    {
        if (! is_array(reset($values))) {
            $values = [$values];
        }

        list($query, $bindings) = $this->grammar->compileInsert($this, $values);

        return $this->conn->insert($query, $bindings);
    }
}
