<?php
class Postgressive
{
    public $link;

    public function __construct($host, $dbname, $port, $user, $password)
    {
        $this->link = $this->connect($host, $dbname, $port, $user, $password);
    }

    public function connect($host, $dbname, $port, $user, $password)
    {
        $pg = pg_connect("host=$host dbname=$dbname port=$port user=$user password=$password");

        if (!$pg) {
            throw new \RuntimeException('Could not connect: ' . pg_last_error());
        }

        return $pg;
    }

    // maybe: could have types specified with
    // new Postgressive\Type('bytea', $d) or $db::bytea($d)
    public function buildInsert($table, $data)
    {
        $columns = array_map(function($d) { return "\"$d\""; }, array_keys($data));
        $refs = array_map(function ($d) { return "\$$d"; }, range(1,count($data)));

        $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)',
            $table, implode(',', $columns), implode(',', $refs));

        return $sql;
    }

    public function insert($table, $row, &$insert_id = null)
    {
        $sql = $this->buildInsert($table, $row);

        if ($this->query($sql, $row)) {
            //$insert_id = $this->insert_id();
            // XXX: set insert_id
            return true;
        }

        return false;
    }

    public function query($sql, array $bind = [])
    {
        return pg_query_params($this->link, $sql, $bind);
    }
}

