<?php

namespace VPA\Corus\Models;

class Workday extends \VPA\Model {

    /**
    * Отмечает начало рабочего дня для сотрудника 
    *
    * profileID - ID сотрудника
    * @var int
    *
    * @return bool
    */
    public function beginWorkday(int $profileID):bool
    {
	// Проверяем является ли день рабочим
	$isWorkday = $this->todayIsWorkday();
	if (!$isWorkday) {
	    return false;
	}
	// Проверяем что текущий рабочий день уже не был начат
	$workdayID =$this->getCurrentWorkday($profileID);
	if ($workdayID) {
	    return false;
	}
	// Добавляем запись о начале рабочего дня
	// поэтому инициализируем ее явно  не полагаясь на значение по умолчанию в описании таблицы

	// Для избегания SQL Injections данные требуется экранировать, и для этого у нас есть метод $this->db->escape
	// Но в нашем случае мы а) явно указали в описании метода что ждем int и в случае, если это не так, то получим исключение TypeError
	// б) мы собираем запрос с явным указанием типа %d, поэтому будет произведено явное преобразование типа к INT
	$query = sprintf("INSERT INTO workday(`profile_id`,`date_start`,`date_stop`) VALUE('%d','%s',NULL)",$profileID,date('c'));
        $result = $this->db->query($query);
        return ($this->db->affected_rows() > 0);
    }

    /**
    * Отмечает конец рабочего дня для сотрудника 
    *
    * profileID - ID сотрудника
    * @var int
    *
    * @return bool
    */
    public function endWorkday(int $profileID):bool
    {
	// Проверяем является ли день рабочим
	$isWorkday = $this->todayIsWorkday();
	if (!$isWorkday) {
	    return false;
	}
	// Если для сотрудника день не начат - то возвращаем false
	/* Поскольку задачей стоит минимизация запросов к БД, может показаться логичным
	*  отказаться от отдельной проверки на наличие нужной записи в БД и попытаться записать данные
	*  используя, к примеру, INSERT IGNORE или UPDATE несуществующей записи  и отследить статус операции по affected_rows
	*  Но в пользу дополнительной проверки можно привести следующие аргументы:
	*  1) Операции UPDATE,INSERT приводит к блокированию всей таблицы(MyISAM) или строк (InnoDB) и чем быстрее эта блокировка
	*  снята - тем лучше, в нашем случае поиск записи для изменения будет идти по PRIMARY KEY, который очень эффективен и время
	*  блокировки будет минимальным.
	*  2) Более читаемый код: проверка прописана явно
	*/
	$workdayID =$this->getCurrentWorkday($profileID);
	if (!$workdayID) {
	    return false;
	}
	// Выставляем время окончания рабочего дня
	$query = sprintf("UPDATE workday SET `date_stop`='%s' WHERE id=%d",date('c'),$workdayID);
        $result = $this->db->query($query);
        return ($this->db->affected_rows() > 0);
    }

    /**
    * Ищем запись о начале рабочего дня 
    *
    * profileID - ID сотрудника
    * @var int
    *
    * @return int
    **/
    public function getCurrentWorkday(int $profileID):int
    {
	// Определять текущий рабочий день будем по неустановленной дате date_stop
	// Альтернативный вариант: Можно было бы проверять, не попадает ли дата начала рабочего дня в сегодняшний день,
	// к примеру, через проверку date_start>=date('Y-m-d 00:00:00') 
	// п.2 задания допускает работоспособность такого варианта, но 
	// (а) п.2 выполнять не требуется,
	// (б) этот вариант не позволяет обслуживать ситуации с ночными сменами, начинающимися раньше 12 ночи и заканчивающимися позже
	// Бытует мнение, что MySQL не использует индексы для NULL и альтернативный вариант был бы быстрее, но это не так
	// https://dev.mysql.com/doc/refman/8.0/en/is-null-optimization.html
	$query = sprintf("SELECT `id` FROM `workday` WHERE `profile_id`=%d AND date_stop IS NULL LIMIT 1",$profileID);
        $result = $this->db->query($query);
        if ($result->num_rows > 0) {
            return $result->fetch_assoc()['id'];
        }
	return 0;
    }

    /**
    *  Получаем полную информацию о рабочем дне, заданным workdayID
    *
    * workdayID - ID рабочего дня
    * @var int
    *
    * @return array
    **/
    public function getWorkdayInfo(int $workdayID):array
    {
	$query = sprintf("SELECT `id`,`date_start`,`date_stop`,`profile_id` FROM `workday` WHERE `id`=%d",$workdayID);
        $result = $this->db->query($query);
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
	return [];
    }

    /**
    * Проверяем что для сотрудника текущий день является рабочим
    * Поскольку критерии рабочего дня не заданы, принимаем, что рабочими являются все дни кроме субботы-воскресенья
    * @return bool
    **/
    public function todayIsWorkday():bool
    {
	$dayOfWeek = date('w');
	return ($dayOfWeek>0 && $dayOfWeek<6);
    }
}