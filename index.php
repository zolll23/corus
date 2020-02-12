<?php
require_once './vendor/autoload.php';

use \VPA\HTTP\Router as Router;
use \VPA\Config as Config;

try {

    $config = new Config('config.ini');
    $router = new Router($config);


    // REST API Старт рабочего дня
    // /worday/{profileID}/start
    $router->add_route('GET','|^/workday/(\d+)/start/?$|i',function($config,$http,$uri_data) {
	VPA\Corus\Controllers\WorkdayController::startWorkday($config,$http,$uri_data);
    });


    // /worday/{profileID}/stop
    $router->add_route('GET','|^/workday/(\d+)/stop/?$|i',function($config,$http,$uri_data) {
	VPA\Corus\Controllers\WorkdayController::stopWorkday($config,$http,$uri_data);
    });

    // /worday/{profileID}/pause
    $router->add_route('GET','|^/workday/(\d+)/pause/?$|i',function($config,$http,$uri_data) {
	VPA\Corus\Controllers\WorkdayController::pauseWorkday($config,$http,$uri_data);
    });

    // /worday/{profileID}/resume
    $router->add_route('GET','|^/workday/(\d+)/resume/?$|i',function($config,$http,$uri_data) {
	VPA\Corus\Controllers\WorkdayController::resumeWorkday($config,$http,$uri_data);
    });

    // /worday/{profileID}/lateness
    $router->add_route('GET','|^/workday/(\d+)/lateness/?$|i',function($config,$http,$uri_data) {
	VPA\Corus\Controllers\WorkdayController::testForLateWorkday($config,$http,$uri_data);
    });


    // Handler for "page not found"
    $router->default_route(function($config,$http,$uri_data) {
	$http->pageNotFound();
	$view = new \VPA\Views\JSON($config);
	$template = $view->render(['status'=>'error','msg'=>'Page not found']);
	$http->contentType('json');
	echo $template;
    });

    $router->route();


} catch (Exception $e) {
    echo "<hr>";
    echo $e;
    echo "<hr>";
}


