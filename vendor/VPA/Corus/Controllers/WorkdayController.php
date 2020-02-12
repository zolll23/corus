<?php

namespace VPA\Corus\Controllers;

class WorkdayController extends \VPA\Controller
{
    /**
    * Начинает рабочий день
    **/
    static function startWorkday(\VPA\Config $config,\VPA\HTTP $http,array $uri_data)
    {
	// В uri_data у нас лежит массив от разбора URI с помощью preg_match_all
	// Таким образрм, если в regexp мы использовали скобки для выделения параметров - (\d+) в нашем случае
	// эти параметры попадут в этот массив
	$profileID = $uri_data[1][0];
	// Создаем объект модели для работы с Workday
	$modelWorkday = new \VPA\Corus\Models\Workday();
	// Пробуем создать запись о том, что день начался
	$status = $modelWorkday->beginWorkday($profileID);
	// Ответ представляем как объект с одним полем status
	$data = ['status'=>$status ? 'ok' : 'error'];
	// Чтобы вывести ответ как JSON инициализируем соответствующий View
	// View - это обертка для предпочитаемого способа вывода данных
	// Мы можем создать свою обертку типа \VPA\Views\Smarty или \VPA\Views\Blade (или любой другой шаблонизатор)
	// И выводить даные в нужный шаблон
	$view = new \VPA\Views\JSON($config);
	// Форматируем данные и полуаем строку для вывода
	// (если бы view был шаблонизатором, то мы бы вызвали его метод для получения строки
	// render для Twig, fetch для Smarty и т.д.)
	$template = $view->render($data);
	// Выставялем HTTP заголовок Content-Type для выводимого содержимого
	$http->contentType('json');
	// непосредственно выводим сами данные
	echo $template;
    }

    static function stopWorkday(\VPA\Config $config,\VPA\HTTP $http,array $uri_data)
    {
	$profileID = $uri_data[1][0];
	$modelWorkday = new \VPA\Corus\Models\Workday();
	$status = $modelWorkday->endWorkday($profileID);
	$data = ['status'=>$status ? 'ok' : 'error'];
	$view = new \VPA\Views\JSON($config);
	$template = $view->render($data);
	$http->contentType('json');
	echo $template;
    }

    static function pauseWorkday(\VPA\Config $config,\VPA\HTTP $http,array $uri_data)
    {
	$profileID = $uri_data[1][0];
	$modelWorkday = new \VPA\Corus\Models\Workday();
	$workdayID = $modelWorkday->getCurrentWorkday($profileID);
	$modelPause = new \VPA\Corus\Models\WorkdayPause();
	$status = $modelPause->beginPause($workdayID);
	$data = ['status'=>$status ? 'ok' : 'error'];
	$view = new \VPA\Views\JSON($config);
	$template = $view->render($data);
	$http->contentType('json');
	echo $template;
    }

    static function resumeWorkday(\VPA\Config $config,\VPA\HTTP $http,array $uri_data)
    {
	$profileID = $uri_data[1][0];
	$modelWorkday = new \VPA\Corus\Models\Workday();
	$workdayID = $modelWorkday->getCurrentWorkday($profileID);
	$modelPause = new \VPA\Corus\Models\WorkdayPause();
	$status = $modelPause->endPause($workdayID);
	$data = ['status'=>$status ? 'ok' : 'error'];
	$view = new \VPA\Views\JSON($config);
	$template = $view->render($data);
	$http->contentType('json');
	echo $template;
    }


    /**
    *	Проверка опоздания сотрудника в текущий день
    *	Считается, что он опоздал если по его локальному времени наступило 9 утра, 
    *	но записи о начале рабочего дня еще нет
    *	true - опоздание произошло, false - опоздания не зафиксировано
    *	@return bool
    **/
    static function testForLateWorkday(\VPA\Config $config,\VPA\HTTP $http,array $uri_data)
    {
	$profileID = $uri_data[1][0];
	// Время начала рабочего дня по времени сервера
	$startHour = 9;

	$view = new \VPA\Views\JSON($config);
	$http->contentType('json');

	$modelWorkday = new \VPA\Corus\Models\Workday();
	// Если сегодня - не рабочий день, то ничего не делаем и выходим
	$isWorkday = $modelWorkday->todayIsWorkday();
	if (!$isWorkday) {
	    echo $view->render(['status'=>'error']);
	    return false;
	}
	// Получаем часовой пояс сотрудника
	$modelProfile = new \VPA\Corus\Models\Profile();
	$profileInfo = $modelProfile->getProfile($profileID);
	// Поскольку в ТЗ явно указано, что часовые пояса только кратны часу, 
	// можем использовать такое приведение 
	$workerTimeZone = intval(str_replace('0','',$profileInfo['offset']));
	// Находим текущее время сотрудника (timestamp)
	$workerTime = WorkdayController::getWorkerTime($workerTimeZone,time());

	// Получаем начало рабочего дня (timestamp)
	$startWorkTime = strtotime(date(sprintf('Y-m-d %d:00:00',$startHour)));
	// Если время рабочего дня еще на наступило - ничего не делаем и выходим
	if ($workerTime<$startWorkTime) {
	    echo $view->render(['status'=>'error']);
	    return false;
	}
	$workdayID = $modelWorkday->getCurrentWorkday($profileID);
	// Если рабочий день найден, то проверяем, когда он начался
	if($workdayID) {
	    $workdayDateStart = strtotime($modelWorkday->getWorkdayInfo($workdayID)['date_start']);
	    $workerDateStart = WorkdayController::getWorkerTime($workerTimeZone,$workdayDateStart);
	    // Если сотрудник начал работать вовремя - все нормально, ничего не делаем и выходим
	    if ($workerDateStart<=$startWorkTime) {
		echo $view->render(['status'=>'error']);
		return false;
	    }
	}
	// 
	$modelLateness = new \VPA\Corus\Models\Lateness();
	$status = $modelLateness->markLateness($profileID);
	$data = ['status'=>$status ? 'ok' : 'error'];
	$template = $view->render($data);
	echo $template;
    }

    /**
    * Вынесем всю математику по расчету времени сотрудника относительно времени сервера в отдельный метод
    *
    * workerTimeZone - смещение в часах
    * @var int 
    *
    * serverTime - время сервера в timestamp 
    * @var int 
    *
    * @return int
    **/
    static function getWorkerTime(int $workerTimeZone,int $serverTime):int
    {
	// Находим таймзону сервера
	$serverTimeZone = intval(str_replace('0','',date('O')));
	// Вычисляем смещение в часах времени сотрудника относительно времени сервера
	$offset = $workerTimeZone - $serverTimeZone;
	$offsetHours = sprintf("%d hours",$offset);
	// Получаем локальное время сотрудника (timestamp)
	$workerTime = strtotime($offsetHours,$serverTime);
	return $workerTime;
    }
}