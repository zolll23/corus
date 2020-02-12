<?php

namespace VPA;

use VPA\DB\Mysql as DB;
use VPA\Config as Config;

abstract class Model {
    /**
     * Object of DataBase class 
     *
     * @var Object
     */
    protected $db;
    /**
     * Object of Config class 
     *
     * @var Object
     */
    private $cfg;

    function __construct()
    {
        $this->cfg = new Config();
	$connection_data = $this->cfg->getSection('mysql');
        $this->db = new DB($connection_data);
    }
}