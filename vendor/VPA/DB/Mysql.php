<?php 

namespace VPA\DB;

use \VPA\Log as Log;

/**
 * DataBase object implements simple ORM
 * @package Parser
 */
class MySQL
{
    /**
     * MySQLi Resource
     *
     * @var Resource
     */
    private $mysqli;
    /**
     * MySQL host
     *
     * @var string
     */
    private const dbHost='127.0.0.1';
    /**
     * MySQL login
     *
     * @var string
     */
    private const dbLogin='toor';
    /**
     * MySQL password
     *
     * @var string
     */
    private const dbPassword='';
    /**
     * Name of database
     *
     * @var string
     */
    private const dbName = 'media';

    function __construct(array $connection)
    {
        $this->mysqli = new \mysqli($connection['host'], $connection['user'], $connection['password'], $connection['database']);
        $this->mysqli->set_charset('utf8');
        if ($this->mysqli->connect_errno) {
            throw new \Exception(sprintf("MySQL connect failed. Error: %s", $this->mysqli->connect_error));
        }
    }

    /**
     * Execute SQL query
     * @return MySQLi Result
     */
    public function query(string $query)
    {
        $result = $this->mysqli->query($query);
        if ($this->mysqli->error) {
            throw new \Exception(sprintf("MySQL error %d. Query: %s",$this->msqli->errno, $query));
        }
        return $result;
    }

    public function escape(string $string):string
    {
	return $this->mysqli->real_escape_string($string);
    }

    public function affected_rows():int
    {
	return $this->mysqli->affected_rows;
    }

}