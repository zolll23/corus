<?php

namespace VPA\Views;

class JSON
{
    function __construct()
    {
    }

    public function render($data):string
    {
	$result = json_encode($data);
	if (!$result) {
	    throw new \Exception("JSON encoding failed");
	}
	return $result;
    }
}