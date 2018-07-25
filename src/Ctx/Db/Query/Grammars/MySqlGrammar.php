<?php

namespace Tree6bee\Support\Ctx\Db\Query\Grammars;

class MySqlGrammar extends Grammar
{
    protected function wrap($value)
    {
        if ($value !== '*') {
            return "`$value`";
        }

        return $value;
    }
}
