<?php

namespace VPA;

abstract class Controller
{
    protected $config;
    protected $view;
    protected $http;
    protected $uri_data;

    function __construct($config,$http,$uri_data)
    {
	$this->config = $config;
	$this->http = $http;
	$this->uri_data = $uri_data;
    }

    /**
    * Static method for start of execute our handler
    **/
    static public function run(\VPA\Config $config,\VPA\HTTP $http,array $uri_data)
    {
    /**
    * Our code
    **/
    }

    public function setView($view)
    {
	$this->view = $view;
    }
}