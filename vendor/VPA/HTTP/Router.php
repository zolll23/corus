<?php

namespace VPA\HTTP;

class Router
{

    private $method = 'GET';
    private $uri = '';
    private $hanlers = [];
    private $http;
    private $config;
    private $default_handler = null;

    function __construct($config)
    {
	$this->config = $config;
	$this->http = new \VPA\HTTP();
	$this->method = $this->http->getMethod();
	$this->uri = $this->http->getURI();
    }

    /**
    * Add new route 
    * method - HTTP method for linstening
    * regexp - RegExp pattern (Example: |^/$|i - root page)
    * handler - Function for this route
    **/
    function add_route(string $method,string $regexp,$handler):bool
    {
        $this->handlers[$method][] = ['pattern'=>$regexp,'handler'=>$handler];
        return true;
    }

    function default_route($handler):bool
    {
        $this->default_handler =$handler;
        return true;
    }

    /** 
    * Call a handler for the current route
    **/
    function route()
    {
        $current_handlers = array_key_exists($this->method,$this->handlers) ? $this->handlers[$this->method] : [];
        foreach ($current_handlers as $h) {
            $result = preg_match_all($h['pattern'],$this->uri,$uri_data);
            if ($result) {
        	return $h['handler']($this->config,$this->http,$uri_data);
            }
        }
	if (!empty($this->default_handler)) {
		$h = $this->default_handler;
        	return $h($this->config,$this->http,[]);
	}
    }
}