<?php

namespace VPA;

abstract class View
{
    private $config;
    function __construct($config)
    {
	$this->config = $config;
    }
}