<?php

namespace VPA\Corus\Models;

class WorkdayPause extends \VPA\Model {

    /**
    * Отмечает начало паузы в рабочем дне для сотрудника 
    *
    * workdayID - ID рабочего дня
    * @var int
    *
    * @return bool
    */
    public function beginPause(int $workdayID):bool
    {
	// Проверяем что у нас еще нет начатой паузы
	$pauseID=$this->getCurrentPause($workdayID);
	if ($pauseID) {
	    return false;
	}
	// Добавляем запись о начале паузы
	// Определять текущую паузу будем по неустановленной дате date_stop, поэтому инициализируем ее явно
	// не полагаясь на значение по умолчанию в описании таблицы
	$query = sprintf("INSERT INTO workday_pause(`workday_id`,`date_start`,`date_stop`) VALUE('%d','%s',NULL)",$workdayID,date('c'));
        $result = $this->db->query($query);
        return ($this->db->affected_rows() > 0);
    }

    /**
    * Отмечает конец рабочего дня для сотрудника 
    *
    * workdayID - ID рабочего дня
    * @var int
    *
    * @return bool
    */
    public function endPause(int $workdayID):bool
    {
	// Если для сотрудника пауза не начата - то возвращаем false
	$pauseID =$this->getCurrentPause($workdayID);
	if (!$pauseID) {
	    return false;
	}
	// Выставляем время окончания рабочего дня
	$query = sprintf("UPDATE `workday_pause` SET `date_stop`='%s' WHERE `id`=%d",date('c'),$pauseID);
        $result = $this->db->query($query);
        return ($this->db->affected_rows() > 0);
    }

    /**
    * Ищем запись о текущей паузе
    *
    * workdayID - ID рабочего дня
    * @var int
    *
    * @return int
    **/
    private function getCurrentPause(int $workdayID):int
    {
	$query = sprintf("SELECT `id` FROM `workday_pause` WHERE `workday_id`=%d AND `date_stop` IS NULL LIMIT 1",$workdayID);
        $result = $this->db->query($query);
        if ($result->num_rows > 0) {
            return $result->fetch_assoc()['id'];
        }
	return 0;
    }
}