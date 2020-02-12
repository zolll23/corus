<?php

namespace VPA;

class Config
{
    private $base_dir;
    private $templates_dir;
    private $templates_cache_dir;
    private $use_cache = false;
    private $config_ini_file;
    private $config;

    function __construct(string $config_ini_file='config.ini')
    {
        $bt =debug_backtrace();
	$btl = array_pop($bt);
        $index = $btl['file'];
	$this->base_dir = dirname($index);
	$this->config_ini_dir = $this->base_dir.DIRECTORY_SEPARATOR.'ini';
        $this->config_ini_file = $config_ini_file;
        $this->loadINIFile();
        
	$this->templates_dir = $this->base_dir.DIRECTORY_SEPARATOR.'twig'.DIRECTORY_SEPARATOR.'templates';
	$this->templates_cache_dir = $this->base_dir.DIRECTORY_SEPARATOR.'twig'.DIRECTORY_SEPARATOR.'cache';
    }

    /*static function &get_instance()
    {
	static $instance;
        $class = __CLASS__;
	if (!isset($instance)) $instance = new $class;
	return $instance;
    }*/


    private function loadINIFile()
    {
        $file = $this->config_ini_dir.DIRECTORY_SEPARATOR.$this->config_ini_file;
        if (!file_exists($file)) {
            throw new \Exception(sprintf("INI file %s not found",$file));
        }
        if (!is_file($file)) {
            throw new \Exception(sprintf("INI file %s is not file",$file));
        }
        $this->config = parse_ini_file($file,true);
    }

    public function getSection(string $name):array
    {
	return (isset($this->config[$name])) ? $this->config[$name] : [];
    }

    public function getBaseDir():string
    {
	return $this->base_dir;
    }

    public function getTplDir():string
    {
	return $this->templates_dir;
    }

    public function getTplCacheDir():string
    {
	return $this->templates_cache_dir;
    }

    public function useCache():bool
    {
	return $this->use_cache;
    }
}